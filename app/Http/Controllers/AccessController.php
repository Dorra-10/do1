<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Document;
use App\Models\User;
use App\Models\Access;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\DocumentAccessNotification;

class AccessController extends Controller
{
    // Afficher les accès avec les relations
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

    // Afficher le formulaire pour donner un accès
    public function giveAccessForm(Request $request)
    {
        $users = User::all();
        $projects = Project::all();
        $documents = collect([]); // Initialiser une collection vide pour les documents

        return view('access.index', compact('users', 'projects', 'documents'));
    }

    // Récupérer les documents d'un projet spécifique
    public function getDocumentsByProject($projectId)
    {
        // Assurez-vous que la méthode renvoie bien les documents associés au projet
        $documents = Document::where('project_id', $projectId)->get();
    
        // Vérifiez si des documents existent pour le projet
        if ($documents->isEmpty()) {
            return response()->json(['message' => 'No documents found for this project.'], 404);
        }
    
        return response()->json($documents);
    }
    

    // Donner un accès à un utilisateur
    public function giveAccess(Request $request)
{
    // Valider les données
    $request->validate([
        'user_id' => 'required|array', // L'utilisateur peut être un tableau
        'user_id.*' => 'exists:users,id', // Chaque utilisateur dans le tableau doit exister
        'project_id' => 'required|exists:projects,id',
        'document_id' => 'required|exists:documents,id',
        'permission' => 'required|in:read,write',
    ]);

    // Récupérer le document en fonction de l'ID du projet et du document
    $document = Document::findOrFail($request->document_id);

    // Récupérer les utilisateurs pour lesquels on va donner l'accès
    $users = User::whereIn('id', $request->user_id)->get();

    // Créer l'accès pour chaque utilisateur
    foreach ($users as $user) {
        // Vérifiez si l'utilisateur est valide avant de continuer
        if (!$user) {
            continue; // Si l'utilisateur est invalide, passez à l'utilisateur suivant
        }

        $exists = Access::where('user_id', $user->id)
                        ->where('project_id', $request->project_id)
                        ->where('document_id', $request->document_id)
                        ->exists();

        if ($exists) {
            continue; // Passer à l'utilisateur suivant s'il a déjà l'accès
        }

        // Créer l'accès pour l'utilisateur
        Access::create([
            'user_id' => $user->id,
            'project_id' => $request->project_id,
            'document_id' => $request->document_id,
            'permission' => $request->permission,
        ]);

        // Envoyer un email à chaque utilisateur avec son nom
        if ($user && $user->email) {
            \Mail::to($user->email)->send(new DocumentAccessNotification($document, $request->permission, $user));
        }
    }

    return redirect()->route('access.index')->with('success', 'Access granted to selected users successfully!');
}



    

    
    

    


    // Supprimer un accès
    public function deleteAccess(Request $request)
{
    $accessId = $request->input('access_id');

    if (!$accessId) {
        return redirect()->route('access.index')->with('error', 'Aucun ID fourni.');
    }

    $access = Access::find($accessId);

    if (!$access) {
        return redirect()->route('access.index')->with('error', 'Access not found.');
    }

    $access->delete();

    return redirect()->route('access.index')->with('success', 'Access deleted successfully !');
}

    
    // Afficher le formulaire pour éditer une permission d'accès
    public function editAccessForm($accessId)
    {
        // Récupérer l'accès par son ID
        $access = Access::findOrFail($accessId);
        
        // Récupérer les utilisateurs et les projets
        $users = User::all();
        $projects = Project::all();
    
        // Retourner la vue avec les données pour le formulaire d'édition
        return view('access.editPermission', compact('users', 'projects', 'access'));
    }
    

    // Mettre à jour un accès
    public function update(Request $request)
{
    // Validation des données
    $request->validate([
        'access_id' => 'required|exists:accesses,id',
        'permission' => 'required|in:read,write',
    ]);

    // Trouver l'accès et mettre à jour uniquement le type de permission
    $access = Access::findOrFail($request->access_id);
    $access->permission = $request->permission;
    $access->save();

    return redirect()->route('access.index')->with('success', 'Access type updated successfully!');
}

    
}
