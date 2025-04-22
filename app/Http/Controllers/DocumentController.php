<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
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
use App\Models\Export;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $searchTerm = $request->input('search', '');
        $documents = Document::all();
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,catpart,catproduct,cgr|max:20480',
            'project_id' => 'required|exists:projects,id',
            'description' => 'nullable|string',
            'is_locked' => 'nullable|boolean'
        ]);
    
        DB::beginTransaction();
    
        try {
            $file = $request->file('file');
            $path = $file->store('documents', 'public');
    
            $document = Document::create([
                'name' => $request->name,
                'path' => $path,
                'file_type' => $file->extension(),
                'project_id' => $request->project_id,
                'owner' => auth()->id(),
                'company' => $request->company,
                'description' => $request->description,
                'is_locked' => $request->is_locked ?? false,
                'date_added' => now()
            ]);
    
            DB::commit();
    
            return redirect()->route('documents.index')
                           ->with('success', 'Document créé avec succès');
    
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la création: ' . $e->getMessage());
        }
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
        $document = Document::find($id);
        if (!$document) {
            return redirect()->back()->with('error', "Document introuvable.");
        }

        $user = auth()->user();

        $hasWriteAccess = $document->accesses()
            ->where('user_id', $user->id)
            ->where('permission', 'write')
            ->exists();

        if (! $user->hasRole(['admin', 'superviseur']) && !$hasWriteAccess) {
            return redirect()->back()->with('error', "Vous n'avez pas les permissions pour modifier ce document.");
        }

        // ✅ D'abord récupérer le fichier
        $request->validate([
            'file' => 'required|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,catpart,catproduct,cgr|max:20480',
        ]);

        $file = $request->file('file');

        $oldName = strtolower(trim($document->name));
        $newName = strtolower(trim($file->getClientOriginalName()));

        // ✅ Comparer les noms complets avec extension
        if ($oldName !== $newName) {
            return redirect()->back()->with('error', "Le nom du fichier doit être exactement le même que l'ancien : '{$document->name}'.");
        }

        $extension = $file->getClientOriginalExtension();

        if ($extension !== $document->file_type) {
            return redirect()->back()->with('error', "Le type de fichier doit être le même que l'ancien : '{$document->file_type}'.");
        }

        $oldPath = $document->path;

        // 🔥 Supprimer l'ancien fichier s'il existe
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        // 📥 Stocker le nouveau fichier
        $newStoragePath = 'documents/' . time() . '.' . $extension;
        Storage::disk('public')->put($newStoragePath, file_get_contents($file->getRealPath()));

        if (!Storage::disk('public')->exists($newStoragePath)) {
            throw new \Exception("Le fichier n'a pas pu être enregistré.");
        }

        // 📝 Mettre à jour le document
        $document->update([
            'name' => $file->getClientOriginalName(),
            'file_type' => $extension,
            'path' => $newStoragePath,
            'updated_at' => now(),
        ]);

        History::recordAction($document->id, 'modify', auth()->id());

        // 🕒 Historique
        $history = History::firstOrNew(['document_id' => $document->id]);
        $history->document_name = $document->name;
        $history->last_modified_at = now();
        $history->last_modified_by = $user->id;
        $history->save();

        DB::commit();

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



public function lock(Request $request, $id)
{
    $document = Document::findOrFail($id);

    $document->is_locked = true;
    $document->save();

    Export::create([
        'name' => $document->name,
        'file_type' => $document->file_type,
        'project_id' => $document->project_id,
        'path' => $document->path,
        'date_added' => now(),
        'owner' => $document->owner,
        'company' => $document->company,
        'description' => $document->description,
    ]);

    // Réponse JSON pour les appels JS
    if ($request->expectsJson()) {
        return response()->json(['success' => true]);
    }

    // Fallback classique
    return redirect()->back()->with('success', 'Document verrouillé et exporté avec succès.');
}






    
}





