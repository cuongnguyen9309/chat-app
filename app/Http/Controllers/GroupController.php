<?php

namespace App\Http\Controllers;

use App\Models\Group;

class GroupController extends Controller
{
    public function adminIndex()
    {
        $groups = Group::paginate(10);
        return view('admin.pages.group.index', compact('groups'));
    }
}
