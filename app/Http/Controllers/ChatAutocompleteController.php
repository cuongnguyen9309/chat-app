<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatAutocompleteController extends Controller
{
    public function autocomplete(Request $request)
    {
        $words = DB::table('chat_autocompletes')
            ->select('id', 'full')
            ->where('full', 'like', '%' . $request->get('query') . '%')
            ->union(DB::table('abbreviations')
                ->where('short', 'like', '%' . $request->get('query') . '%')
                ->select('id', 'full')
            )
            ->get()->pluck('full');
        return response()->json($words);
    }
}
