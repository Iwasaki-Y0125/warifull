<?php

use App\Http\Controllers\WeeklyBoardController;
use Illuminate\Support\Facades\Route;

Route::get('/', WeeklyBoardController::class)->name('weekly-board.index');
