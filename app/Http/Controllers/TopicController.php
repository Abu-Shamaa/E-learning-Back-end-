<?php

namespace App\Http\Controllers\Quiz;

use App\Http\Controllers\Controller;
use App\Models\Quiz\Course;
use App\Models\Quiz\Level;
use App\Models\Quiz\LevelTopic;
use App\Models\Quiz\TArticle;
use App\Models\Quiz\TContent;
use App\Models\Quiz\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class TopicController extends Controller
{
    public function topicIndex()
    {

        $topic = Topic::join('courses', 'topics.course_id', '=', 'courses.id')
            ->get(['topics.id', 'courses.course_name', 'topics.topic_name']);
        return response()->json($topic);
    }

    public function topicStore(Request $request)
    {
        $form = request()->validate([
            'topic_name' => 'required',
        ]);


        $course_name = $request->course_name;
        $course_n = Course::where('course_name', $course_name)->first();
        $form['course_id'] = $course_n->id;
        Topic::create($form);

        return response()->json([
            'status' => true,
            'message' => 'Topic Created Successfully'

        ], 200);
    }

    public function topicShow($id)
    {
        $topic = Topic::with('topicArticle', 'topicContent')->findOrFail($id);

        if ($topic) {
            return response()->json([
                'status' => true,
                'topic' => $topic

            ], 200);
        }
    }

    public function topicUpdate($id)
    {


        $topic = Topic::findOrFail($id);
        $form = request()->validate([
            'topic_name' => 'string',
        ]);
        $topic->update($form);

        return response()->json([
            'status' => true,
            'message' => 'Topic updated Successfully'

        ], 200);
    }

    public function topicDestroy($id)
    {
        $topic = Topic::findOrFail($id);
        LevelTopic::where('topic_id', '=', $id)->delete();
        $topic->delete();

        return response()->json([
            'status' => true,
            'message' => 'Topic deleted Successfully'

        ], 200);
    }

    public function fileStore(Request $request)
    {
        $form = request()->validate([
            'files' => 'required',
            'file.*' => 'required|mimes:doc,docx,xlsx,xls,pdf,zip,png,jpg|size:100000', //100 mb

        ]);

        $topic_name = $request->topic_name;
        $topic_n = Topic::where('topic_name', $topic_name)->first();
        $topic_id = $topic_n->id;
        if ($form['files']) {
            foreach ((array)$form['files'] as $file) {

                $filename = $file->getClientOriginalName();
                $file->move('uploads/topic/', $filename);

                TContent::insert([
                    'file' => $filename,
                    'topic_id' => $topic_id,
                ]);
            }
        }




        return response()->json([
            'status' => true,
            'message' => 'Topic content created Successfully'

        ], 200);
    }

    public function downloadFile($id)
    {
        $file = TContent::where('id', $id)->first();
        $pathToFile = public_path("uploads/topic/{$file->file}");
        return Response::download($pathToFile);
    }

    public function topicContentDestroy($id)
    {
        $topicCon = TContent::findOrFail($id);
        if ($topicCon->file) {
            $path = 'uploads/topic/' . $topicCon->file;
            if (File::exists($path)) {
                File::delete($path);
            }
        }
        $topicCon->delete();

        return response()->json([
            'status' => true,
            'message' => 'Topic content deleted Successfully'

        ], 200);
    }
    public function checkSlug($slug)
    {
        $ta_slug = TArticle::where('slug', '=', $slug)->first();
        return response()->json($ta_slug);
    }
    public function slugCreate(Request $request)
    {


        $name = $request->name;
        $slug = str()->slug($name);
        $allSlugs = TArticle::select('slug')->where('slug', 'like', $slug . '%')
            ->get();
        if (!$allSlugs->contains('slug', $slug)) {
            return response()->json($slug);
        }

        $i = 1;
        $is_contain = true;
        do {
            $newSlug = $slug . '-' . $i;
            if (!$allSlugs->contains('slug', $newSlug)) {
                $is_contain = false;
                return response()->json($newSlug);
            }
            $i++;
        } while ($is_contain);
    }
    public function articleStore(Request $request)
    {
        $form = request()->validate([
            'name' => 'required',
            'slug' => 'required|unique:topic_articles,slug',
            'content' => 'required',
        ]);
        $topic_name = $request->topic_name;
        $topic_n = Topic::where('topic_name', $topic_name)->first();

        $form['topic_id'] = $topic_n->id;

        TArticle::create($form);

        return response()->json([
            'status' => true,
            'message' => 'Topic article created Successfully'

        ], 200);
    }
    public function showTopicArticle($id)
    {
        $topicAticle = TArticle::findOrFail($id);
        if ($topicAticle) {
            return response()->json([
                'status' => true,
                'topicAticle' => $topicAticle

            ], 200);
        }
    }
    public function viewTopicArticle($slug)
    {
        $topicAticle = TArticle::where('slug', $slug)->firstOrFail();
        if ($topicAticle) {
            return response()->json([
                'status' => true,
                'topicAticle' => $topicAticle

            ], 200);
        }
    }
    public function updateTopicArticle($id)
    {
        $topicAticle = TArticle::findOrFail($id);
        $form = request()->validate([
            'name' => 'string',
            'content' => 'string',
        ]);


        $topicAticle->update($form);

        return response()->json([
            'status' => true,
            'message' => 'Topic article created Successfully'

        ], 200);
    }

    public function topicArticleDestroy($id)
    {
        $topicA = TArticle::findOrFail($id);

        $topicA->delete();

        return response()->json([
            'status' => true,
            'message' => 'Topic article deleted Successfully'

        ], 200);
    }
}
