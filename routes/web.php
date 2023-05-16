<?php


use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupMessageController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SignUpController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', [ChatController::class, 'index'])->name('chat.index')->middleware('auth');
Route::get('/home', [ChatController::class, 'index'])->name('home')->middleware('auth');
Route::get('chat/', [ChatController::class, 'index'])->name('chat.index')->middleware('auth');
Route::get('chat/recent/{type?}/{id?}', [ChatController::class, 'recent'])->name('chat.recent')->middleware('auth');
Route::post('chat/send', [ChatController::class, 'sendChat'])->name('chat.send')->middleware('auth');
Route::get('/login', [LoginController::class, 'index'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'attempt'])->name('login.attempt')->middleware('guest');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');
Route::get('/signup', [SignUpController::class, 'index'])->name('signup')->middleware('guest');
Route::post('/signup', [SignUpController::class, 'store'])->name('signup.store')->middleware('guest');
Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::resource('user', UserController::class);
    Route::get('group', [GroupController::class, 'adminIndex'])->name('group');
    Route::get('message', [MessageController::class, 'adminIndex'])->name('message');
    Route::get('groupMessage', [GroupMessageController::class, 'adminIndex'])->name('groupMessage');
});

