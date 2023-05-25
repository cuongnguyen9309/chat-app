<?php

namespace App\Http\Controllers;

use App\Events\ReceiveChat;
use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\Message;
use App\Models\User;
use Exception;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(): View
    {
        $user = User::findOrFail(Auth::id());
        $friends = DB::table('users')
            ->join('friendship', 'friendship.friend_id', '=', 'users.id')
            ->leftJoinSub(DB::table('messages')
                ->where('receiver_id', Auth::id())
                ->whereNull('seen_at')
                ->select(DB::raw("count(sender_id) as unread_num,sender_id"))
                ->groupBy('sender_id'), 'messages', function (JoinClause $join) {
                $join->on('messages.sender_id', '=', 'users.id');
            })
            ->leftJoinSub(DB::table('messages as max')
                ->where('max.receiver_id', Auth::id())
                ->leftJoin('messages as bigger', function (JoinClause $join) {
                    $join->on('max.sender_id', '=', 'bigger.sender_id')->on('max.id', '<', 'bigger.id');
                })
                ->select('max.*')
                ->whereNull('bigger.id'),
                'last_message',
                function (JoinClause $join) {
                    $join->on('users.id', '=', 'last_message.sender_id');
                })
            ->where('friendship.user_id', Auth::id())
            ->select('users.*', 'messages.unread_num', 'last_message.content as last_content', 'last_message.created_at as last_sent')
            ->orderBy('last_sent', 'DESC')
            ->get();
        $joined_groups = DB::table('groups')
            ->join('group_user', 'groups.id', '=', 'group_user.group_id')
            ->joinSub(DB::table('group_message_user_unseen')
                ->join('group_messages', 'group_messages.id', '=', 'group_message_user_unseen.group_message_id')
                ->where('group_message_user_unseen.user_id', Auth::id())
                ->groupBy('group_messages.receiver_id')
                ->select(DB::raw('group_messages.receiver_id,count(*) as unread_num'))
                , 'unseen_message',
                function (JoinClause $join) {
                    $join->on('unseen_message.receiver_id', '=', 'groups.id');
                })
            ->joinSub(DB::table('group_messages as max')
                ->leftJoin('group_messages as bigger', function (JoinClause $join) {
                    $join->on('max.receiver_id', '=', 'bigger.receiver_id')->on('max.id', '<', 'bigger.id');
                })
                ->whereNull('bigger.id')
                ->select('max.*')
                , 'last_message', function (JoinClause $join) {
                    $join->on('last_message.receiver_id', '=', 'groups.id');
                })
            ->where('group_user.user_id', Auth::id())
            ->select('groups.*', 'unseen_message.unread_num', 'last_message.created_at as last_sent', 'last_message.content as last_content')
            ->orderBy('last_sent', 'DESC')
            ->get();
//        dd($friends);
        $friendRequests = $user->isRequestingToBeFriend;
        $groupRequests = $user->pending_groups;
        return view('client.pages.chat', compact('friends', 'joined_groups', 'friendRequests', 'groupRequests'));
    }

    public function recent($type, $id)
    {
        switch ($type) {
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
                    ->where(function ($query) use ($id) {
                        $query->where('messages.sender_id', Auth::id())->where('messages.receiver_id', $id);
                    })
                    ->orWhere(function ($query) use ($id) {
                        $query->where('messages.sender_id', $id)->where('messages.receiver_id', Auth::id());
                    })
                    ->orderBy('created_at', 'DESC')
                    ->orderBy('id', 'DESC')
                    ->limit(10)
                    ->get();
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
                    ->where('group_messages.receiver_id', $id)
                    ->orderBy('created_at', 'DESC')
                    ->orderBy('id', 'DESC')
                    ->limit(10)
                    ->get();
                break;
            default:
                abort(404);
        }
        $reverse = $messages->reverse()->values()->all();
        return response()->json(['recent_messages' => $reverse]);
    }

    public function sendChat(Request $request)
    {
        $request = $request->all();

        switch ($request['receiver_type']) {
            case 'user':
                $message = Message::create([
                    'content' => $request['input'],
                    'sender_id' => Auth::id(),
                    'receiver_id' => $request['receiver_id']
                ]);
                break;
            case 'group':
                $message = GroupMessage::create([
                    'content' => $request['input'],
                    'sender_id' => Auth::id(),
                    'receiver_id' => $request['receiver_id']
                ]);
                $group = Group::find($request['receiver_id']);
                $users_id = $group->users->where('id', '!=', Auth::id())->pluck('id');
                $message->unseen_users()->attach($users_id);
                break;
            default:
                abort(404);
        }
        broadcast(new ReceiveChat($message, $request['receiver_type'], $message->sender->name))->toOthers();
        return response()->json(['message' => $message]);

    }
}
