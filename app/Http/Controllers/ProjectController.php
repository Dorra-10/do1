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
            $query = Project::query();
    
            // Filtrer uniquement sur POST avec une recherche
            if ($request->isMethod('post') && $request->has('search')) {
                $search = $request->input('search');
                $query->where('name', 'like', '%' . $search . '%');
            }
    
            $projects = $query->paginate(10);
    
            return view('projects.index', compact('projects'));
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
        $search = $request->query('search');

        // Filtrer les projets par nom si une recherche est saisie
        if ($search) {
            $projects = Project::where('name', '=' , $search)->get();
        } else {
            $projects = Project::all(); // Afficher tous les projets si pas de recherche
        }

        // Retourner la vue avec les projets
        return view('projects.index', compact('projects')); // Ajustez 'projects.index' selon votre vue
    }

    public function showDocuments($projectId)
    {
        $project = Project::with('documents')->findOrFail($projectId);
        $projects = Project::all(); // Récupère tous les projets
    
        return view('projects.documents', [
            'project' => $project,
            'projects' => $projects, // Passe la liste des projets à la vue
            'documents' => $project->documents,
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
            // 1. Récupérer le document avec verrouillage
            $document = Document::lockForUpdate()->findOrFail($id);
            $oldPath = $document->getRawOriginal('path'); // Sauvegarde du chemin actuel
    
            // 2. Validation stricte
            $validated = $request->validate([
                'file' => [
                    'required',
                    'file',
                    'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,catpart,catproduct,cgr',
                    'max:20480'
                ],
            ]);
    
            $file = $request->file('file');
    
            // 3. Générer le nouveau nom de fichier
            $extension = $file->getClientOriginalExtension();
            $newFileName = 'doc_'.$id.'_'.time().'_'.Str::random(8).'.'.$extension;
            $newStoragePath = 'documents/'.$newFileName;
    
            // 4. S'assurer que le dossier "documents" existe
            if (!Storage::disk('public')->exists('documents')) {
                Storage::disk('public')->makeDirectory('documents');
            }
    
            // 5. Sauvegarde du nouveau fichier
            if (!Storage::disk('public')->put($newStoragePath, file_get_contents($file->getRealPath()))) {
                throw new \Exception("Échec de l'écriture du fichier");
            }
    
            // 6. Vérifier que le fichier est bien stocké
            if (!Storage::disk('public')->exists($newStoragePath)) {
                throw new \Exception("Le fichier n'a pas été créé correctement");
            }
    
            // 7. Mise à jour des informations du document
            $document->update([
                'path' => $newStoragePath,
                'name' => $file->getClientOriginalName(),
                'file_type' => $extension,
                'updated_at' => now(),
            ]);
    
            // 8. Suppression immédiate de l'ancien fichier
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
    
            DB::commit();
    
            return redirect()->back()
                   ->with('success', 'Fichier mis à jour avec succès.');
    
        } catch (\Exception $e) {
            DB::rollBack();
    
            // Supprimer le nouveau fichier en cas d'échec
            if (isset($newStoragePath) && Storage::disk('public')->exists($newStoragePath)) {
                Storage::disk('public')->delete($newStoragePath);
            }
    
            \Log::error("Erreur lors de la mise à jour du document: ".$e->getMessage());
    
            return redirect()->back()
                   ->with('error', 'Échec de la mise à jour: '.$e->getMessage());
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
