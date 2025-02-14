<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ChatController;

Route::get('/', function () {
    return view('chat');
});

Route::post('/stream-chat', [ChatController::class, 'streamChat']);
