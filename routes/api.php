<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tool API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your tool. These routes
| are loaded by the ServiceProvider of your tool. They are protected
| by your tool's "Authorize" middleware by default. Now, go build!
|
*/

// Route::get('/', function (Request $request) {
//     //
// });

Route::post('/upload', \Vqs\Papertrail\Http\Controllers\UploadPdfController::class);
Route::get('/documents', \Vqs\Papertrail\Http\Controllers\ListDocumentsController::class);
Route::get('/documents/{document}/thumb', \Vqs\Papertrail\Http\Controllers\GetThumbController::class)
    ->name('papertrail.documents.thumb');
Route::get('/documents/{document}/pages/{page}', \Vqs\Papertrail\Http\Controllers\GetPageImageController::class)
    ->whereNumber('page')
    ->name('papertrail.documents.page');

// Form fields and placeholders
Route::get('/documents/{document}/fields', \Vqs\Papertrail\Http\Controllers\ListPdfFieldsController::class)
    ->name('papertrail.documents.fields');
Route::put('/fields/{field}', \Vqs\Papertrail\Http\Controllers\UpdatePdfFieldController::class)
    ->name('papertrail.fields.update');
Route::get('/placeholders', \Vqs\Papertrail\Http\Controllers\ListPlaceholdersController::class)
    ->name('papertrail.placeholders');