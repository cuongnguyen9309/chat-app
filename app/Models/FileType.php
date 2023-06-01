<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FileType extends Model
{
    use HasFactory;

    protected $table = 'file_types';

    protected $guarded = [];

    public function fileTypeExtensions(): HasMany
    {
        return $this->hasMany(FileTypeExtension::class, 'file_type_id', 'id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'file_type_id', 'id');
    }
}
