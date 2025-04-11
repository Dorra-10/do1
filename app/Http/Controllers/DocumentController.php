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

class DocumentController extends Controller
{
    public function index()
{
    $user = auth()->user();

    if ($user->hasRole(['admin', 'superviseur'])) {
        // Tous les documents + tous les projets
        $documents = Document::with('project')->paginate(10);
        $projects = Project::all();
    } else {
        // Seulement les documents accessibles à l'utilisateur
        $documents = Document::whereHas('accesses', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->whereIn('permission', ['read', 'write']);
        })->with('project')->paginate(10);

        // Seulement les projets qui ont des documents accessibles par l'utilisateur
        $projects = Project::whereHas('documents.accesses', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->whereIn('permission', ['read', 'write']);
        })->get();
    }

    return view('documents.index', compact('documents', 'projects'));
}

    public function upload(Request $request){
        $request->validate([
            'document' => 'required|file|mimes:pdf,docx,pptx,xlsx,xls|max:20480',
            'project_id' => 'required|exists:projects,id',
            'name' => 'required|string|max:255',
        ]);

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $fileName = $request->name . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('documents', $fileName, 'public');

            Document::create([
                'name' => $request->name,
                'file_type' => $file->getClientOriginalExtension(),
                'project_id' => $request->project_id,
                'path' => $path,
                'access' => $request->access,
                'date_added' => now(),
            ]);

            return redirect()->route('documents.index')->with('success', 'Document ajouté avec succès');
        }

        return redirect()->back()->with('error', 'Erreur lors de l\'ajout du document');
    }


    public function download($id){

    $document = Document::findOrFail($id);

    // Nettoyage du chemin
    $filePath = str_replace('\\', '/', ltrim($document->path, '/'));

    // Vérifie si le fichier existe dans le stockage
    if (!Storage::disk('public')->exists($filePath)) {
        logger()->error('Fichier introuvable', [
            'requested_file' => $filePath,
            'storage_root' => storage_path('app/public'),
            'file_exists' => file_exists(storage_path('app/public/'.$filePath)),
            'files_available' => Storage::disk('public')->allFiles(),
            'document_data' => $document->toArray()
        ]);
        abort(404, "Fichier non trouvé. Voir les logs pour plus de détails.");
    }

    // Téléchargement avec nom d'origine
    return Storage::disk('public')->download($filePath, $document->name);
    }




    public function update(Request $request, Document $document)
    {
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

        return redirect()->route('documents.index')->with('success', 'Document mis à jour avec succès');
    }

    public function revision(Request $request, $id){
        
    DB::beginTransaction();

    try {
        $document = Document::find($id);
        if (!$document) {
            return redirect()->back()->with('error', "Document introuvable.");
        }

        $user = auth()->user();

        // 🔒 Vérification des permissions
        $hasWriteAccess = $document->accesses()
            ->where('user_id', $user->id)
            ->where('permission', 'write')
            ->exists();

        if (! $user->hasRole(['admin', 'superviseur']) && !$hasWriteAccess) {
            return redirect()->back()->with('error', "Vous n'avez pas les permissions pour modifier ce document.");
        }

        $oldPath = $document->path;

        $request->validate([
            'file' => 'required|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,catpart,catproduct,cgr|max:20480',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $newStoragePath = 'documents/' . time() . '.' . $extension;
        Storage::disk('public')->put($newStoragePath, file_get_contents($file->getRealPath()));

        if (!Storage::disk('public')->exists($newStoragePath)) {
            throw new \Exception("Le fichier n'a pas pu être enregistré.");
        }

        $document->update([
            'name' => $file->getClientOriginalName(),
            'file_type' => $extension,
            'path' => $newStoragePath,
            'updated_at' => now(),
        ]);

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
        $document = Document::findOrFail($id);
        $filePath = storage_path('app/public/' . $document->path);

        if (file_exists($filePath)) {
            unlink($filePath);
        } elseif (Storage::disk('public')->exists($document->path)) {
            Storage::disk('public')->delete($document->path);
        }

        $document->delete();

        return redirect()->route('documents.index')->with('status', 'Document supprimé avec succès !');
    }


}
