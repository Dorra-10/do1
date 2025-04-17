<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class History extends Model
{
    protected $fillable = [
        'document_id',
        'last_viewed_at',
        'last_viewed_by',
        'last_modified_at',
        'last_modified_by'
    ];

    // Relation avec le document
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    // Relation avec l'utilisateur qui a modifié
    public function modifier()
    {
        return $this->belongsTo(User::class, 'last_modified_by');
    }

    // Relation avec l'utilisateur qui a consulté
    public function viewer()
    {
        return $this->belongsTo(User::class, 'last_viewed_by');
    }

    // Méthode pour enregistrer les actions
    
public static function recordAction($documentId, $action, $userId)
{
    $document = \App\Models\Document::find($documentId);

    if (!$document) {
        return;
    }

    $history = self::firstOrNew(['document_id' => $documentId]);

    $history->document_name = $document->name;

    if ($action === 'view') {
        $history->last_viewed_by = $userId;
        $history->last_viewed_at = now();
    }

    if ($action === 'modify') {
        $history->last_modified_by = $userId;
        $history->last_modified_at = now();
    }

    $history->save();
}
}