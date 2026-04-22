<?php

use App\Http\Controllers\MemberVacationController;
use App\Http\Controllers\WeeklyBoardController;
use Illuminate\Support\Facades\Route;

Route::get('/', WeeklyBoardController::class)->name('weekly-board.index');
Route::put('/members/{member}/vacations', MemberVacationController::class)->name('members.vacations.update');
