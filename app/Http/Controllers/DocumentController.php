<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index()
    {
        // Récupérer tous les documents
        $documents = Document::all();
        
        // Retourner la vue avec les documents
        return view('documents.index', compact('documents'));
    }
    public function store(Request $request)
{
    // Validation des données du formulaire
    $request->validate([
        'name' => 'required',
        'type' => 'required',
        'project_id' => 'required',
        'acces' => 'required',
        'date_added' => 'required|date',
    ]);

    // Créer un nouveau document
    Document::create([
        'name' => $request->name,
        'type' => $request->type,
        'project_id' => $request->project_id,
        'acces' => $request->acces,
        'date_added' => $request->date_added,
    ]);

    // Rediriger vers la page des documents avec un message de succès
    return redirect()->route('documents.index')->with('success', 'Document added successfully');
}
public function update(Request $request, Document $document)
{
    // Validation des données
    $request->validate([
        'name' => 'required|string|max:255',
        'type' => 'required|string|max:255',
        'date_added' => 'required|date',
        'project_id' => 'required|exists:projects,id', // Assurez-vous que le document est lié à un projet existant
        'acces' => 'required|string|max:255', // Ajoutez la validation pour le champ `acces`
    ]);

    // Mise à jour des données du document
    $document->update([
        'name' => $request->name,
        'type' => $request->type,
        'date_added' => $request->date_added,
        'project_id' => $request->project_id, // Le projet auquel le document est associé
        'acces' => $request->acces, // Le champ `acces`
    ]);

    // Vérifier si la requête est AJAX
    if ($request->ajax()) {
        return response()->json([
            'message' => 'Document updated successfully!',
            'document' => $document, // Renvoie le document mis à jour
        ]);
    }

    // Rediriger vers la liste des documents avec un message de succès
    return redirect()->route('documents.index')->with('status', 'Document updated successfully!');
}
public function destroy($id)
{
    // Recherche du document par son ID
    $document = Document::find($id);

    // Si le document est trouvé
    if ($document) {
        // Supprimer le fichier s'il existe (si applicable)
        if ($document->file_path && file_exists(storage_path('app/' . $document->file_path))) {
            unlink(storage_path('app/' . $document->file_path));  // Supprimer le fichier
        }

        // Supprimer le document de la base de données
        $document->delete();

        // Rediriger avec un message de succès
        return redirect()->route('documents.index')->with('status', 'Document deleted successfully!');
    }

    // Si le document n'est pas trouvé
    return redirect()->route('documents.index')->with('error', 'Document not found!');
}




}
