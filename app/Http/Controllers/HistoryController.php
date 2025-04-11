<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    /**
     * Afficher l'historique de tous les documents.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Récupérer tous les historiques des documents
        $histories = History::all();

        // Retourner la vue avec les historiques
        return view('history.index', compact('histories'));
    }

    /**
     * Afficher l'historique d'un document spécifique.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        // Trouver l'historique du document avec l'ID donné
        $history = History::where('document_id', $id)->first();

        // Si aucun historique n'est trouvé, on crée un historique pour ce document
        if (!$history) {
            $document = Document::findOrFail($id);
            $history = History::create([
                'document_id' => $document->id,
                'document_name' => $document->name,
            ]);
        }

        // Retourner la vue avec l'historique
        return view('history.show', compact('history'));
    }

    /**
     * Enregistrer la consultation d'un document.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function view($id)
    {
        // Trouver le document et son historique
        $document = Document::findOrFail($id);
        $history = History::firstOrCreate(
            ['document_id' => $document->id],
            ['document_name' => $document->name]
        );

        // Mettre à jour la date de la dernière consultation
        $history->last_viewed_at = now();
        $history->save();

        // Retourner à la page du document ou vers une autre action
        return redirect()->route('documents.show', $id);
    }

    /**
     * Enregistrer une nouvelle version d'un document et mettre à jour son historique.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadNewVersion(Request $request, $id)
    {
        // Trouver le document et son historique
        $document = Document::findOrFail($id);

        // Enregistrer le fichier (ajouter ton code pour la gestion du fichier ici)

        // Mettre à jour l'historique
        $history = History::updateOrCreate(
            ['document_id' => $document->id],
            ['document_name' => $document->name]
        );

        // Mettre à jour les informations de modification
        $history->last_modified_at = now();
        $history->last_modified_by = Auth::id(); // ID de l'utilisateur connecté
        $history->save();

        return redirect()->back()->with('success', 'Nouvelle version uploadée avec succès.');
    }

    /**
     * Supprimer un historique spécifique.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        // Trouver l'historique et le supprimer
        $history = History::findOrFail($id);
        $history->delete();

        return redirect()->route('history.index')->with('success', 'Historique supprimé.');
    }
}
