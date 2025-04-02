<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'type', 'date_added'];

    public function documents()
    {
        return $this->hasMany(Document::class); 
    }
    public function accesses()
    {
        return $this->hasMany(Access::class);
    }
}