<?php

namespace App\Http\Controllers;

use App\Events\ReceivedGroupRequest;
use App\Models\Group;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class GroupController extends Controller
{
    public function getGroup($id)
    {
        $group = Group::with('admin')->findOrFail($id);
        return response()->json(['group' => $group]);
    }

    public function store(Request $request)
    {

        $group = DB::transaction(function () use ($request) {
            $request->validate([
                'name' => 'required|unique:groups',
                'group-avatars' => 'mimes:jpeg,png,jpg,gif|max:10240'
            ]);
            $name = $request->get('name');
            $groups_members = $request->get('selectFriends');
            $image_url = $request->file('group-avatar')
                ->storeAs('images/avatars',
                    uniqid() . '_' . $request->file('group-avatar')->getClientOriginalName(),
                    'asset_public');
            $admin_id = Auth::id();
            $created_by = Auth::id();
            $group = Group::create(compact('name', 'admin_id',
                'created_by', 'image_url'));
            $group->users()->attach(Auth::id(), ['status' => 'accepted']);
            $group->pending_users()->attach($groups_members);
            foreach ($groups_members as $groups_member_id) {
                broadcast(new ReceivedGroupRequest($groups_member_id))->toOthers();
            }
            return $group;
        });
        return response()->json(['data' => $group]);
    }

    public function acceptGroup($id)
    {
        $group = Group::findOrFail($id);
        DB::table('group_user')
            ->where('group_id', $id)
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->update(['status' => 'accepted', 'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),]);
        return response()->json(['group' => $group]);
    }

    public function leaveGroup($id)
    {
        $group = Group::findOrFail($id);
        $group->users()->detach(Auth::id());
        if ($group->users->count() == 0) {
            $group->pending_users()->detach();
            $group->delete();
            return response()->json(['message' => 'success']);
        } else {
            if ($group->admin_id == Auth::id()) {
                $group->admin_id = $group->users->first()->id;
            }
            return response()->json(['message' => 'success']);
        }
    }
}
