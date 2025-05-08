<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Document;
class Import extends Model
{
   
    use HasFactory;
    protected $fillable = ['name', 'file_type', 'project_id', 'path', 'date_added','owner','company','description','file_hash'];
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
