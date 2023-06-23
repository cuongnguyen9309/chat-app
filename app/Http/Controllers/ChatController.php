<?php

namespace App\Http\Controllers;

use App\Events\ReceiveChat;
use App\Events\TestEvent;
use App\Models\Attachment;
use App\Models\FileType;
use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\Message;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(Request $request): View
    {
        $user = User::findOrFail(Auth::id());
        $user->makeVisible(['add_friend_link']);
        $messages_sent = DB::table('messages')
            ->where('sender_id', Auth::id())
            ->select('id', 'content', 'created_at', 'receiver_id as user_id', DB::raw("'0' is_received"));
        $message_receive = DB::table('messages')
            ->where('receiver_id', Auth::id())
            ->select('id', 'content', 'created_at', 'sender_id as user_id', DB::raw("'1' is_received"));
        $messages_union = DB::query()
            ->fromSub($messages_sent->union($message_receive), 'messages');
        $messages = DB::query()
            ->from($messages_union, 'max')
            ->leftJoinSub($messages_union, 'bigger', function (JoinClause $join) {
                $join->on('max.user_id', '=', 'bigger.user_id')->on('max.id', '<', 'bigger.id');
            })->select('max.*')->whereNull('bigger.user_id');

        $friends = DB::table('users')
            ->join('friendship', 'friendship.friend_id', '=', 'users.id')
            ->leftJoinSub(DB::table('messages')
                ->where('receiver_id', Auth::id())
                ->whereNull('seen_at')
                ->select(DB::raw("count(sender_id) as unread_num,sender_id"))
                ->groupBy('sender_id'), 'messages', function (JoinClause $join) {
                $join->on('messages.sender_id', '=', 'users.id');
            })
            ->leftJoinSub($messages,
                'last_message',
                function (JoinClause $join) {
                    $join->on('users.id', '=', 'last_message.user_id');
                })
            ->where('friendship.user_id', Auth::id())
            ->select('users.*', 'messages.unread_num', 'last_message.content as last_content', 'last_message.created_at as last_sent', 'last_message.is_received as last_message_is_received')
            ->orderBy('last_sent', 'DESC')
            ->get();
        $joined_groups = DB::table('groups')
            ->join('group_user', 'groups.id', '=', 'group_user.group_id')
            ->leftJoinSub(DB::table('group_message_user_unseen')
                ->join('group_messages', 'group_messages.id', '=', 'group_message_user_unseen.group_message_id')
                ->where('group_message_user_unseen.user_id', Auth::id())
                ->groupBy('group_messages.receiver_id')
                ->select(DB::raw('group_messages.receiver_id,count(*) as unread_num'))
                , 'unseen_message',
                function (JoinClause $join) {
                    $join->on('unseen_message.receiver_id', '=', 'groups.id');
                })
            ->leftJoinSub(DB::table('group_messages as max')
                ->leftJoin('group_messages as bigger', function (JoinClause $join) {
                    $join->on('max.receiver_id', '=', 'bigger.receiver_id')->on('max.id', '<', 'bigger.id');
                })
                ->join('users', 'max.sender_id', '=', 'users.id')
                ->whereNull('bigger.id')
                ->select('max.*', 'users.name as sender_name')
                , 'last_message', function (JoinClause $join) {
                    $join->on('last_message.receiver_id', '=', 'groups.id');
                })
            ->where('group_user.user_id', Auth::id())
            ->select('groups.*', 'unseen_message.unread_num', 'last_message.created_at as last_sent', 'last_message.content as last_content',
                'last_message.sender_id as last_message_sender_id', 'last_message.sender_name as last_message_sender_name')
            ->orderBy('last_sent', 'DESC')
            ->get();
        $friendRequests = $user->isRequestingToBeFriend;
        $groupRequests = $user->pending_groups;


        $input = $request->get('search') ?? null;
        if (!is_null($input)) {
            $input = trim($input);
            $search_contacts = DB::query()
                ->fromSub(DB::table('users')
                    ->join('friendship', 'friendship.friend_id', '=', 'users.id')
                    ->where('friendship.user_id', Auth::id())
                    ->select('id', 'name', 'image_url', DB::raw("'user' type"))
                    ->union(DB::table('groups')
                        ->join('group_user', 'groups.id', '=', 'group_user.group_id')
                        ->where('group_user.user_id', Auth::id())
                        ->select('id', 'name', 'image_url', DB::raw("'group' type"))), 'contacts')
                ->where('name', 'like', '%' . $input . '%')->paginate(8, ['*'], 'search-contacts');
            $search_contacts_page_num = $search_contacts->lastPage();
            $search_contacts = $this->toCollection($search_contacts);
            $search_contacts_page = $request->get('search-contacts') ?? 1;

            $search_messages = DB::query()
                ->fromSub(
                    DB::query()->fromSub(
                        DB::table('messages')
                            ->join('users as senders', 'senders.id', '=', 'messages.sender_id')
                            ->join('users as receivers', 'receivers.id', '=', 'messages.receiver_id')
                            ->where('messages.sender_id', Auth::id())
                            ->select('messages.id as message_id', 'messages.created_at', 'messages.content',
                                'messages.receiver_id as id', 'receivers.name as name', 'receivers.image_url as image_url')
                            ->union(DB::table('messages')
                                ->join('users as senders', 'senders.id', '=', 'messages.sender_id')
                                ->join('users as receivers', 'receivers.id', '=', 'messages.receiver_id')
                                ->where('messages.receiver_id', Auth::id())
                                ->select('messages.id as message_id', 'messages.created_at',
                                    'messages.content', 'messages.sender_id as id', 'senders.name as name', 'senders.image_url as image_url'))
                        , 'messages')
                        ->select('messages.*', DB::raw("'user' type"))
                        ->union(DB::table('group_messages as messages')
                            ->join('users as senders', 'senders.id', '=', 'messages.sender_id')
                            ->joinSub(DB::table('groups')
                                ->join('group_user', 'groups.id', '=', 'group_user.group_id')
                                ->where('group_user.user_id', Auth::id())->select('groups.*'), 'receivers', function (JoinClause $join) {
                                $join->on('receivers.id', '=', 'messages.receiver_id');
                            })
                            ->select('messages.id as message_id', 'messages.created_at', 'messages.content',
                                'messages.receiver_id as id', 'receivers.name as name', 'receivers.image_url as image_url', DB::raw("'group' type")))
                    , 'contacts')
                ->where('content', 'like', '%' . $input . '%')->paginate(5, ['*'], 'search-messages');
            $search_messages_page_num = $search_messages->lastPage();
            $search_messages = $this->toCollection($search_messages);
            $search_messages_page = $request->get('search-messages') ?? 1;

        } else {
            $search_contacts = null;
            $search_messages = null;
            $search_contacts_page = 0;
            $search_contacts_page_num = 0;
            $search_messages_page = 0;
            $search_messages_page_num = 0;
        }

        $reactions = Reaction::all();
        return view('client.pages.chat', compact('friends', 'joined_groups', 'friendRequests', 'groupRequests',
            'search_contacts', 'search_contacts_page', 'search_contacts_page_num',
            'search_messages', 'search_messages_page', 'search_messages_page_num',
            'input', 'user', 'reactions'));
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
                    ->leftJoinSub(DB::table('group_message_user_unseen')->where('user_id', Auth::id()), 'group_message_user_unseen', 'group_messages.id', '=', 'group_message_user_unseen.group_message_id')
                    ->select('group_messages.*',
                        'group_message_user_unseen.created_at as unseen',
                        'senders.name as sender_name',
                        'senders.image_url as sender_image_url',
                        'receivers.name as receiver_name',
                        'receivers.image_url as receiver_image_url',
                        DB::raw("'group' as receiver_type")
                    )
                    ->where('group_messages.receiver_id', $id)
                    ->orderBy('id', 'DESC')
                    ->limit(10)
                    ->get();
                break;
            default:
                abort(404);
        }
        $message_ids = $messages->pluck('id')->all();
        $messageType = $type === 'group' ? 'App\Models\GroupMessage' : 'App\Models\Message';
        $attachments = DB::table('attachments')
            ->join('file_types', 'file_types.id', '=', 'attachments.file_type_id')
            ->whereIn('attachments.attachmentable_id', $message_ids)->where('attachments.attachmentable_type', $messageType)
            ->select('attachments.*', 'file_types.image_url as thumbnail')
            ->get();
        $attachment_map = [];
        foreach ($attachments as $attachment) {
            $attachment_map[$attachment->attachmentable_id] = $attachment;
        }
        $reaction_table = $type === 'group' ? 'group_message_reaction_user' : 'message_reaction_user';
        $reactions = DB::table('reactions')
            ->join($reaction_table . ' as reaction_relation', 'reaction_relation.reaction_id', '=', 'reactions.id')
            ->join('users', 'users.id', 'reaction_relation.user_id')
            ->whereIn('reaction_relation.' . ($type === 'group' ? 'group_' : '') . 'message_id', $message_ids)
            ->select('reactions.*', 'users.name as user_name', 'reaction_relation.user_id as user_id', 'reaction_relation.' . ($type === 'group' ? 'group_' : '') . 'message_id as message_id', 'reaction_relation.id as relation_id')
            ->get();
        $reaction_map = [];
        foreach ($reactions as $reaction) {
            $reaction_map[$reaction->message_id][] = $reaction;
        }
        foreach ($messages as $message) {
            $message->attachment = $attachment_map[$message->id] ?? null;
            $message->reaction = $reaction_map[$message->id] ?? null;
        }
        $reverse = $messages->reverse()->values()->all();
        return response()->json(['recent_messages' => $reverse]);
    }

    public function sendChat(Request $request)
    {
        switch ($request['receiver_type']) {
            case 'user':
                $message = Message::create([
                    'content' => $request->get('content'),
                    'sender_id' => Auth::id(),
                    'receiver_id' => $request['receiver_id']
                ]);
                break;
            case 'group':
                $message = GroupMessage::create([
                    'content' => $request->get('content'),
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
        $attachment = null;
        $attachmentThumbnail = null;
        if ($request->file('attachment')) {
            $file_name = uniqid() . '_' . $request->file('attachment')->getClientOriginalName();
            $request->file('attachment')->storeAs('attachments', $file_name, 'asset_public');
            $attachment = $message->attachment()->create([
                'name' => $file_name,
                'file_type_id' => DB::table('file_types')
                    ->join('file_type_extension', 'file_type_extension.file_type_id', '=', 'file_types.id')
                    ->select('file_types.id as id', 'file_type_extension.name as name')
                    ->where('file_type_extension.name', $request->file('attachment')->getClientOriginalExtension())
                    ->first()->id,
                'file_size' => $request->file('attachment')->getSize()
            ]);
            $attachment->save();
            $message->attachment = $attachment;
            $message->attachment->thumbnail = $attachment->fileType->image_url;
            $attachmentThumbnail = $attachment->fileType->image_url;
        }
        broadcast(new ReceiveChat($message, $request['receiver_type'], $message->sender->name, $attachment, $attachmentThumbnail))->toOthers();
        return response()->json(['message' => $message]);
    }


    public function toCollection($collection)
    {
        return $collection->map(function ($row) {
            return $row;
        });
    }

    public function userStatus(Request $request)
    {
        $events = $request->events;
        foreach ($events as $event) {
            if ($event['name'] === 'member_removed') {
                $user = User::find($event['user_id']);
                $user->status = 'offline';
                $user->save();
            } elseif ($event['name'] === 'member_added') {
                error_log('member_added');
                $user = User::find($event['user_id']);
                $user->status = 'online';
                $user->save();
            }
        }
        return response()->json('ok');
    }
}
