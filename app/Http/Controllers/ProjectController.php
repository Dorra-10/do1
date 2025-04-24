<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'date_added' => 'required|date',
        ]);

        Project::create($validatedData);

        return redirect()->route('projects.index')
                         ->with('success', 'Project created successfully !');
        
       
    }
    public function show($id)
    {
        $project = Project::findOrFail($id);
        return view('projects.show', compact('project'));
    }
    
    
        // Méthode pour afficher tous les projets
        public function index(Request $request)
        {
            $user = auth()->user();
            $searchTerm = $request->input('search', ''); // Récupère le terme de recherche ou une chaîne vide
        
            if ($user->hasRole(['admin', 'superviseur'])) {
                $query = Project::query();
            } else {
                $query = Project::whereHas('documents', function ($query) use ($user) {
                    $query->whereHas('accesses', function ($q) use ($user) {
                        $q->where('user_id', $user->id)
                          ->whereIn('permission', ['read', 'write']);
                    });
                });
            }
        
            // Ajoute la condition de recherche si un terme est fourni
            if (!empty($searchTerm)) {
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', '%'.$searchTerm.'%')
                      ->orWhere('type', 'LIKE', '%'.$searchTerm.'%');
                });
            }
        
            $projects = $query->orderBy('created_at', 'desc')->paginate(10);
        
            return view('projects.index', compact('projects', 'searchTerm'));
        }

    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'date_added' => 'required|date',
        ]);

        $project->update($request->only(['name', 'type', 'date_added']));

        if ($request->ajax()) {
            return response()->json(['message' => 'Projet mis à jour avec succès !', 'project' => $project]);
        }

        return redirect()->route('projects.index')->with('success', 'Project updated successfully !');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted successfully !');
    }

    public function search(Request $request)
{
    $searchTerm = trim($request->input('search'));

    $projects = Project::query();

    if (!empty($searchTerm)) {
        $projects->where('name', 'LIKE', '%'.$searchTerm.'%')
                 ->orWhere('description', 'LIKE', '%'.$searchTerm.'%');
    }

    $projects = $projects->latest()->get();

    if ($request->ajax()) {
        return response()->json([
            'table' => view('projects.partials.table', compact('projects'))->render()
        ]);
    }

    return view('projects.index', compact('projects'));
}

    public function showDocuments($projectId)
    {
    $user = auth()->user();
    $project = Project::with('documents')->findOrFail($projectId);
    $projects = Project::all(); // Liste complète pour affichage latéral ou dropdown

    // Si l'utilisateur est admin ou superviseur → voir tous les documents du projet
    if ($user->hasRole(['admin', 'superviseur'])) {
        $documents = $project->documents;
    } else {
        // Filtrer les documents du projet auxquels l'utilisateur a un accès explicite
        $documents = $project->documents()->whereHas('accesses', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->whereIn('permission', ['read', 'write']);
        })->get();
    }

    return view('projects.documents', [
        'project' => $project,
        'projects' => $projects,
        'documents' => $documents,
    ]);

    }

    public function downloadDocument($projectId, $documentId)
    {
        $document = Document::where('project_id', $projectId)->findOrFail($documentId);
        
        if (!Storage::disk('public')->exists($document->path)) {
            return redirect()->back()->with('error', 'Le fichier n\'existe pas.');
        }
        
        return response()->download(Storage::disk('public')->path($document->path), $document->name);
    }

    public function updateDocument(Request $request, $projectId, $documentId)
{
    $document = Document::where('id', $documentId)
                        ->where('project_id', $projectId)
                        ->firstOrFail();
        $request->validate([
            'name' => 'required|string|max:255',
            'access' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,docx,pptx,xlsx|max:10240',
        ]);

        $filePath = $document->path;
        $file_type = $document->file_type;

        if ($request->hasFile('file')) {
            if (Storage::disk('public')->exists($document->path)) {
                Storage::disk('public')->delete($document->path);
            }

            $file = $request->file('file');
            $filePath = $file->storeAs('documents', time() . '_' . $file->getClientOriginalName(), 'public');
            $file_type = $file->getClientOriginalExtension();
        }

        $document->update([
            'name' => $request->input('name'),
            'access' => $request->input('access'),
            'path' => $filePath,
            'file_type' => $file_type,
        ]);
        return redirect()
    ->route('projects.documents', ['projectId' => $projectId]) // Utilise 'projectId' ici
    ->with('success', 'Document mis à jour avec succès');
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
                'file' => 'required|file|mimes:pdf,doc,docx,ppt,pptx,xlsx,xls,catpart,stl,igs,stp|max:20480',
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

    

    public function deleteDocument($projectId, $documentId)
{
    $document = Document::where('project_id', $projectId)->findOrFail($documentId);

    // Suppression du fichier associé
    Storage::disk('public')->delete($document->path);

    // Suppression du document de la base de données
    $document->delete();

    // Redirection avec message de succès
    return redirect()->route('projects.show', $projectId)
                     ->with('success', 'Document deleted successfully !');
}

}
