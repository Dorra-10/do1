<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Document;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use App\Models\Access;
use App\Models\History;


class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $searchTerm = $request->input('search', '');
    
        // Documents query
        $documentsQuery = Document::query();
        $filetypes = [
            1 => 'pdf',
            2 => 'docx',
            3 => 'pptx',
            4 => 'xls',
            5 => 'catia',
        ];
        
        if ($user->hasRole(['admin', 'superviseur'])) {
            // Tous les documents pour admin/superviseur
            $projects = Project::all();
        } else {
            // Restrictions pour les autres utilisateurs
            $documentsQuery->whereHas('accesses', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->whereIn('permission', ['read', 'write']);
            });
    
            $projects = Project::whereHas('documents.accesses', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->whereIn('permission', ['read', 'write']);
            })->get();
        }
    
        // Ajout de la recherche si terme existant
        if (!empty($searchTerm)) {
            $documentsQuery->where(function($query) use ($searchTerm) {
                $query->where('name', 'LIKE', '%'.$searchTerm.'%')
                      ->orWhere('file_type', 'LIKE', '%'.$searchTerm.'%')
                      ->orWhereHas('project', function($q) use ($searchTerm) {
                          $q->where('name', 'LIKE', '%'.$searchTerm.'%');
                      });
            });
        }
    
        $documents = $documentsQuery->with('project')
                                   ->orderBy('date_added', 'desc')
                                   ->paginate(10);
    
        return view('documents.index', compact('documents', 'projects', 'searchTerm','filetypes'));
    }

   
    public function upload(Request $request)
{
    // Validation des données
    $request->validate([
        'document' => 'required|file|mimes:pdf,docx,pptx,xlsx,xls|max:20480',
        'project_id' => 'required|exists:projects,id',
        'name' => 'required|string|max:255',
        'access' => 'nullable|string|max:255',
        'owner' => 'nullable|string|max:255',
        'company' => 'nullable|string|max:255',
        'description' => 'nullable|string',
    ]);

    // Vérification si un fichier est présent
    if ($request->hasFile('document')) {
        $file = $request->file('document');
        $fileExtension = $file->getClientOriginalExtension();  // Extension du fichier
        $fileName = $request->name . '.' . $fileExtension;

        // Stockage du fichier dans le dossier public
        $path = $file->storeAs('documents', $fileName, 'public');

        // Définir un type de fichier basé sur l'extension
        $fileType = match ($fileExtension) {
            'pdf' => 1,
            'docx' => 2,
            'pptx' => 3,
            'xlsx', 'xls' => 4,
            default => null,
        };

        if (!$fileType) {
            return redirect()->back()->with('error', 'Type de fichier non valide');
        }

        // Création du document avec les informations validées
        $document = Document::create([
            'name' => $request->name,
            'type_id' => $fileType,
            'file_type' => $fileExtension,
            'project_id' => $request->project_id,
            'path' => $path,
            'owner' => $request->owner,
            'company' => $request->company,
            'description' => $request->description,
        ]);

        // Enregistrement dans l'historique
        History::recordAction($document->id, 'modify', auth()->id());

        return redirect()->route('documents.index')->with('success', 'Document ajouté avec succès');
    }

    return redirect()->back()->with('error', 'Erreur lors de l\'ajout du document');
}

    

    public function download($id)
   {
    // Récupération du document
    $document = Document::findOrFail($id);  // Trouver le document ou renvoyer une erreur 404
    History::recordAction($document->id, 'view', auth()->id());
    // Nettoyage du chemin du fichier (assurer que les slashes sont corrects)
    $filePath = str_replace('\\', '/', ltrim($document->path, '/'));

    // Vérification si le fichier existe dans le stockage
    if (!Storage::disk('public')->exists($filePath)) {
        // Log de l'erreur si le fichier n'existe pas
        logger()->error('Fichier introuvable', [
            'requested_file' => $filePath,
            'storage_root' => storage_path('app/public'),
            'file_exists' => file_exists(storage_path('app/public/'.$filePath)),
            'document_data' => $document->toArray()
        ]);

        // Renvoyer une erreur 404 avec un message clair
        abort(404, "Le fichier demandé est introuvable. Voir les logs pour plus de détails.");
    }

    // Récupérer l'extension du fichier stocké
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
     
    // 🔄 Mise à jour de l'historique de consultation
    $history = History::firstOrNew(['document_id' => $document->id]);
    $history->document_name = $document->name;
    $history->last_viewed_by = Auth::id(); // ✅ récupère l'utilisateur connecté
    $history->last_viewed_at = now();
    $history->save();
    
    // Télécharger le fichier avec son nom d'origine et son extension
    return Storage::disk('public')->download($filePath, $document->name . '.' . $extension);
   }
   
   public function edit($id)
   {
       // Récupère le document à éditer
       $editdocument = Document::findOrFail($id);
   
       // Récupère tous les projets pour les afficher dans la liste déroulante
       $projects = Project::all();
   
       // Liste des types de fichiers valides
       $filetypes = [1 => 'pdf', 2 => 'docx', 3 => 'pptx', 4 => 'xls', 5 => 'catia'];
   
       // Passe les variables à la vue
       return view('documents.index', [
        'editDocument' => $document, // ← changement ici
        'projects' => $projects,
        'filetypes' => $filetypes,
    ]);
    
   }
   
   public function update(Request $request, Document $document)
{
    // 1. Valider tous les champs du formulaire
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'project_id' => 'required|exists:projects,id',
        'owner' => 'required|string|max:255',
        'company' => 'required|string|max:255',
        'description' => 'nullable|string',
        'date_added' => 'required|date',
        'file' => 'nullable|file|mimes:pdf,docx,pptx,xlsx|max:10240', // seulement si vous avez un champ file
    ]);

    // 2. Gestion du fichier (uniquement si vous avez ce champ)
    $fileData = [];
    if ($request->hasFile('file')) {
        // Supprimer l'ancien fichier
        if (Storage::disk('public')->exists($document->path)) {
            Storage::disk('public')->delete($document->path);
        }

        // Stocker le nouveau fichier
        $file = $request->file('file');
        $filePath = $file->storeAs('documents', time() . '_' . $file->getClientOriginalName(), 'public');
        
        $fileData = [
            'path' => $filePath,
            'file_type' => $file->getClientOriginalExtension(),
        ];
    }

    // 3. Mettre à jour tous les champs
    $document->update(array_merge([
        'name' => $validated['name'],
        'project_id' => $validated['project_id'],
        'owner' => $validated['owner'],
        'company' => $validated['company'],
        'description' => $validated['description'],
        'date_added' => $validated['date_added'],
    ], $fileData));

    return redirect()->route('documents.index')->with('success', 'Document updated successfully!');
}
    public function revision(Request $request, $id)
{
    DB::beginTransaction();

    try {
        // Récupérer le document par son ID
        $document = Document::find($id);
        if (!$document) {
            return redirect()->back()->with('error', "Document introuvable.");
        }

        // Récupérer l'utilisateur actuellement connecté
        $user = auth()->user();

        // Vérification des permissions d'accès au document
        $hasWriteAccess = $document->accesses()
            ->where('user_id', $user->id)
            ->where('permission', 'write')
            ->exists();

        // Vérifier si l'utilisateur est admin, superviseur ou s'il a les permissions en écriture
        if (! $user->hasRole(['admin', 'superviseur']) && !$hasWriteAccess) {
            return redirect()->back()->with('error', "Vous n'avez pas les permissions pour modifier ce document.");
        }

        // Récupérer le chemin, le nom et l'extension du document original
        $oldPath = $document->path;
        $oldName = pathinfo($oldPath, PATHINFO_FILENAME);
        $oldExtension = pathinfo($oldPath, PATHINFO_EXTENSION);

        // Validation du fichier envoyé dans la requête
        $request->validate([
            'file' => 'required|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,catpart,catproduct,cgr|max:20480',
        ]);

        // Récupérer le fichier et ses informations
        $file = $request->file('file');
        $newName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $newExtension = $file->getClientOriginalExtension();

        // Vérifier si le nom et l'extension correspondent au fichier original
        if ($newName !== pathinfo($document->name, PATHINFO_FILENAME) || $newExtension !== $document->file_type) {
            return redirect()->back()->with('error', "Le fichier doit avoir le même nom et le même type que le fichier original.");
        }

        // Supprimer l'ancien fichier du stockage, s'il existe
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        // Générer un nouveau chemin pour le fichier et le sauvegarder
        $newStoragePath = 'documents/' . time() . '.' . $newExtension;
        Storage::disk('public')->put($newStoragePath, file_get_contents($file->getRealPath()));

        // Vérifier si le fichier a bien été sauvegardé
        if (!Storage::disk('public')->exists($newStoragePath)) {
            throw new \Exception("Le fichier n'a pas pu être enregistré.");
        }

        // Mettre à jour le document dans la base de données
        $document->update([
            'name' => $file->getClientOriginalName(),
            'file_type' => $newExtension,
            'path' => $newStoragePath,
            'updated_at' => now(),
        ]);

        // Enregistrer l'action de modification dans l'historique
        History::recordAction($document->id, 'modify', auth()->id());

        // Mettre à jour l'historique de modification
        $history = History::firstOrNew(['document_id' => $document->id]);
        $history->document_name = $document->name;
        $history->last_modified_at = now();
        $history->last_modified_by = $user->id;
        $history->save();

        // Commit des transactions
        DB::commit();

        // Effacer le cache pour que les changements soient pris en compte
        clearstatcache();
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        // Retourner un message de succès
        return redirect()->back()->with('success', 'Fichier mis à jour avec succès.');

    } catch (\Exception $e) {
        // Rollback en cas d'erreur et log de l'exception
        DB::rollBack();
        Log::error('Erreur lors de la mise à jour du fichier : ' . $e->getMessage());
        return redirect()->back()->with('error', "Le fichier doit avoir le même nom et le même type que le fichier original.");

    }
}

public function destroy($id)
{
    // Trouver le document dans la base de données
    $document = Document::findOrFail($id);

    try {
        // Supprimer le fichier du stockage (si il existe)
        if (Storage::disk('public')->exists($document->path)) {
            Storage::disk('public')->delete($document->path);
        } else {
            return redirect()->route('documents.index')->with('error', 'Le fichier n\'existe pas sur le disque');
        }

        // Utiliser forceDelete pour une suppression définitive (si SoftDeletes est activé)
        $document->forceDelete();

        // Retourner un message de succès
        return redirect()->route('documents.index')->with('status', 'Documents deleted successfully !');
        
    } catch (\Exception $e) {
        // Gestion des erreurs
        return redirect()->route('documents.index')->with('error', 'Erreur lors de la suppression du document: ' . $e->getMessage());
    }
}



    
}





