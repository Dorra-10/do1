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
                         ->with('success', 'Projet ajouté avec succès !');
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

        return redirect()->route('projects.index')->with('success', 'Projet mis à jour avec succès !');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Projet supprimé avec succès !');
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

    public function updateDocument(Request $request, $projectId, Document $document)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'access' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,docx,pptx,xlsx|max:10240',
        ]);

        if ($request->hasFile('file')) {
            Storage::disk('public')->delete($document->path);
            $file = $request->file('file');
            $filePath = $file->storeAs('documents', time() . '_' . $file->getClientOriginalName(), 'public');
            $document->update(['path' => $filePath, 'file_type' => $file->getClientOriginalExtension()]);
        }

        $document->update($request->only(['name', 'access']));
        return redirect()->route('projects.show', $projectId)->with('success', 'Document mis à jour avec succès.');
    }


    public function revision(Request $request, $id)
    {
        DB::beginTransaction();
    
        try {
            // Affichage de l'ID dans la requête
            Log::info("ID reçu dans la requête : $id");
    
            // Récupération du document par ID
            $document = Document::find($id);
            
            // Vérification que le document existe bien
            if (!$document) {
                Log::error("Document ID $id introuvable.");
                return redirect()->back()->with('error', "Document introuvable.");
            }
    
            // Affichage de l'ID du document dans la base de données
            Log::info("Tentative de mise à jour du document avec l'ID : $document->id");
    
            // Récupérer l'ancien chemin du fichier
            $oldPath = $document->path;
            Log::info("Ancien chemin du fichier : $oldPath");
    
            // Validation du fichier
            $request->validate([
                'file' => 'required|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,catpart,catproduct,cgr|max:20480',
            ]);
    
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
    
            // Vérification que le document a un chemin valide
            if (!$oldPath) {
                throw new \Exception("Le document ne possède pas de chemin de fichier valide.");
            }
    
            // Vérifier si le fichier existe avant de tenter de le supprimer
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Log::info("Le fichier existe, suppression en cours : $oldPath");
                Storage::disk('public')->delete($oldPath);
                Log::info("Ancien fichier supprimé : $oldPath");
            } else {
                Log::error("Fichier introuvable : $oldPath");
            }
    
            // Enregistrer le fichier sous un nouveau chemin (ou le même si tu veux écraser)
            $newStoragePath = 'documents/' . time() . '.' . $extension; // Créer un nouveau nom de fichier unique
            Storage::disk('public')->put($newStoragePath, file_get_contents($file->getRealPath()));
    
            // Vérifier si le fichier est bien stocké
            if (!Storage::disk('public')->exists($newStoragePath)) {
                throw new \Exception("Le fichier n'a pas pu être enregistré.");
            }
    
            // Mise à jour de la base de données avec le nouveau chemin
            $document->update([
                'name' => $file->getClientOriginalName(),
                'file_type' => $extension,
                'path' => $newStoragePath, // Mettre à jour le chemin pour que ce soit celui du nouveau fichier
                'updated_at' => now(),
            ]);
    
            DB::commit();
    
            // Nettoyage du cache
            clearstatcache();
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
    
            Log::info("Fichier mis à jour avec succès : $newStoragePath");
    
            return redirect()->back()->with('success', 'Fichier mis à jour avec succès.');
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors de la mise à jour du fichier : " . $e->getMessage());
    
            return redirect()->back()->with('error', 'Échec de la mise à jour: ' . $e->getMessage());
        }
    }
    

    public function deleteDocument($projectId, $documentId)
    {
        $document = Document::where('project_id', $projectId)->findOrFail($documentId);
        Storage::disk('public')->delete($document->path);
        $document->delete();
        return redirect()->route('projects.show', $projectId)->with('success', 'Document supprimé avec succès !');
    }
}
