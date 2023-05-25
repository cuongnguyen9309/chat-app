<?php

namespace App\Http\Controllers;

use App\Events\FriendListUpdated;
use App\Events\ReceivedFriendRequest;
use App\Models\Group;
use App\Models\User;
use Doctrine\DBAL\Query\QueryBuilder;
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

    public function retrieveMessage(Request $request)
    {
        $messages = null;
        switch ($request->get('partner_type')) {
            case 'user':
                $messages = DB::table('messages')
                    ->join('users as senders', 'messages.sender_id', '=', 'senders.id')
                    ->join('users as receivers', 'messages.receiver_id', '=', 'receivers.id')
                    ->select('messages.*',
                        'senders.name as sender_name',
                        'senders.image_url as sender_image_url',
                        'receivers.name as receiver_name',
                        'receivers.image_url as receivers_image_url',
                        DB::raw("'user' as receiver_type")
                    )
                    ->where(function ($query) use ($request) {
                        $query
                            ->where(function ($query) use ($request) {
                                $query->where('messages.sender_id', Auth::id())->where('messages.receiver_id', $request->get('partner_id'));
                            })
                            ->orWhere(function ($query) use ($request) {
                                $query->where('messages.sender_id', $request->get('partner_id'))->where('messages.receiver_id', Auth::id());
                            });
                    })
                    ->where('messages.created_at', '<', $request->get('headTime'))
                    ->orderBy('messages.created_at', 'DESC')
                    ->limit(10)
                    ->get();;
                break;
            case 'group':
                $messages = DB::table('group_messages')
                    ->join('users as senders', 'group_messages.sender_id', '=', 'senders.id')
                    ->join('groups as receivers', 'group_messages.receiver_id', '=', 'receivers.id')
                    ->select('group_messages.*',
                        'senders.name as sender_name',
                        'senders.image_url as sender_image_url',
                        'receivers.name as receiver_name',
                        'receivers.image_url as receiver_image_url',
                        DB::raw("'group' as receiver_type")
                    )
                    ->where('group_messages.receiver_id', $request->get('partner_id'))
                    ->where('group_messages.created_at', '<', $request->get('headTime'))
                    ->orderBy('group_messages.created_at', 'DESC')
                    ->limit(10)
                    ->get();
                break;
            default:
                abort(404);
        }
        return response()->json(compact('messages'));
    }
}
