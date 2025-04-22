<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\Export;
use Illuminate\Support\Facades\Storage;
class ExportController extends Controller
{
    public function index()
    {
        $exports = Export::all();  // Récupère tous les enregistrements de la table 'exports'
        return view('impoexpo.expo.index', compact('exports'));
    }

    public function download($id)
    {
     // Récupération du document
     $export = Export::findOrFail($id);  // Trouver le document ou renvoyer une erreur 404
     ;
     // Nettoyage du chemin du fichier (assurer que les slashes sont corrects)
     $filePath = str_replace('\\', '/', ltrim($export->path, '/'));
 
     // Vérification si le fichier existe dans le stockage
     if (!Storage::disk('public')->exists($filePath)) {
         // Log de l'erreur si le fichier n'existe pas
         logger()->error('Fichier introuvable', [
             'requested_file' => $filePath,
             'storage_root' => storage_path('app/public'),
             'file_exists' => file_exists(storage_path('app/public/'.$filePath)),
             'export_data' => $export->toArray()
         ]);
 
         // Renvoyer une erreur 404 avec un message clair
         abort(404, "Le fichier demandé est introuvable. Voir les logs pour plus de détails.");
     }
 
     // Récupérer l'extension du fichier stocké
     $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    
     // Télécharger le fichier avec son nom d'origine et son extension
     return Storage::disk('public')->download($filePath, $export->name . '.' . $extension);
    }
    
}
