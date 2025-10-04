<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/check-auth', function () {
    if (Auth::check()) {
        return response()->json(auth()->user());
    }
    return response()->json(null, 401);
});
