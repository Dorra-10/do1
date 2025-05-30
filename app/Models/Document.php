<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Document extends Model
{ 
    use SoftDeletes;
    use HasFactory;
    
    protected $fillable = ['name', 'file_type', 'project_id', 'path', 'date_added','owner','company','description','is_exported','file_hash'];
    protected $casts = [
        'date_added' => 'datetime',
    ];
    
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function accesses()
    {
        return $this->hasMany(Access::class);
    }
    public function history()
    {
    return $this->hasMany(History::class);
    }

}