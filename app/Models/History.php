<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class History extends Model
{    
    use HasFactory;
    protected $table = 'histories';
    protected $fillable = [
        'document_id',
        'document_name',
        'last_viewed_at',
        'last_modified_at',
        'last_modified_by',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function lastModifiedUser()
    {
        return $this->belongsTo(User::class, 'last_modified_by');
    }
}
