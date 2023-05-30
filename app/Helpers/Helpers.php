<?php
function truncateAndHighlight($text, $search, $len, $tail)
{
    $search_pos = strpos(strtolower($text), strtolower($search));
    $sub_str = substr($text, $search_pos, $len);
    return ($search_pos === 0 ? '' : $tail) . str_ireplace($search, '<em>' . $search . '</em>', $sub_str) . $tail;
}

?>
