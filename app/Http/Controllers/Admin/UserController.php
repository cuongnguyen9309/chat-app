<?php

namespace App\Http\Controllers\Admin;

use App\Events\ReceivedGroupRequest;
use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::paginate(10);
        return view('admin.pages.user.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.pages.user.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $image_url = '';
        if ($request->file('image')) {
            $image_url = $request->file('image')
                ->storeAs('images/avatars',
                    uniqid() . '_' . $request->file('image')->getClientOriginalName(),
                    'asset_public');
        };
        $request->validate([
            'name' => 'required',
            'email' => 'email|required|unique:users',
            'password' => 'required|confirmed|min:6|max:32'
        ], [
            'email.required' => 'Email can not be empty',
            'email.unique' => 'This email is already in used',
            'password.required' => 'Password can not be empty',
            'password.confirmed' => 'Password confirmation does not match',
            'password.min' => 'Password length must be greater than 6',
            'password.max' => 'Password length must be less than 32',
        ]);
        $request = $request->except(['password_confirmation', '_token', 'image']);

        $request['password'] = Hash::make($request['password']);
        if (isset($request['is_accept_stranger_request']) && $request['is_accept_stranger_request']) {
            $request['is_accept_stranger_request'] = 1;
        } else {
            $request['is_accept_stranger_request'] = 0;
        }
        if (isset($request['is_admin']) && $request['is_admin']) {
            $request['is_admin'] = 1;
        } else {
            $request['is_admin'] = 0;
        }
        if ($image_url) {
            $request['image_url'] = $image_url;
        }
        $user = User::create($request);
        $short_url = createShortUrl(route('friend.add.no.confirm', $user->id));
        $user->add_friend_link = $short_url;
        $user->save();
        return redirect()->route('admin.user.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::with('friends', 'joined_groups')->findOrFail($id);
        $friends = $user->friends()->paginate(3, ['*'], 'friends');
        $groups = $user->joined_groups()->with('admin')->paginate(3, ['*'], 'groups');
        $personal_messages = DB::table('messages')->where('sender_id', $id)->orWhere('receiver_id', $id)->select('id', 'content', 'sender_id', 'receiver_id', 'created_at', 'updated_at', 'deleted_at', DB::raw("'personal' as type"));
        $all_messages = DB::table('group_messages')->where('sender_id', $id)->select('*', DB::raw("'group' as type"))->union($personal_messages);
        $messages = DB::table('users as senders')
            ->joinSub($all_messages, 'messages', function (JoinClause $join) {
                $join->on('senders.id', '=', 'messages.sender_id');
            })->join('users as receivers', 'receivers.id', '=', 'messages.receiver_id')
            ->select('messages.*', 'senders.name as sender_name', 'receivers.name as receiver_name')
            ->orderBy('messages.created_at', 'DESC')
            ->paginate(5, ['*'], 'messages');
        return view('admin.pages.user.detail', compact('user', 'friends', 'messages', 'groups'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $bool = DB::transaction(function () use ($request, $id) {
            $request->validate([
                'name' => ['required', Rule::unique('users')->ignore($id, 'id')],
                'email' => ['required', 'email', Rule::unique('users')->ignore($id, 'id')],
                'image' => 'mimes:jpeg,png,jpg,gif|max:10240'
            ]);
            $name = $request->get('name');
            $email = $request->get('email');
            if ($request->file('image')) {
                $image_url = $request->file('image')
                    ->storeAs('images/avatars',
                        uniqid() . '_' . $request->file('image')->getClientOriginalName(),
                        'asset_public');
                $update_values = compact('name', 'email', 'image_url');
            } else {
                $update_values = compact('name', 'email');
            }
            $user = User::where('id', $id)->update($update_values);
            return $user;
        });
        return response()->json(['data' => $bool]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function addFriend(Request $request)
    {
        $user = User::findOrFail($request->get('user_id'));
        DB::transaction(function () use ($request, $user) {
            $user->friends()->attach($request->addFriendList, ['status' => 'accepted']);
            $user->isFriendOf()->attach($request->addFriendList, ['status' => 'accepted']);
        });
        return response()->json(['data' => $request->all()]);
    }

    public function removeFriend($userId, $id)
    {
        $user = User::findOrFail($userId);
        $friend = User::findOrFail($id);
        $user->friends()->detach($id);
        $friend->friends()->detach($userId);
        $friends_id = $user->friends->pluck('id');
        return response()->json(['message' => 'success', 'friends_id' => $friends_id]);
    }

    public function joinGroup(Request $request)
    {
        $user = User::findOrFail($request->get('user_id'));
        DB::transaction(function () use ($request, $user) {
            $user->joined_groups()->attach($request->addGroupList, ['status' => 'accepted']);
        });
        return response()->json(['data' => $request->all()]);
    }

    public function leaveGroup($userId, $id)
    {
        $user = User::find($userId);
        $user->joined_groups()->detach($id);
        $groups_id = $user->joined_groups()->pluck('id');
        return response()->json(compact('groups_id'));
    }

}
