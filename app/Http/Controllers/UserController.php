<?php

namespace App\Http\Controllers;

use App\Events\FriendListUpdated;
use App\Events\MessageReact;
use App\Events\ReceivedFriendRequest;
use App\Events\UserOffline;
use App\Events\UserOnline;
use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\Message;
use App\Models\User;
use Doctrine\DBAL\Query\QueryBuilder;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use function PHPUnit\Framework\throwException;

class UserController extends Controller
{
    public function getUser($keyword)
    {
        $keyword = trim($keyword);
        $users = DB::table('users')->where('name', 'like', '%' . $keyword . '%')->orWhere('email', 'like', '%' . $keyword . '%')->get();
        return response()->json(['users' => $users]);
    }

    public function updateUser(Request $request)
    {
        $user = DB::transaction(function () use ($request) {
            $id = Auth::id();
            $user = User::findOrFail(Auth::id());
            $request->validate([
                'name' => ['required', Rule::unique('users')->ignore($id, 'id')],
                'image' => 'mimes:jpeg,png,gif'
            ]);
            $name = $request->get('name');
            $user->name = $name;
            if ($request->file('image')) {
                $image_url = $request->file('image')
                    ->storeAs('images/avatars',
                        uniqid() . '_' . $request->file('image')->getClientOriginalName(),
                        'asset_public');
                $user->image_url = $image_url;
            }
            if ((int)$request->get('is_accept_stranger_request')) {
                $user->is_accept_stranger_request = 1;
            } else {
                $user->is_accept_stranger_request = 0;
            }
            $user->save();
            return $user;
        });
        return response()->json(['user' => $user]);
    }

    public function addFriend($id)
    {
        if (Auth::id() === $id) {
            abort(404);
        }
        $friend = User::find($id);
        if ($friend->is_accept_stranger_request) {
            $user = User::find(Auth::id());
            $user->inRequestFriends()->attach($id, ['status' => 'pending']);
            broadcast(new ReceivedFriendRequest($id, $user))->toOthers();
        } else {
            return response()->json(['error' => 'User does not accept stranger friend request']);
        }
        return response()->json(compact('friend'));
    }

    public function addFriendNoConfirm($id)
    {
        if (Auth::id() === $id) {
            abort(404);
        }
        $friend = User::find($id);
        $user = User::find(Auth::id());
        if ($user->friends->contains('id', $id)) {
            return response()->json('already friend');
            abort(404);
        }
        $user->friends()->attach($id, ['status' => 'accepted']);
        $user->isFriendOf()->attach($id, ['status' => 'accepted']);
        broadcast(new FriendListUpdated($id, 'acceptFriend', $user))->toOthers();
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
            $event = 'acceptFriend';
            broadcast(new FriendListUpdated($id, $event, $user))->toOthers();
            return response()->json(['friend' => $friend]);
        } else {
            abort(404);
        }
    }

    public function removeFriend($id)
    {
        $event = 'removeFriend';
        User::findOrFail($id)->friends()->detach(Auth::id());
        User::findOrFail(Auth::id())->friends()->detach($id);
        broadcast(new FriendListUpdated($id, $event))->toOthers();
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
                    ->where('messages.id', '<', $request->get('headId'))
                    ->orderBy('messages.id', 'DESC')
                    ->limit(10)
                    ->get();;
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
                    ->where('group_messages.receiver_id', $request->get('partner_id'))
                    ->where('group_messages.id', '<', $request->get('headId'))
                    ->orderBy('group_messages.id', 'DESC')
                    ->limit(10)
                    ->get();
                break;
            default:
                abort(404);
        }
        $message_ids = $messages->pluck('id')->all();
        $messageType = $request->get('partner_type') === 'group' ? 'App\Models\GroupMessage' : 'App\Models\Message';
        $attachments = DB::table('attachments')
            ->join('file_types', 'file_types.id', '=', 'attachments.file_type_id')
            ->whereIn('attachments.attachmentable_id', $message_ids)->where('attachments.attachmentable_type', $messageType)
            ->select('attachments.*', 'file_types.image_url as thumbnail')
            ->get();
        $attachment_map = [];
        foreach ($attachments as $attachment) {
            $attachment_map[$attachment->attachmentable_id] = $attachment;
        }
        $reaction_table = $request->get('partner_type') === 'group' ? 'group_message_reaction_user' : 'message_reaction_user';
        $type = $request->get('partner_type');
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
        return response()->json(['messages' => $messages, 'id' => $request->get('headId')]);
    }

    public function readMessage(Request $request)
    {
        $read = json_decode(stripslashes($request->get('read')));
        $partner_keys = [];
        foreach ($read as $item) {
            $keyArr = explode('-', $item);
            $type = $keyArr[0];
            $id = $keyArr[1];
            switch ($type) {
                case 'user':
                    $message = Message::findOrFail($id);
                    $message->seen_at = Carbon::now()->format('Y-m-d H:i:s');
                    $message->save();
                    $partner_key = $type . '-' . $message->sender->id;
                    break;
                case 'group':
                    $message = GroupMessage::findOrFail($id);
                    $message->unseen_users()->detach(Auth::id());
                    $partner_key = $type . '-' . $message->receiver->id;
                    break;
                default:
                    abort(404);
            }
            $partner_keys[$partner_key] = isset($partner_keys[$partner_key]) ? $partner_keys[$partner_key] + 1 : 1;
        }
        return response()->json(['partner_keys' => $partner_keys]);
    }

    public function userOnline($id)
    {
        $user = User::findOrFail($id);
        $user->status = 'online';
        $user->save();

        broadcast(new UserOnline($user));
        return response()->json(['message' => 'success']);
    }

    public function userOffline($id)
    {
        $user = User::findOrFail($id);
        $user->status = 'offline';
        $user->save();

        broadcast(new UserOffline($user));
        return response()->json(['message' => 'success']);
    }

    public function searchMessage($type, $id, $from, $to)
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
                    ->where(function ($query) use ($id, $from, $to) {
                        $query
                            ->where(function ($query) use ($id, $from, $to) {
                                $query->where('messages.sender_id', Auth::id())->where('messages.receiver_id', $id);
                            })
                            ->orWhere(function ($query) use ($id, $from, $to) {
                                $query->where('messages.sender_id', $id)->where('messages.receiver_id', Auth::id());
                            });
                    })
                    ->where('messages.id', '>', $from);
                if ($to != 0) {
                    $messages->where('messages.id', '<', $to);
                }
                $messages = $messages
                    ->orderBy('messages.id', 'DESC')
                    ->get();
                break;
            case 'group':
                $messages = DB::table('group_messages')
                    ->join('users as senders', 'group_messages.sender_id', '=', 'senders.id')
                    ->join('groups as receivers', 'group_messages.receiver_id', '=', 'receivers.id')
                    ->leftJoin('group_message_user_unseen', 'group_messages.id', '=', 'group_message_user_unseen.group_message_id')
                    ->select('group_messages.*',
                        'group_message_user_unseen.created_at as unseen',
                        'senders.name as sender_name',
                        'senders.image_url as sender_image_url',
                        'receivers.name as receiver_name',
                        'receivers.image_url as receiver_image_url',
                        DB::raw("'group' as receiver_type")
                    )
                    ->where('group_messages.receiver_id', $id)
                    ->where('group_messages.id', '>', $from);
                if ($to != 0) {
                    $messages->where('group_messages.id', '<', $to);
                }
                $messages = $messages
                    ->orderBy('group_messages.id', 'DESC')
                    ->get();
                break;
            default:
                abort(404);
        }
        return response()->json(['messages' => $messages]);
    }

    public function react(Request $request)
    {
        switch ($request->type) {
            case 'user':
                $message = Message::findOrFail($request->message_id);
                DB::table('message_reaction_user')
                    ->insert([
                        'message_id' => $request->message_id,
                        'reaction_id' => $request->reaction_id,
                        'user_id' => Auth::id()
                    ]);
                break;
            case 'group':
                $message = GroupMessage::findOrFail($request->message_id);
                DB::table('group_message_reaction_user')
                    ->insert([
                        'group_message_id' => $request->message_id,
                        'reaction_id' => $request->reaction_id,
                        'user_id' => Auth::id()
                    ]);
                break;
            default:
                abort(404);
                break;
        }
        broadcast(new MessageReact($message, $request->type));
        return response()->json(['data' => $request->all()]);
    }
}
