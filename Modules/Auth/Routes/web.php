<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Controllers\AuthController;

Route::get('/auth', [AuthController::class, 'index']);
