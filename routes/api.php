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
Route::post('/upload', \Fanmade\Papertrail\Http\Controllers\UploadPdfController::class);
Route::get('/documents', \Fanmade\Papertrail\Http\Controllers\ListDocumentsController::class);
ROute::delete('/documents/{document}', \Fanmade\Papertrail\Http\Controllers\DeletePdfController::class);
Route::get('/documents/{document}/thumb', \Fanmade\Papertrail\Http\Controllers\GetThumbController::class)
    ->name('papertrail.documents.thumb');
Route::get('/documents/{document}/pages/{page}', \Fanmade\Papertrail\Http\Controllers\GetPageImageController::class)
    ->whereNumber('page')
    ->name('papertrail.documents.page');

// Form fields and placeholders
Route::get('/documents/{document}/fields', \Fanmade\Papertrail\Http\Controllers\ListPdfFieldsController::class)
    ->name('papertrail.documents.fields');
Route::put('/fields/{field}', \Fanmade\Papertrail\Http\Controllers\UpdatePdfFieldController::class)
    ->name('papertrail.fields.update');
Route::get('/placeholders', \Fanmade\Papertrail\Http\Controllers\ListPlaceholdersController::class)
    ->name('papertrail.placeholders');