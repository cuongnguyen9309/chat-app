<?php


use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AutocompleteController;
use App\Http\Controllers\ChatAutocompleteController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\GoogleLoginController;
use App\Http\Controllers\GroupController as ClientGroupController;
use App\Http\Controllers\GroupMessageController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MessageController as ClientMessageController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\ShortURLController;
use App\Http\Controllers\SignUpController;
use App\Http\Middleware\IsAdmin;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\UserController as ClientUserController;
use Laravel\Socialite\Facades\Socialite;

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
Route::post('/pusher/presence', [ChatController::class, 'userStatus'])->name('user.observe');

Route::get('/', [ChatController::class, 'index'])->name('chat.index')->middleware('auth');
Route::get('/home', [ChatController::class, 'index'])->name('home')->middleware('auth');
Route::get('chat/', [ChatController::class, 'index'])->name('chat.index')->middleware('auth');
Route::get('chat/recent/{type?}/{id?}', [ChatController::class, 'recent'])->name('chat.recent')->middleware('auth');
Route::post('chat/send', [ChatController::class, 'sendChat'])->name('chat.send')->middleware('auth');
Route::post('/search', [ChatController::class, 'search'])->name('search');
Route::get('/autocomplete/chat', [ChatAutocompleteController::class, 'autocomplete'])->name('chat.autocomplete');
Route::get('/attachment/download/{name?}', [AttachmentController::class, 'download'])->name('attachment.download');

Route::get('/login', [LoginController::class, 'index'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'attempt'])->name('login.attempt')->middleware('guest');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');
Route::get('/auth/google/redirect', [GoogleLoginController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleLoginController::class, 'callback']);

Route::get('/signup', [SignUpController::class, 'index'])->name('signup')->middleware('guest');
Route::post('/signup', [SignUpController::class, 'store'])->name('signup.store')->middleware('guest');

Route::get('/short/{url_key}', [ShortURLController::class, 'navigate'])->name('short.url');
Route::middleware('auth')->group(function () {

    Route::get('/user/{keyword?}', [ClientUserController::class, 'getUser'])->name('user.info');
    Route::post('/user/update', [ClientUserController::class, 'updateUser'])->name('user.update');
    Route::get('/user/add-friend/{id?}', [ClientUserController::class, 'addFriend'])->name('friend.add');
    Route::get('/user/add-friend-no-confirm/{id?}', [ClientUserController::class, 'addFriendNoConfirm'])->name('friend.add.no.confirm');
    Route::get('/user/accept-friend/{id?}', [ClientUserController::class, 'acceptFriend'])->name('friend.accept');
    Route::get('/user/remove-friend/{id?}', [ClientUserController::class, 'removeFriend'])->name('friend.remove');
    Route::post('/user/message/retrieve', [ClientUserController::class, 'retrieveMessage'])->name('user.message.retrieve');
    Route::post('/user/message/read', [ClientUserController::class, 'readMessage'])->name('user.message.read');
    Route::get('/user/message/search/{type?}/{id?}/{from?}/{to?}', [ClientUserController::class, 'searchMessage'])->name('user.message.search');
    Route::get('/user/online/{id?}', [ClientUserController::class, 'userOnline'])->name('user.online');
    Route::get('/user/offline/{id?}', [ClientUserController::class, 'userOffline'])->name('user.offline');
    Route::post('/user/react', [ClientUserController::class, 'react'])->name('user.react');


    Route::post('/group', [ClientGroupController::class, 'store'])->name('group.store');
    Route::get('/group/accept/{id?}', [ClientGroupController::class, 'acceptGroup'])->name('group.accept');
    Route::get('/group/leave/{id?}', [ClientGroupController::class, 'leaveGroup'])->name('group.leave');
    Route::get('/group/{id?}', [ClientGroupController::class, 'getGroup'])->name('group.info');
});


Route::middleware([IsAdmin::class])->prefix('admin')->name('admin.')->group(function () {
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

