<?php


use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizattemptController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\UserCourseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/auth/register', [AuthController::class, 'createUser']);
Route::post('/auth/login', [AuthController::class, 'loginUser']);
Route::put('/change/password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');
Route::get('/users/{email}', [AuthController::class, 'checkUser']);

// ******ADMIN *******



//**Quiz***
//Instructors
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/instructors', [InstructorController::class, 'insIndex']);
    Route::post('/add/instructor', [InstructorController::class, 'insStore']);
    Route::put('/update/instructor/{id}', [InstructorController::class, 'insUpdate']);
    Route::delete('/delete/instructor/{id}', [InstructorController::class, 'insDestroy']);

    Route::get('/send', [InstructorController::class, 'try']);
});
//course
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/courses', [CourseController::class, 'courseIndex']);
    Route::get('/my/course', [CourseController::class, 'myCourse']);
    Route::post('/add/course', [CourseController::class, 'courseStore']);
    Route::post('/course/enrole', [CourseController::class, 'courseEnrole']);
    Route::get('/pending/enrole', [CourseController::class, 'pendingEnrole']);
    Route::get('/pending/all', [CourseController::class, 'indexEnrole']);
    Route::put('/enrole/approve/{id}', [CourseController::class, 'approveEnrole']);
    Route::delete('/enrole/disapproved/{id}', [CourseController::class, 'disapprovedEnrole']);

    Route::get('/edit/course/{id}', [CourseController::class, 'editCourse']);
    Route::get('/show/course/{id}', [CourseController::class, 'courseShow']);

    Route::put('/update/course/{id}', [CourseController::class, 'courseUpdate']);

    Route::delete('/delete/course/{id}', [CourseController::class, 'courseDestroy']);
});
//topic
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/topics', [TopicController::class, 'topicIndex']);
    Route::post('/add/topic', [TopicController::class, 'topicStore']);
    Route::post('/add/topic/file', [TopicController::class, 'fileStore']);
    Route::get('/download/topic/file/{id}', [TopicController::class, 'downloadFile']);
    Route::post('/add/topic/article', [TopicController::class, 'articleStore']);
    Route::post('/update/topic/article/{id}', [TopicController::class, 'updateTopicArticle']);
    Route::get('/show/topic/article/{id}', [TopicController::class, 'showTopicArticle']);
    Route::get('/show/topic/{id}', [TopicController::class, 'topicShow']);

    Route::put('/update/topic/{id}', [TopicController::class, 'topicUpdate']);
    Route::get('/topic/article/{slug}', [TopicController::class, 'checkSlug']);
    Route::post('/topic/article/slug', [TopicController::class, 'slugCreate']);
    Route::delete('/delete/topic/{id}', [TopicController::class, 'topicDestroy']);
    Route::delete('/delete/topic/article/{id}', [TopicController::class, 'topicArticleDestroy']);
    Route::delete('/delete/topic/content/{id}', [TopicController::class, 'topicContentDestroy']);
});
//quiz
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/quizzes', [QuizController::class, 'Qindex']);
    Route::post('/add/quiz', [QuizController::class, 'Qstore']);

    Route::get('/show/quiz/{id}', [QuizController::class, 'Qshow']);

    Route::put('/update/quiz/{id}', [QuizController::class, 'Qupdate']);

    Route::delete('/delete/quiz/{id}', [QuizController::class, 'Qdestroy']);
});

// level
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/levels', [LevelController::class, 'levelIndex']);
    Route::post('/add/level', [LevelController::class, 'levelStore']);

    Route::get('/show/level/{id}', [LevelController::class, 'levelShow']);

    Route::put('/update/level/{id}', [LevelController::class, 'levelUpdate']);

    Route::delete('/delete/level/{id}', [LevelController::class, 'levelDestroy']);
});

//questions
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/questions', [QuestionController::class, 'Qsnindex']);
    Route::post('/add/question', [QuestionController::class, 'Qsnstore']);
    Route::post('/topic/level', [QuestionController::class, 'levelTopic']);
    Route::get('/show/question/{id}', [QuestionController::class, 'Qsnshow']);

    Route::put('/update/question/{id}', [QuestionController::class, 'Qsnupdate']);

    Route::delete('/delete/question/{id}', [QuestionController::class, 'Qsndestroy']);
});

///attemptHistory
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/quiz/attemt/history/{id}', [UserCourseController::class, 'attemptHistory']);
});


// ******USER *******


//// quiz for user
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/view/course/{id}', [UserCourseController::class, 'ViewCourse']);
    Route::get('/view/quiz/{id}', [UserCourseController::class, 'ViewQuiz']);
    Route::post('/attempt', [UserCourseController::class, 'attemptQuiz']);
    Route::get('/quiz/result/{id}', [UserCourseController::class, 'quizResult']);
    Route::get('/quiz/result', [UserCourseController::class, 'quizResultIndex']);
    Route::put('/quiz/reattempt/{id}', [UserCourseController::class, 'quizReattempt']);
    //Route::get('try/{id}', [UserCourseController::class, 'Try']);
});



