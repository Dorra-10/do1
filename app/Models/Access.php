<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Access extends Model
{
    use HasFactory;

    protected $table = 'accesses'; 

    protected $fillable = ['project_id', 'document_id', 'user_id', 'permission'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    
    public function document()
    {
        return $this->belongsTo(Document::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


