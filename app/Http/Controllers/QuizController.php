<?php

namespace App\Http\Controllers\Quiz;

use App\Http\Controllers\Controller;
use App\Models\Quiz\Course;
use App\Models\Quiz\Level;
use App\Models\Quiz\LevelQuiz;
use App\Models\Quiz\Question;
use App\Models\Quiz\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class QuizController extends Controller
{
    public function Qindex()
    {
        $quiz = Quiz::join('courses', 'quizzes.course_id', '=', 'courses.id')
            ->get(['quizzes.id', 'courses.course_name', 'quizzes.quiz_name', 'quizzes.test_time', 'quizzes.status', 'quizzes.start_date', 'quizzes.test_end']);
        return response()->json($quiz);
    }

    public function Qstore(Request $request)
    {
        $form = request()->validate([
            'quiz_name' => 'required',
            'start_date' => 'required',
            'inputlist' => 'required',
            'test_time' => 'required',
            'status' => 'required|max:1|string|regex:/(^([PD])$)/u',
        ]);
        try {
            DB::beginTransaction();


            // $course_name = $request->course_name;
            // $course_n = Course::where('course_name', $course_name)->first();
            // $form['course_id'] = $course_n->id;
            // $time = $form['test_time'];
            // $start_time = new Carbon($request->start_date);
            // $form['test_end'] = $start_time->addMinute($time);
            // $quiz = Quiz::create($form);

            $course_name = $request->course_name;
            $course_n = Course::where('course_name', $course_name)->first();
            $form['course_id'] = $course_n->id;
            $time = $form['test_time'];
            $start_time = new Carbon($request->start_date);
            $test_end = $start_time->addMinute($time);
            $form['test_end'] = Carbon::parse($test_end)->toIso8601String();
            $form['start_date'] = Carbon::parse($request->start_date)->toIso8601String();
            $quiz = Quiz::create($form);

            $inputlist = $form['inputlist'];
            foreach ((array)$inputlist as $index => $levels) {
                $levelId = Level::where('level_name', $levels['level_name'])->first();
                $qstn = Question::where('level_id', '=', $levelId->id)->get()->count();
                //return ($qstn);
                $lq['quiz_id'] = $quiz->id;
                $lq['level_id'] = $levelId->id;
                if ($qstn >= $levels['qcount']) {
                    $lq['qcount'] = $levels['qcount'];
                } else {
                    return response()->json([
                        'message' => 'please select question count less than or equal' . ' ' . $qstn
                    ], 400);
                }

                LevelQuiz::create($lq);
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Quiz Created Successfully'

            ], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()

            ], 500);
        }
    }

    public function Qshow($id)
    {

        $quiz = Quiz::with(['qlevel' => function ($ql) {
            $ql->get(['levels.level_name', 'level_quiz.qcount']);
        }])->where('quizzes.id', $id)->first();

        $level = Quiz::join('levels', 'levels.course_id', 'quizzes.course_id')
            ->where('quizzes.id', $id)->get(['levels.level_name']);
        if ($quiz) {
            return response()->json([
                'status' => true,
                'quiz' => $quiz,
                'level' => $level

            ], 200);
        }
    }

    public function Qupdate(Request $request, $id)
    {
        $form = request()->validate([
            'quiz_name' => 'string',
            'start_date' => 'required',
            'test_time' => 'required',
            'status' => 'string|max:1|string|regex:/(^([PD])$)/u',
        ]);
        try {
            DB::beginTransaction();
            $quiz = Quiz::findOrFail($id);

            $time = $form['test_time'];
            $start_time = new Carbon($request->start_date);
            $test_end = $start_time->addMinute($time);
            $form['test_end'] = Carbon::parse($test_end)->toIso8601String();
            $form['start_date'] = Carbon::parse($request->start_date)->toIso8601String();
            $quiz->update($form);

            if (isset($request->inputlist)) {
                LevelQuiz::where('quiz_id', '=', $id)->delete();
                $inputlist = $request->inputlist;
                foreach ((array)$inputlist as $index => $levels) {
                    $levelId = Level::where('level_name', $levels['level_name'])->first();
                    $qstn = Question::where('level_id', '=', $levelId->id)->get()->count();
                    if ($qstn >= $levels['qcount']) {
                        $qcount = $levels['qcount'];
                    } else {
                        return response()->json([
                            'message' => 'please select question count less than or equal' . ' ' . $qstn
                        ], 400);
                    }
                    LevelQuiz::create([
                        'quiz_id' => $quiz->id,
                        'level_id' => $levelId->id,
                        'qcount' => $qcount
                    ]);
                }
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Quiz updated Successfully'

            ], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()

            ], 500);
        }
    }

    public function Qdestroy($id)
    {
        $quiz = Quiz::findOrFail($id);
        LevelQuiz::where('quiz_id', '=', $id)->delete();
        $quiz->delete();

        return response()->json([
            'status' => true,
            'message' => 'Quiz deleted Successfully'

        ], 200);
    }
}
