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
         abort(404, "The requested file could not be found.");
     }
 
     // Récupérer l'extension du fichier stocké
     $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    
     // Télécharger le fichier avec son nom d'origine et son extension
     return Storage::disk('public')->download($filePath, $export->name);
    }
    

    public function destroy(Export $export)
{
    try {
        // Supprimer le fichier du stockage
        if (Storage::disk('public')->exists($export->path)) {
            Storage::disk('public')->delete($export->path);
        } else {
            return redirect()->route('impoexpo.expo.index')->with('error', 'Le fichier n\'existe pas sur le disque');
        }

        // Suppression définitive
        $export->forceDelete();

        return redirect()->route('impoexpo.expo.index')->with('success', 'Export deleted successfully !');

    } catch (\Exception $e) {
        return redirect()->route('impoexpo.expo.index')->with('error', 'Error while deleting : ' . $e->getMessage());
    }
}



}
