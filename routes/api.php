<?php

use App\Http\Controllers\LibraryVersionController;
use App\Http\Middleware\ValidateApiRequest;
use Illuminate\Support\Facades\Route;

Route::middleware([ValidateApiRequest::class, 'throttle:60,1'])->group(function () {
    Route::get('/library-versions', [LibraryVersionController::class, 'getVersions']);
});
