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
        // Valider les données envoyées
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id',
            'document_id' => 'required|exists:documents,id',
            'permission' => 'required|in:read,write',
        ]);
    
        // Récupérer l'utilisateur et le document
        $user = User::find($request->user_id);
        $document = Document::find($request->document_id);
    
        // Vérifier si l'utilisateur ou le document est introuvable
        if (!$user) {
            return redirect()->back()->with('error', 'Utilisateur introuvable.');
        }
    
        if (!$document) {
            return redirect()->back()->with('error', 'Document introuvable.');
        }
    
        // Enregistrer la permission dans la table 'accesses'
        $access = new Access();
        $access->user_id = $request->user_id;
        $access->project_id = $request->project_id;
        $access->document_id = $request->document_id;
        $access->permission = $request->permission;
        $access->save();
    
        // Envoyer l'email de notification
        Mail::to($user->email)->send(new DocumentAccessNotification($document, $request->permission, $user));
    
        return redirect()->route('access.index')->with('success', 'Access granted successfully !');
    }
    

    // Supprimer un accès
    public function deleteAccess(Request $request)
    {
        $accessId = $request->input('access_id');
        $access = Access::findOrFail($accessId);
        $access->delete();
    
        return redirect()->route('access.index')->with('success', 'Access deleted successfully!');
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
            'user_id' => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id',
            'document_id' => 'required|exists:documents,id',
            'permission' => 'required|in:read,write',
        ]);

        // Trouver l'accès et mettre à jour les données
        $access = Access::findOrFail($request->access_id);
        $access->user_id = $request->user_id;
        $access->project_id = $request->project_id;
        $access->document_id = $request->document_id;
        $access->permission = $request->permission;
        $access->save();
        return redirect()->route('access.index')->with('success', 'Access updated successfully !');
    }
    
}
