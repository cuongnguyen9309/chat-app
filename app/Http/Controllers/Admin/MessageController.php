<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroupMessage;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $messages = Message::paginate(10);
        return view('admin.pages.message.index', compact('messages'));
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function removeMessage($type, $id)
    {
        switch ($type) {
            case('personal'):
                Message::whereId($id)->delete();
                break;
            case('group'):
                GroupMessage::whereId($id)->delete();
                break;
            default:
                abort(404);
        }
        return response()->json(['message' => 'deleted']);
    }

    public function restoreMessage($type, $id)
    {
        switch ($type) {
            case('personal'):
                Message::whereId($id)->restore();
                break;
            case('group'):
                GroupMessage::whereId($id)->restore();
                break;
            default:
                abort(404);
        }
        return response()->json(['message' => 'restore']);
    }
}
