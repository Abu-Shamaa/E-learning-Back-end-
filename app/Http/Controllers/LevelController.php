<?php

namespace App\Http\Controllers\Quiz;

use App\Http\Controllers\Controller;

use App\Models\Quiz\Course;
use App\Models\Quiz\Level;
use App\Models\Quiz\LevelTopic;
use App\Models\Quiz\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LevelController extends Controller
{
    public function levelIndex()
    {
        $level = Level::join('courses', 'levels.course_id', 'courses.id')

            ->get(['levels.id', 'courses.course_name', 'levels.level_name']);
        return response()->json($level);
    }


    public function levelStore(Request $request)
    {
        $form = request()->validate([

            'level_name' => 'required',
            'ltopic' => 'required',

        ]);
        try {
            DB::beginTransaction();

            $course_name = $request->course_name;
            $course_n = Course::where('course_name', $course_name)->first();

            $form['course_id'] = $course_n->id;
            $level = Level::create($form);
            $leveltopic = $form['ltopic'];

            foreach ((array)$leveltopic as $lt) {
                $topic = Topic::where('topic_name', $lt)->first();
                LevelTopic::create([
                    'topic_id' => $topic->id,
                    'topic_name' => $topic->topic_name,
                    'level_id' => $level->id,

                ]);
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'level Created Successfully'

            ], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()

            ], 500);
        }
    }

    public function levelShow($id)
    {
        $level = Level::with('ltopic')->findOrFail($id);
        $topic = Topic::join('levels', 'levels.course_id', 'topics.course_id')
            ->where('levels.id', '=', $id)->get('topics.topic_name');

        if ($level) {
            return response()->json([
                'status' => true,
                'level' => $level,
                'topic' => $topic,

            ], 200);
        }
    }

    public function levelUpdate($id)
    {
        $form = request()->validate([

            'level_name' => 'string',
            'ltopic' => 'array',

        ]);
        try {
            DB::beginTransaction();
            $level = Level::findOrFail($id);

            $level->update($form);
            if (isset($form['ltopic'])) {
                LevelTopic::where('level_id', '=', $id)->delete();
                $leveltopic = $form['ltopic'];
                foreach ((array)$leveltopic as $lt) {
                    $topic = Topic::where('topic_name', $lt)->first();
                    LevelTopic::create([
                        'topic_id' => $topic->id,
                        'topic_name' => $topic->topic_name,
                        'level_id' => $level->id,
                    ]);
                }
            }

            DB::commit();
            return response()->json([

                'status' => true,
                'message' => 'level updated Successfully'

            ], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()

            ], 500);
        }
    }

    public function levelDestroy($id)
    {
        $level = Level::findOrFail($id);
        $level->delete();

        return response()->json([
            'status' => true,
            'message' => 'level deleted Successfully'

        ], 200);
    }
}
