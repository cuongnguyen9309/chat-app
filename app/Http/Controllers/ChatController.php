<?php

namespace App\Http\Controllers;

use App\Events\SendChatEvent;
use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\Message;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(): View
    {
        $user = User::findOrFail(Auth::id());
        $friends = $user->friends;
        $joined_groups = $user->joined_groups;
        return view('client.pages.chat', compact('friends', 'joined_groups'));
    }

    public function recent($type, $id)
    {
        switch ($type) {
            case 'user':
                $messages = DB::table('messages')
                    ->join('users as senders', 'messages.sender_id', '=', 'senders.id')
                    ->join('users as receivers', 'messages.receiver_id', '=', 'receivers.id')
                    ->select('messages.*', 'senders.name as sender_name', 'receivers.name as receiver_name')
                    ->where(function ($query) use ($id) {
                        $query->where('messages.sender_id', Auth::id())->where('messages.receiver_id', $id);
                    })
                    ->orWhere(function ($query) use ($id) {
                        $query->where('messages.sender_id', $id)->where('messages.receiver_id', Auth::id());
                    })
                    ->orderBy('created_at', 'DESC')
                    ->limit(10)
                    ->get();
                break;
            case 'group':
                $messages = DB::table('group_messages')
                    ->join('users as senders', 'group_messages.sender_id', '=', 'senders.id')
                    ->join('groups as receivers', 'group_messages.receiver_id', '=', 'receivers.id')
                    ->select('group_messages.*', 'senders.name as sender_name', 'receivers.name as receiver_name')
                    ->where('group_messages.receiver_id', $id)
                    ->orderBy('created_at', 'DESC')
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
        try {
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
                    break;
                default:
                    abort(404);
            }
            $message['sender_name'] = $message->sender->name;
            $message['receiver_name'] = $message->receiver->name;
            $message['type'] = $request['receiver_type'];
            SendChatEvent::dispatch($message);
            return response()->json(['message' => $message]);
        } catch (Exception $error) {
            return response()->json(['message' => 'Something wrong here']);
        }
    }
}
