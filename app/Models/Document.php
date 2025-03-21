<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    // Ajoutez les champs que vous souhaitez pouvoir affecter par la méthode de création ou d'assignation de masse
    protected $fillable = ['name','type','project_id','access','date_added',];
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

}
