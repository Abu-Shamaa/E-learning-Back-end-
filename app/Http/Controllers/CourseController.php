<?php

namespace App\Http\Controllers\Quiz;

use App\Http\Controllers\Controller;
use App\Models\CourseUser\CourseUser;
use App\Models\Quiz\Course;
use App\Models\Quiz\Level;
use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CourseController extends Controller
{
    public function courseIndex()
    {
        if (
            Gate::allows('aa_staff2') || Gate::allows('aa_instructor')
        ) {
            $course = Course::all();
            return response()->json($course);
        } else {
            $user_id = Auth()->user()->id;
            $c = CourseUser::where('course_user.user_id', '=', $user_id)
                // ->where('course_user.is_enrole', '=', 1)
                ->get(['course_user.course_id']);
            //return response()->json($c);
            //dd($c->course_id);

            $courseA = Course::all();
            $filter = $courseA->filter(function ($ca) use ($c) {
                //dd($ca->id);
                //dd($c->where('course_id', $ca->id)->count());
                return !$c->where('course_id', $ca->id)->count();
                //return false;
            })->values()->all();
            //return ($filter);
            return response()->json($filter);
        }
    }
    public function myCourse()
    {
        $user_id = Auth()->user()->id;
        $course = Course::join('course_user', 'course_user.course_id', '=', 'courses.id')
            ->where('user_id', $user_id)
            ->where('is_enrole', '=', 1)
            ->get();
        return response()->json($course);
    }

    public function courseStore()
    {
        $form = request()->validate([
            'course_name' => 'required',
            'description' => 'nullable',
            'crs_inst' => 'required',
        ]);
        try {

            DB::beginTransaction();

            $course = Course::create($form);

            $crs_inst = $form['crs_inst'];

            foreach ($crs_inst as $ci) {
                $instructor = User::where('id', $ci['value'])->where('is_instructor', '=', 1)->first();
                CourseUser::create([
                    'course_id' => $course->id,
                    'user_id' => $instructor->id,
                    'is_enrole' =>  $crs_inst == TRUE ? 1 : 0,
                ]);
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Course Created Successfully'

            ], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()

            ], 500);
        }
    }

    public function courseShow($id)
    {
        $course = Course::with('topic', 'quiz')
            ->with(['level' => function ($lvl) {
                $lvl->with('ltopic');
                $lvl->select('levels.id', 'levels.course_id', 'levels.level_name',);
            }])->with(['question' => function ($qstn) {
                $qstn->join('topics', 'topics.id', 'questions.topic_id');
                $qstn->join('levels', 'levels.id', 'questions.level_id');
                $qstn->select(
                    'questions.id',
                    'questions.course_id',
                    'questions.title',
                    'questions.q_content',
                    'questions.question_type',
                    'questions.answer',
                    'topics.topic_name',
                    'levels.level_name'
                );
            }])
            ->where('courses.id', $id)->first();
        //$course = Course::with('topic', 'level', 'quiz', 'question')->where('courses.id', $id)->first();

        if ($course) {
            return response()->json([
                'status' => true,
                'course' => $course

            ], 200);
        }
    }

    public function editCourse($id)
    {

        $course = Course::with(['instructor' => function ($ins) {
            $ins->where('is_instructor', '=', 1);
        }])->findOrFail($id);

        //$course->load('instructor');
        if ($course) {
            return response()->json([
                'status' => true,
                'course' => $course

            ], 200);
        }
    }
    public function courseUpdate($id)
    {
        $form = request()->validate([
            'course_name' => 'string',
            'description' => 'nullable',
            'crs_inst' => 'array',
        ]);
        try {
            DB::beginTransaction();
            $course = Course::findOrFail($id);

            $course->update($form);
            if (isset($form['crs_inst'])) {
                CourseUser::where('course_id', '=', $id)->delete();
                $crs_inst = $form['crs_inst'];
                foreach ((array)$crs_inst as $ci) {
                    $instructor = User::where('id', $ci['value'])->where('is_instructor', '=', 1)->first();
                    CourseUser::create([
                        'course_id' => $course->id,
                        'user_id' => $instructor->id,
                        'is_enrole' =>  $crs_inst == TRUE ? 1 : 0,
                    ]);
                }
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Course updated Successfully'

            ], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()

            ], 500);
        }
    }

    public function courseDestroy($id)
    {
        $course = Course::findOrFail($id);
        CourseUser::where('course_id', '=', $id)->delete();
        $course->delete();

        return response()->json([
            'status' => true,
            'message' => 'Course deleted Successfully'

        ], 200);
    }
    public function indexEnrole()
    {
        $course = Course::join('course_user', 'course_user.course_id', '=', 'courses.id')
            ->join('users', 'course_user.user_id', '=', 'users.id')
            ->where('is_enrole', '=', 0)
            ->get([
                'course_user.id', 'course_user.course_id', 'course_user.user_id', 'course_user.is_enrole',
                'courses.course_name', 'courses.description', 'users.name'
            ]);
        return response()->json($course);
    }
    public function approveEnrole($id)
    {
        $course = CourseUser::findOrFail($id);
        if ($course) {
            $course->is_enrole = 1;
            if (Gate::allows('aa_staff2')) {
                $course->save();
            } else {
                return response([
                    'message' => 'you are not allow to approve'
                ], 403);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Approved Successfully'

        ], 200);
    }
    public function disapprovedEnrole($id)
    {
        $course = CourseUser::findOrFail($id);
        $course->delete();

        return response()->json([
            'status' => true,
            'message' => 'deleted Successfully'

        ], 200);
    }
    ///////////** for user */
    public function courseEnrole(Request $request)
    {
        $course_id = $request->course_id;
        $user_id = Auth()->user()->id;
        CourseUser::create([
            'course_id' => $course_id,
            'user_id' => $user_id,
            'is_enrole' => $course_id == TRUE ? 0 : 1,
        ]);
        return response()->json([
            'status' => true,
            'message' => 'Course enroled Successfully'

        ], 200);
    }
    public function pendingEnrole()
    {
        $user_id = Auth()->user()->id;
        $course = Course::join('course_user', 'course_user.course_id', '=', 'courses.id')
            ->where('user_id', $user_id)
            ->where('is_enrole', '=', 0)
            ->get();
        return response()->json($course);
    }
}
