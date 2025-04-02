<?php

namespace App\Http\Controllers;
use App\Models\Project;
use App\Models\Document;
use App\Models\User;
use App\Models\Access;
use Illuminate\Http\Request;

class AccessController extends Controller{
    
    public function index(Request $request)
    {
        // Récupérer les accès avec les relations
        $accesses = Access::with(['user', 'project', 'document'])->get();
        $users = User::all();  // Récupérer tous les utilisateurs
        $projects = Project::all();
        $documents = Document::all();

        // Passer les données à la vue
        return view('access.index', compact('accesses', 'users', 'projects', 'documents'));
    }
    

    public function giveAccessForm(Request $request)
{
    $users = User::all();
    $projects = Project::all();
    $documents = collect([]); // Initialiser une collection vide
    
    return view('access.givePermission', compact('users', 'projects', 'documents'));
}
public function getDocumentsByProject($projectId)
{
    try {
        // Vérifier d'abord si le projet existe
        $project = Project::find($projectId);
        
        if (!$project) {
            return response()->json([
                'error' => 'Projet non trouvé'
            ], 404);
        }

        $documents = Document::where('project_id', $projectId)->get();
        
        return response()->json($documents);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Erreur serveur'
        ], 500);
    }
}

    // Fonction pour donner un accès
    public function giveAccess(Request $request)
    {
        // Valider les données envoyées
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id',
            'document_id' => 'required|exists:documents,id',
            'permission' => 'required|in:read,write',
        ]);

        // Enregistrer la permission dans la table 'accesses'
        $access = new Access();
        $access->user_id = $request->user_id;
        $access->project_id = $request->project_id;
        $access->document_id = $request->document_id;
        $access->permission = $request->permission;
        $access->save();

        // Retourner une réponse de succès
        return redirect()->route('access.index')->with('success', 'Permission donnée avec succès.');
    }

}
