<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'file_type', 'project_id', 'path', 'date_added', 'access'];
    
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function accesses()
    {
        return $this->hasMany(Access::class);
    }
}

