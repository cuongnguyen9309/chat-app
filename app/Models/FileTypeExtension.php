<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FileTypeExtension extends Model
{
    use HasFactory;

    protected $table = 'file_type_extension';

    protected $guarded = [];

    public function fileType(): BelongsTo
    {
        return $this->belongsTo(FileType::class, 'file_type_id', 'id');
    }
}
