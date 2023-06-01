<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AttachmentController extends Controller
{
    public function download($id)
    {
        return response()->json('success');
    }
}
