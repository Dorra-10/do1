<?php

namespace App\Http\Controllers;
use App\Models\Document;
use App\Models\Import;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class ImportController extends Controller
{
    public function index()
    {
        $projects = Project::all();
        $imports = Import::all();  // Récupère tous les enregistrements de la table 'exports'
        return view('impoexpo.impo.index', compact('imports','projects'));
    }

    public function upload(Request $request)
{
    $validated = $request->validate([
        'document' => [
            'required',
            'file',
            function ($attribute, $value, $fail) {
                $ext = strtolower($value->getClientOriginalExtension());
                $allowed = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xlsx', 'xls', 'catpart', 'catproduct', 'cgr', 'stl', 'igs', 'iges', 'stp', 'step'];
                if (!in_array($ext, $allowed)) {
                    $fail('Extension non autorisée : .' . $ext);
                }
            }
        ],
        'project_id' => 'required|exists:projects,id',
        'name' => 'required|string|max:255',
        'access' => 'nullable|string|max:255',
        'owner' => 'nullable|string|max:255',
        'company' => 'nullable|string|max:255',
        'description' => 'nullable|string',
    ]);

    $file = $request->file('document');
    $extension = strtolower($file->getClientOriginalExtension());

    // Nettoyer le nom du fichier
    $baseName = Str::slug(pathinfo($validated['name'], PATHINFO_FILENAME));
    $fileName = $baseName . '-' . now()->format('d-m-Y') . '.' . $extension;

    // 🔒 Vérification du nom globalement (pas seulement par projet)
    if (Import::where('name', $fileName)->exists()) {
        return back()->with('error', 'A document with this name already exists !');
    }

    // Vérifier par hash (évite les doublons exacts)
    $hash = hash_file('sha256', $file->getRealPath());
    if (Import::where('file_hash', $hash)->exists()) {
        return back()->with('error', 'Document already imported !');
    }

    // Sauvegarder le fichier
    $path = $file->storeAs('imports', $fileName, 'public');

    // Déduire le type
    $fileType = match ($extension) {
        'pdf' => 1,
        'doc', 'docx' => 2,
        'ppt', 'pptx' => 3,
        'xls', 'xlsx' => 4,
        default => 0,
    };

    try {
        $import = Import::create([
            'name' => $fileName,
            'type_id' => $fileType,
            'file_hash' => $hash,
            'file_type' => $extension,
            'project_id' => $validated['project_id'],
            'path' => $path,
            'owner' => $validated['owner'] ?? auth()->user()->name,
            'company' => $validated['company'] ?? null,
            'description' => $validated['description'] ?? null,
            'date_added' => now(),
        ]);

        Document::create([
            'name' => $import->name,
            'type_id' => $import->type_id,
            'file_type' => $import->file_type,
            'project_id' => $import->project_id,
            'path' => $import->path,
            'owner' => $import->owner,
            'company' => $import->company,
            'description' => $import->description,
            'date_added' => $import->date_added,
            'access' => $validated['access'] ?? 'private',
        ]);

        return redirect()->route('impoexpo.impo.index')->with('success','Document imported successfully !');

    } catch (\Exception $e) {
        return back()->with('error', 'Error while importing: '. $e->getMessage());
    }
}

    


}
