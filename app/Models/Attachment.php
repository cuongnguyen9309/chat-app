<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends Model
{
    use HasFactory;

    protected $table = 'attachments';
    protected $guarded = [];

    public function attachmentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function fileType(): BelongsTo
    {
        return $this->belongsTo(FileType::class, 'file_type_id', 'id');
    }

}
