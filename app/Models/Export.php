<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Export extends Model
{
    use SoftDeletes;
    use HasFactory;
    // Si la table ne suit pas la convention de nommage (par exemple 'exports' au lieu de 'export')
    protected $table = 'exports';

    // Définir les attributs que vous pouvez remplir en masse
    protected $fillable = [
        'name', 'type_id', 'file_type', 'project_id', 'path', 'owner', 'company', 'description'
    ];
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
