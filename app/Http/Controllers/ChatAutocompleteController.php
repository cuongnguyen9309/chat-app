<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatAutocompleteController extends Controller
{
    public function autocomplete(Request $request)
    {
        $words = DB::table('chat_autocompletes')->where('content', 'like', '%' . $request->get('query') . '%')->get()->pluck('content');
        return response()->json($words);
    }
}
