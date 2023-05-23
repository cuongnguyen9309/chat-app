<?php

namespace App\Http\Controllers;

use App\Events\FriendListUpdated;
use App\Events\ReceivedFriendRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use function PHPUnit\Framework\throwException;

class UserController extends Controller
{
    public function getUser($id)
    {
        $user = User::findOrFail($id);
        return response()->json(['user' => $user]);
    }

    public function addFriend($id)
    {
        if (Auth::id() === $id) {
            abort(404);
        }
        $friend = User::find($id);
        $user = User::find(Auth::id());
        $user->inRequestFriends()->attach($id, ['status' => 'pending']);
        broadcast(new ReceivedFriendRequest($id))->toOthers();
        return response()->json(compact('friend'));
    }

    public function acceptFriend($id)
    {
        $pendingFriendRequest = DB::table('friendship')->where('user_id', $id)->where('friend_id', Auth::id())->get();
        $user = User::findOrFail(Auth::id());
        $friend = User::findOrFail($id);
        if ($pendingFriendRequest) {
            DB::transaction(function () use ($id, $user) {
                DB::table('friendship')
                    ->where('user_id', $id)
                    ->where('friend_id', Auth::id())
                    ->update(['status' => 'accepted', 'updated_at' => Carbon::now()->format('Y-m-d H:i:s')]);
                $user->friends()->attach($id, ['status' => 'accepted']);
            });
            broadcast(new FriendListUpdated($id))->toOthers();
            return response()->json(['friend' => $friend]);
        } else {
            abort(404);
        }
    }

    public function removeFriend($id)
    {
        User::findOrFail($id)->friends()->detach(Auth::id());
        User::findOrFail(Auth::id())->friends()->detach($id);
        broadcast(new FriendListUpdated($id))->toOthers();
        return response()->json(['message' => 'success']);
    }
}
