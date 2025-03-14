<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['name', 'type', 'date_added'];

    public function documents()
    {
        return $this->hasMany(Document::class); // Assurez-vous que Document existe
    }
}