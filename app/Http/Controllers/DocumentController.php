<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Models\Document;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    // Afficher tous les documents
    public function index()
    {
        $documents = Document::all();
        $projects = Project::all(); // Récupère tous les projets
        
        return view('documents.index', compact('documents', 'projects')); // Passe à la fois les documents et les projets
    }
    
    // Upload d'un document
    public function upload(Request $request)
    {
        // Validation
        $request->validate([
            'document' => 'required|file|mimes:pdf,docx,pptx,xlsx|max:20480',  // 20MB max
            'project_id' => 'required|exists:projects,id', // Vérifie si le projet existe
            'name' => 'required|string|max:255',
        ]);
    
        // Vérification et stockage du fichier
        if ($request->hasFile('document')) {
            $file = $request->file('document');
    
            // Utilisation du nom donné dans le formulaire, en ajoutant l'extension du fichier
            $fileName = $request->name . '.' . $file->getClientOriginalExtension();
            
            // Stockage du fichier avec le nom spécifié dans le formulaire
            $path = $file->storeAs('documents', $fileName, 'public');
    
            // Enregistrement dans la base de données
            Document::create([
                'name' => $request->name,  // Le nom donné par l'utilisateur
                'file_type' => $file->getClientOriginalExtension(),  // Extension du fichier
                'project_id' => $request->project_id,  // ID du projet
                'path' => $path,  // Chemin du fichier dans le stockage
                'access' => $request->access,  // Accès au document
                'date_added' => now(),  // Date actuelle d'ajout
            ]);
    
            return redirect()->route('documents.index')->with('success', 'Document ajouté avec succès');
        }
    
        return redirect()->back()->with('error', 'Erreur lors de l\'ajout du document');
    }
    
    // 📥 Téléchargement d'un fichier
    public function download($id)
    {
        $document = Document::findOrFail($id);
    
        // Récupération du chemin complet du fichier
        $filePath = Storage::disk('public')->path($document->path);
    
        // Vérification si le fichier existe
        if (!Storage::disk('public')->exists($document->path)) {
            return redirect()->back()->with('error', 'Le fichier n\'existe pas.');
        }
    
        // Téléchargement du fichier avec son nom et son extension
        return response()->download($filePath, $document->name . '.' . $document->file_type);
    }
    
    public function update(Request $request, Document $document)
    {
        // Validation des données du formulaire
        $request->validate([
            'name' => 'required|string|max:255',
            'access' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,docx,pptx,xlsx|max:10240', // Validation du fichier
        ]);
    
        // Initialiser la variable $filePath et $file_type pour plus de clarté
        $filePath = $document->path;
        $file_type = $document->file_type;
    
        // Si un fichier est téléchargé, il faut le traiter
        if ($request->hasFile('file')) {
            // Supprimer l'ancien fichier s'il existe
            if (Storage::disk('public')->exists($document->path)) {
                Storage::disk('public')->delete($document->path);
            }
    
            // Déplacer le fichier vers le dossier de stockage
            $file = $request->file('file');
            $filePath = $file->storeAs('documents', time() . '_' . $file->getClientOriginalName(), 'public');
            $file_type = $file->getClientOriginalExtension(); // Mise à jour du type de fichier (extension)
        }
    
        // Mettre à jour les champs du document avec les nouvelles données
        $document->update([
            'name' => $request->input('name'),
            'access' => $request->input('access'),
            'path' => $filePath, // Mise à jour du chemin du fichier
            'file_type' => $file_type, // Mise à jour du type de fichier (extension)
        ]);
    
        // Retourner une réponse ou rediriger avec un message de succès
        return redirect()->route('documents.index')->with('success', 'Document mis à jour avec succès');
    }
    
    
    

    //  Révision (remplacement d'un fichier)
    public function revision(Request $request, $id)
    {
        DB::beginTransaction();
    
        try {
            $document = Document::findOrFail($id);
            
            // Sauvegarder le chemin de l'ancien fichier
            $oldPath = $document->path;
    
            // Valider le nouveau fichier
            $request->validate([
                'file' => 'required|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,catpart,catproduct,cgr|max:20480',
            ]);
    
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
    
            // Conserver le même nom de fichier
            $newStoragePath = 'documents/' . pathinfo($oldPath, PATHINFO_FILENAME) . '.' . $extension;
    
            // Supprimer l'ancien fichier s'il existe
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
    
            // Enregistrer le NOUVEAU fichier avec le même nom
            Storage::disk('public')->put($newStoragePath, file_get_contents($file->getRealPath()));
    
            // Mettre à jour la base de données
            $document->update([
                'path' => $newStoragePath,
                'name' => $file->getClientOriginalName(),
                'file_type' => $extension,
                'updated_at' => now(),
            ]);
    
            DB::commit();
    
            // Nettoyer le cache du fichier
            clearstatcache();
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
    
            return redirect()->back()->with('success', 'Fichier mis à jour avec succès.');
    
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Échec de la mise à jour: ' . $e->getMessage());
        }
    }
    
    
    
    
    
    
    // 🗑️ Suppression d'un document
    public function destroy($id)
{
    $document = Document::findOrFail($id);

    // Vérifier et supprimer le fichier
    $filePath = storage_path('app/public/' . $document->path);

    if (file_exists($filePath)) {
        unlink($filePath);
    } elseif (Storage::disk('public')->exists($document->path)) {
        Storage::disk('public')->delete($document->path);
    }

    // Supprimer l'entrée de la base de données
    $document->delete();

    return redirect()->route('documents.index')->with('status', 'Document supprimé avec succès !');
}



    
}
