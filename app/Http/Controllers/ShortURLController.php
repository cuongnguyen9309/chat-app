<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShortURLController extends Controller
{
    public function navigate($url_key)
    {
        $short_url = DB::table('short_urls')
            ->where('url_key', $url_key)
            ->select('destination_url')
            ->first();
        return redirect($short_url->destination_url);
    }
}
