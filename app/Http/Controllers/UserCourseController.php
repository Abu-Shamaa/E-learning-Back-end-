<?php

namespace App\Http\Controllers\Quiz;

use App\Http\Controllers\Controller;
use App\Models\Quiz\AttemptHistory;
use App\Models\Quiz\Course;
use App\Models\Quiz\LevelQuiz;
use App\Models\Quiz\Question;
use Illuminate\Support\Facades\Gate;
use App\Models\Quiz\Quizattempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserCourseController extends Controller
{
    public function ViewCourse($id)
    {
        $quiz = Course::with(['topic' => function ($tp) {
            $tp->with('topicContent');
            $tp->with('topicArticle');
        }])->with(['quiz' => function ($q) {
            $q->where('status', '=', 'P');
            $q->with(['ahistory' => function ($ah) {
                $ah->where('user_id', Auth()->user()->id);
                $ah->where('is_attempt', 1);
            }]);
        }])
            ->where('id', $id)->first();
        return response()->json($quiz);
    }

    public function ViewQuiz($id)
    {

        $ql = LevelQuiz::where('quiz_id', $id)
            ->get();

        $question = Question::join('level_quiz', 'level_quiz.level_id', '=', 'questions.level_id')
            ->join('quizzes', 'quizzes.id', '=', 'level_quiz.quiz_id')
            ->with('option')
            ->where('quizzes.id', '=', $id)->inRandomOrder()->get([
                'quizzes.quiz_name', 'questions.topic_id',
                'questions.level_id', 'questions.course_id',
                'questions.title', 'questions.id', 'level_quiz.quiz_id',
                'questions.q_content', 'questions.question_type',
                'quizzes.test_end'

            ]);


        $filter_array = [];
        foreach ($ql as $q) {
            $count = 0;
            $filter = $question->filter(function ($qs) use ($q, &$count) {
                if ($qs->level_id == $q->level_id) {
                    if ($count < $q->qcount) {

                        $count++;
                        return true;
                    } else {
                        return false;
                    }
                }
            })->values()->all();
            $count = 0;
            array_push($filter_array, $filter);
        }
        $arraysMerged = array_merge([], ...$filter_array);
        //return $arraysMerged;

        $quiz = AttemptHistory::where('attempt_histories.user_id', Auth()->user()->id)
            ->where('attempt_histories.quiz_id', $id)
            ->get([
                'attempt_histories.is_attempt'
            ]);

        //$question[0]['quiz_id'] = $id;
        if ($arraysMerged) {
            return response()->json([
                'status' => true,
                'question' => $arraysMerged,
                'quiz' => $quiz,

            ], 200);
        }
    }
    public function attemptQuiz(Request $request)
    {


        try {
            DB::beginTransaction();

            foreach ((array)$request->quiz_id as  $qi) {
                $quizId = $qi;
            }
            foreach ((array)$request->attemptans as  $key => $value) {
                $answer = Question::where('id', $key)->first('answer');
                $equal = strcasecmp($value, $answer->answer);
                //$points = ($value == $answer->answer) ? 1 : 0;
                $points = ($equal == 0) ? 1 : 0;


                Quizattempt::create([
                    'quiz_id' => $quizId,
                    'question_id' => $key,
                    'attempt_ans' => $value,
                    'points' => $points,
                    'user_id' => Auth()->user()->id,
                ]);
            }


            //****attempt history table******
            $quiz_point = Quizattempt::where('quiz_id', '=', $quizId)
                ->where('user_id', '=', Auth()->user()->id)
                ->where('points', '=', '1')
                ->get(['points']);
            $qcount = LevelQuiz::where('quiz_id', $quizId)
                ->get()->sum('qcount');
            /////////////////////////////////////////////////////
            // $quizz = AttemptHistory::where('quiz_id', '=', $quizId)->firstOrFail();
            // $isA = $request->attemptans;
            // $quizz->update([
            //     'total_question' => $qcount,
            //     'total_point' => count($quiz_point),
            //     'is_attempt' => $isA == TRUE ? 1 : 0,
            // ]);
            AttemptHistory::create([
                'quiz_id' => $quizId,
                'user_id' => Auth()->user()->id,
                'total_question' => $qcount,
                'total_point' => count($quiz_point),
                'is_attempt' => $quizId == TRUE ? 1 : 0,

            ]);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => ' Quiz Attempt Successfully'

            ], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()

            ], 500);
        }
    }
    public function quizResult($id)
    {
        $quiz = AttemptHistory::join('quizzes', 'quizzes.id', '=', 'attempt_histories.quiz_id')
            ->join('users', 'users.id', '=', 'attempt_histories.user_id')
            ->where('attempt_histories.quiz_id', $id)
            ->where('user_id', Auth()->user()->id)
            ->where('is_attempt', 1)
            ->get(['quizzes.quiz_name', 'users.name', 'attempt_histories.total_question', 'attempt_histories.total_point',]);
        return response()->json($quiz);
    }
    public function quizResultIndex()
    {
        $quiz = AttemptHistory::join('quizzes', 'quizzes.id', '=', 'attempt_histories.quiz_id')
            ->join('users', 'users.id', '=', 'attempt_histories.user_id')
            ->join('courses', 'courses.id', '=', 'quizzes.course_id')
            ->where('user_id', Auth()->user()->id)
            ->where('is_attempt', 1)
            ->get(['courses.course_name', 'quizzes.quiz_name', 'users.name', 'attempt_histories.total_question', 'attempt_histories.total_point',]);
        return response()->json($quiz);
    }

    /// teacher
    public function attemptHistory($id)
    {
        $atmhistory = AttemptHistory::join('quizzes', 'quizzes.id', '=', 'attempt_histories.quiz_id')
            ->join('users', 'users.id', '=', 'attempt_histories.user_id')
            ->where('quizzes.course_id', $id)
            ->where('attempt_histories.is_attempt', '=', '1')
            ->get(['attempt_histories.id', 'quizzes.quiz_name', 'users.name', 'total_question', 'total_point', 'is_attempt']);
        return response()->json($atmhistory);
    }

    public function quizReattempt($id)
    {
        $quiz = AttemptHistory::findOrFail($id);
        if ($quiz) {
            $quiz->is_attempt = 0;
            if (Gate::allows('aa_instructor')) {
                $quiz->save();
            } else {
                return response([
                    'message' => 'you are not allow to approve'
                ], 403);
            }
        }
        $quiz_id = AttemptHistory::where('id', $id)->first('quiz_id');
        $quizId = $quiz_id->quiz_id;
        Quizattempt::where('quiz_id', $quizId)->delete();
        return response()->json([
            'status' => true,
            'message' => ' updated Successfully'

        ], 200);
    }
}
