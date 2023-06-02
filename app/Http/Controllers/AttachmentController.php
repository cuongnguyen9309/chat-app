<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function download($name)
    {
        return Storage::disk('asset_public')->download('/attachments/' . $name);
    }
}
