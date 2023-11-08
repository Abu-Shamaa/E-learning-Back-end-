<?php

namespace App\Http\Controllers\Quiz;

use App\Http\Controllers\Controller;
use App\Models\Quiz\Level;
use App\Models\Quiz\LevelTopic;
use App\Models\Quiz\Option;
use App\Models\Quiz\Question;
use App\Models\Quiz\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    public function Qsnindex()
    {
        $question = Question::join('courses', 'questions.course_id', 'courses.id')
            ->join('topics', 'questions.topic_id', 'topics.id')
            ->join('levels', 'questions.level_id', 'levels.id')
            ->with('option')
            ->get([
                'questions.id', 'courses.course_name', 'topics.topic_name', 'levels.level_name',
                'questions.title', 'questions.q_content', 'questions.question_type', 'questions.answer',
            ]);
        return response()->json($question);
    }

    public function levelTopic(Request $request)

    {
        $level_name = $request->level_name;
        $level = Level::where('level_name', '=', $level_name)->first();
        $topic = LevelTopic::where('level_id', '=', $level->id)->get();
        return response()->json($topic);
    }
    public function Qsnstore(Request $request)
    {
        $form = request()->validate([
            'title' => 'required',
            'q_content' => 'nullable',
            'question_type' => 'required',
            'answer' => 'required',
            'level_name' => 'required',
            'topic_name' => 'required',
            'option' => 'required',


        ]);
        try {
            DB::beginTransaction();


            // $level_name = $request->level_name;
            $level = Level::where('level_name', $form['level_name'])->first();
            $tn = Topic::where('topic_name', $form['topic_name'])->first();
            $topic = LevelTopic::where('level_id', '=', $level->id)->get('topic_id');
            $filter = $topic->filter(function ($tp) use ($tn) {
                if ($tp->topic_id == $tn->id) {
                    return true;
                }
            })->values()->first();

            if ($filter != null) {
                $form['topic_id'] = $filter->topic_id;
            } else {
                return response([
                    'message' => 'Topic is not matched the selected Level'
                ], 406);
            }

            $form['level_id'] = $level->id;
            $form['course_id'] = $level->course_id;
            $question = Question::create($form);
            if ($form['question_type'] === 'MCQ') {
                $arry = (array)$request->option;
                if (count($arry) != 1) {
                    foreach ($arry as $data) {

                        Option::create([
                            'question_id' => $question->id,
                            'option' => $data['data'],

                        ]);
                    }
                } else {
                    return response([
                        'message' => 'Please enter option at least 2'
                    ], 406);
                }
            }


            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Question Created Successfully'

            ], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()

            ], 500);
        }
    }


    public function Qsnshow($id)
    {
        $question = Question::with('option')->findOrFail($id);

        if ($question) {
            return response()->json([
                'status' => true,
                'qstn' => $question

            ], 200);
        }
    }

    public function Qsnupdate(Request $request, $id)
    {
        $form = request()->validate([

            'title' => 'string',
            'q_content' => 'nullable',
            'answer' => 'string',

        ]);
        try {
            DB::beginTransaction();
            $question = Question::findOrFail($id);

            $question->update($form);

            if (isset($request->option)) {
                Option::where('question_id', '=', $id)->delete();
                foreach ((array)$request->option as $data) {

                    Option::create([
                        'question_id' => $question->id,
                        'option' => $data['option'],
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Question updated Successfully'

            ], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()

            ], 500);
        }
    }

    public function Qsndestroy($id)
    {
        $question = Question::findOrFail($id);
        Option::where('question_id', '=', $id)->delete();
        $question->delete();


        return response()->json([
            'status' => true,
            'message' => 'Question deleted Successfully'

        ], 200);
    }
}
