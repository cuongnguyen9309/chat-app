<?php


use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\GroupController as ClientGroupController;
use App\Http\Controllers\GroupMessageController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MessageController as ClientMessageController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\SignUpController;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\UserController as ClientUserController;

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
Route::get('/user/{id?}', [ClientUserController::class, 'getUser'])->name('user.info');
Route::get('/user/add-friend/{id?}', [ClientUserController::class, 'addFriend'])->name('friend.add');
Route::get('/user/accept-friend/{id?}', [ClientUserController::class, 'acceptFriend'])->name('friend.accept');
Route::get('/user/remove-friend/{id?}', [ClientUserController::class, 'removeFriend'])->name('friend.remove');

Route::post('/group', [ClientGroupController::class, 'store'])->name('group.store');
Route::get('/group/accept/{id?}', [ClientGroupController::class, 'acceptGroup'])->name('group.accept');
Route::get('/group/leave/{id?}', [ClientGroupController::class, 'leaveGroup'])->name('group.leave');
Route::get('/group/{id?}', [ClientGroupController::class, 'getGroup'])->name('group.info');

Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::resource('user', UserController::class);
    Route::post('/user/add-friend', [UserController::class, 'addFriend'])->name('friend.add');
    Route::get('/user/remove-friend/{userId}/{id?}', [UserController::class, 'removeFriend'])->name('friend.remove');
    Route::post('/user/join-group', [UserController::class, 'joinGroup'])->name('user.join.group');
    Route::get('/user/{userId}/leave-group/{id?}', [UserController::class, 'leaveGroup'])->name('user.leave.group');
    Route::resource('group', GroupController::class);
    Route::post('/group/user/add', [GroupController::class, 'addUser'])->name('group.user.add');
    Route::get('/group/remove/{groupId}/{id?}', [GroupController::class, 'removeUser'])->name('group.user.remove');
    Route::resource('message', MessageController::class);
    Route::get('/message/remove/{type?}/{id?}', [MessageController::class, 'removeMessage'])->name('message.remove');
    Route::get('/message/restore/{type?}/{id?}', [MessageController::class, 'restoreMessage'])->name('message.restore');
    Route::get('groupMessage', [GroupMessageController::class, 'adminIndex'])->name('groupMessage');
});

