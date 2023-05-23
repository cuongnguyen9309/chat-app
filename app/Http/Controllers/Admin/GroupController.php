<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\User;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $groups = Group::with('admin')->paginate(10);
        return view('admin.pages.group.index', compact('groups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $group = Group::with('users')->findOrFail($id);
        $users = $group->users()->paginate(3, ['*'], 'users');
        $messages = DB::table('users as senders')
            ->join('group_messages as messages', 'senders.id', '=', 'messages.sender_id')
            ->select('messages.*', 'senders.name as sender_name')
            ->where('messages.receiver_id', $id)
            ->orderBy('messages.created_at', 'DESC')
            ->paginate(5, ['*'], 'messages');
        return view('admin.pages.group.detail', compact('group', 'users', 'messages'));
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
                'name' => ['required'],
                'image' => 'mimes:jpeg,png,jpg,gif|max:10240',
                'admin_id' => 'required'
            ]);
            $name = $request->get('name');
            $admin_id = $request->get('admin_id');
            if ($request->file('image')) {
                $image_url = $request->file('image')
                    ->storeAs('images/avatars',
                        uniqid() . '_' . $request->file('image')->getClientOriginalName(),
                        'asset_public');
                $update_values = compact('name', 'admin_id', 'image_url');
            } else {
                $update_values = compact('name', 'admin_id');
            }
            $bool = Group::where('id', $id)->update($update_values);
            return $bool;
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

    public function addUser(Request $request)
    {
        $group = Group::findOrFail($request->get('group_id'));
        DB::transaction(function () use ($request, $group) {
            $group->users()->attach($request->addUserList, ['status' => 'accepted']);
        });
        return response()->json(['data' => $request->all()]);
    }

    public function removeUser($groupId, $id)
    {
        $group = Group::findOrFail($groupId);
        $group->users()->detach($id);
        $users_id = $group->users->pluck('id');
        return response()->json(['message' => 'success', 'users_id' => $users_id]);
    }
    
}
