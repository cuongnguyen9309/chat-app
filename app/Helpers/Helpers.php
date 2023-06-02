<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

function truncateAndHighlight($text, $search, $len, $tail)
{
    $search_pos = strpos(strtolower($text), strtolower($search));
    $sub_str = substr($text, $search_pos, $len);
    return ($search_pos === 0 ? '' : $tail) . str_ireplace($search, '<em>' . $search . '</em>', $sub_str) . $tail;
}

function createShortUrl($destination_url)
{
    $url_key = Str::random(6);
    $default_short_url = route('short.url', $url_key);
    DB::table('short_urls')
        ->insert([
            'destination_url' => $destination_url,
            'url_key' => $url_key,
            'default_short_url' => $default_short_url
        ]);
    return $default_short_url;
}

?>
