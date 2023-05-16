<?php

namespace App\Http\Controllers;

use App\Models\GroupMessage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GroupMessageController extends Controller
{
    public function adminIndex(): View
    {
        $groupMessages = GroupMessage::paginate(10);
        return view('admin.pages.groupMessage.index', compact('groupMessages'));
    }
}
