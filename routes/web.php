<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatRoomController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Auth;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::middleware(['auth', 'checkIfBanned'])->group(function () {
    Route::get('/', [ChatRoomController::class, 'index'])->name('chatrooms.index');
    Route::get('/chatrooms/{chatRoom}', [ChatRoomController::class, 'show'])->name('chatrooms.show');
    
    Route::post('/chatrooms/leave', [ChatRoomController::class, 'leaveRoom'])->name('chatrooms.leave');
    Route::post('/chatrooms/{chatRoom}/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/chatrooms/{chatRoom}/users', [ChatRoomController::class, 'getUsers'])->name('chatrooms.users');
	Route::post('/chatrooms/{chatRoom}/ban', [ChatRoomController::class, 'banUser'])->name('chatrooms.banUser');
});
Route::post('/chatrooms/switch', [ChatRoomController::class, 'switchRoom'])->name('chatrooms.switch');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
