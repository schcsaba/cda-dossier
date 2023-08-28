<?php

use App\Http\Controllers\UploadController;
use App\Http\Controllers\UploadStepController;
use Illuminate\Support\Facades\Artisan;
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

/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/

/* ex. localhost:80/api/uploads?include=upload_step&filter[client_app]=25&filter[status]=3&filter[start_date]=2023-01-25&filter[end_date]=2023-01-28&filter[client_id]=1&filter[user_id]=10831&filter[id]=01gqq238mchk4b4h1368rx7br5
// localhost:80/api/uploads
// ?include=steps
// &filter[client_app]=25
// &filter[status]=3
// &filter[start_date]=2023-01-25
// &filter[end_date]=2023-01-28
// &filter[client_id]=1
// &filter[user_id]=10831
// &filter[per_page]=2
// &filter[id]=01gqq238mchk4b4h1368rx7br5*/
Route::get('/uploads', [UploadController::class, 'index']);
Route::get('/uploads/list', [UploadController::class, 'availableUploadslist']);
Route::get('/uploads/status', [UploadController::class, 'uploadStatusList']);

// example localhost:80/api/uploads/01gqm9bv6hyhr6tcgq6gygrjeg?include=steps,results&append=
Route::get('/uploads/{id}', [UploadController::class, 'show']);
Route::get('/uploads/{id}/results', [UploadController::class, 'results']);

Route::get('/uploads/{id}/logs/{fileName}', [UploadController::class, 'downloadLogFile']);
Route::get('/uploads/{id}/payload', [UploadController::class, 'downloadPayloadFile']);
Route::get('/uploads/{id}/originals/{fileName}', [UploadController::class, 'downloadOriginalFile']);
Route::get('/steps/{id}/{fileName}', [UploadStepController::class, 'downloadStepFile']);

Route::post('/command/uploads/{category}/{type}', [UploadController::class, 'upload']);

Route::get('/steps/{id}', [UploadStepController::class, 'show']);