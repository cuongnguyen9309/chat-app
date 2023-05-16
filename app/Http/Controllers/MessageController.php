<?php

namespace App\Http\Controllers;

use App\Models\Message;

class MessageController extends Controller
{
    public function adminIndex()
    {
        $messages = Message::paginate(10);
        return view('admin.pages.message.index', compact('messages'));
    }
}
