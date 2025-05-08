<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
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
use App\Models\History;
use App\Models\Export;

class DocumentController extends Controller
{
    public function index(Request $request)
{
    $user = auth()->user();
    $searchTerm = $request->input('search', '');

    $filetypes = [
        1 => 'pdf',
        2 => 'docx',
        3 => 'pptx',
        4 => 'xls',
        5 => 'catia',
    ];

    // Base de la requête
    $documentsQuery = Document::query();

    if ($user->hasRole(['admin', 'superviseur'])) {
        // Tous les documents pour les rôles élevés
        $projects = Project::all();
    } else {
        // Documents accessibles à l'utilisateur
        $documentsQuery->whereHas('accesses', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->whereIn('permission', ['read', 'write']);
        });

        // Projets liés à ces documents
        $projects = Project::whereHas('documents.accesses', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->whereIn('permission', ['read', 'write']);
        })->get();
    }

    // Ajout de la recherche
    if (!empty($searchTerm)) {
        $documentsQuery->where(function($query) use ($searchTerm) {
            $query->where('name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('file_type', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhereHas('project', function($q) use ($searchTerm) {
                      $q->where('name', 'LIKE', '%' . $searchTerm . '%');
                  });
        });
    }

    // Exécution finale de la requête
    $documents = $documentsQuery->with('project')
                                ->orderBy('date_added', 'desc')
                                ->paginate(10);

    return view('documents.index', compact('documents', 'projects', 'searchTerm', 'filetypes'));
}




    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'file' => [
                'required',
                'file',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    $allowed = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'catpart', 'catproduct', 'cgr', 'stl', 'igs','iges', 'stp','step'];
                    if (!in_array($ext, $allowed)) {
                        $fail('Extension non autorisée : .' . $ext);
                    }
                }
            ],
            'project_id' => 'required|exists:projects,id',
            'description' => 'nullable|string',
            'is_locked' => 'nullable|boolean',
            'company' => 'nullable|string|max:255'
        ]);

        DB::beginTransaction();

        try {
            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());
            $fileName = $validated['name'] . now()->format('d-m-Y') . '.' . $extension;

            $hash = hash_file('sha256', $file->getRealPath());

            if (Document::where('file_hash', $hash)->exists()) {
                return back()->with('error', 'This document already exists !');
            }

            $path = $file->storeAs('documents', $fileName, 'public');

            Document::create([
                'name' => $fileName,
                'file_type' => $extension,
                'file_hash' => $hash,
                'path' => $path,
                'project_id' => $validated['project_id'],
                'owner' => auth()->id(),
                'company' => $validated['company'] ?? null,
                'description' => $validated['description'] ?? null,
                'is_locked' => $validated['is_locked'] ?? false,
                'date_added' => now()
            ]);

            DB::commit();
            return redirect()->route('documents.index')->with('success', 'Document created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
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
                        $fail('Unauthorized extension: .' . $ext);
                    }
                }
            ],
            'project_id' => 'required|exists:projects,id',
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    // Extract the base name (without date and extension) from the input name
                    $baseName = $value;

                    // Check for existing documents globally
                    $existingDocuments = Document::all();

                    foreach ($existingDocuments as $doc) {
                        // Extract the base name from the existing document's name (e.g., FinalTest.. from FinalTest..05-05-2025.pptx)
                        $nameParts = explode('.', $doc->name);
                        $docExtension = array_pop($nameParts); // Remove the extension
                        $docBaseNameWithDots = implode('.', array_slice($nameParts, 0, -1)); // Remove the date part
                        $docBaseName = $docBaseNameWithDots;

                        // Compare the base names
                        if ($baseName === $docBaseName) {
                            $fail('A document with this base name already exists globally, regardless of date or extension.');
                            return back()->with('error', 'A document with this base name already exists!');
                        }
                    }
                }
            ],
            'access' => 'nullable|string|max:255',
            'owner' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $file = $request->file('document');
        $extension = strtolower($file->getClientOriginalExtension());
        $fileName = $validated['name'] . '.' . now()->format('d-m-Y') . '.' . $extension;

        $hash = hash_file('sha256', $file->getRealPath());

        // Check if a document with the same hash already exists
        if (Document::where('file_hash', $hash)->exists()) {
            return back()->with('error', 'This document already exists!');
        }

        // Store the file
        $path = $file->storeAs('documents', $fileName, 'public');

        // Determine the file type ID based on extension
        $fileType = match ($extension) {
            'pdf' => 1,
            'docx', 'doc' => 2,
            'pptx', 'ppt' => 3,
            'xlsx', 'xls' => 4,
            default => null,
        };

        if (!$fileType) {
            $fileType = 0;
        }

        // Create the document record
        $document = Document::create([
            'name' => $fileName,
            'type_id' => $fileType,
            'file_hash' => $hash,
            'file_type' => $extension,
            'project_id' => $validated['project_id'],
            'path' => $path,
            'owner' => $validated['owner'] ?? auth()->id(),
            'company' => $validated['company'] ?? null,
            'description' => $validated['description'] ?? null,
            'date_added' => now()
        ]);

        // Record the history
      

        return redirect()->route('documents.index')->with('success', 'Document created successfully!');
}


    public function download($id)
    {
        // Retrieve the document
        $document = Document::findOrFail($id); // Find the document or throw a 404 error
        History::recordAction($document->id, 'view', auth()->id());

        // Clean the file path (ensure slashes are correct)
        $filePath = str_replace('\\', '/', ltrim($document->path, '/'));

        // Check if the file exists in storage
        if (!Storage::disk('public')->exists($filePath)) {
            // Log the error if the file does not exist
            logger()->error('File not found', [
                'requested_file' => $filePath,
                'storage_root' => storage_path('app/public'),
                'file_exists' => file_exists(storage_path('app/public/'.$filePath)),
                'document_data' => $document->toArray()
            ]);

            // Return a 404 error with a clear message
            abort(404, "The requested file could not be found. Check the logs for more details.");
        }

        // Get the file extension
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        // Generate a unique token for this download
        $downloadToken = Str::uuid()->toString();
        
        // Store the token in the session, associated with the document ID
        session()->put("download_token_{$id}", $downloadToken);

        // Update the view history
        $history = History::firstOrNew(['document_id' => $document->id]);
        $history->document_name = $document->name;
        $history->last_viewed_by = Auth::id(); // Get the authenticated user
        $history->last_viewed_at = now();
        $history->save();

        // Download the file with its original name and extension
        return Storage::disk('public')->download($filePath, $document->name);
    }

   
 // Afficher le formulaire d'édition d'un document
public function edit($id)
{
    // Récupérer le document par son ID
    $document = Document::findOrFail($id);
    $projects = Project::all();
    // Retourner la vue avec les données du document à éditer
    return view('documents.edit', compact('document','projects'));
}

// Mettre à jour un document
public function update(Request $request, $id)
{
    // Valider les données du formulaire
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'project_id' => 'required|exists:projects,id',
        'owner' => 'required|string|max:255',
        'company' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'date_added' => 'required|date',
    ]);

    // Trouver le document à mettre à jour
    $document = Document::findOrFail($id);

    // Mettre à jour les informations du document
    $document->update([
        'name' => $validatedData['name'],
        'project_id' => $validatedData['project_id'],
        'owner' => $validatedData['owner'],
        'company' => $validatedData['company'],
        'description' => $validatedData['description'],
        'date_added' => $validatedData['date_added'],
    ]);

    // Retourner une réponse ou rediriger
    return redirect()->route('documents.index')->with('success', 'Document updated successfully !');
}

public function revision(Request $request, $id)
{
    DB::beginTransaction();

    try {
        $document = Document::find($id);
        if (!$document) {
            return $request->ajax()
                ? response()->json(['error' => 'Document not found.'], 404)
                : redirect()->back()->with('error', 'Document not found.');
        }

        $user = auth()->user();

        // Check if a download token exists for this document
        $downloadToken = session()->get("download_token_{$id}");
        if (!$downloadToken) {
            $msg = "You must first download the original file before modifying and re-uploading it.";
            return $request->ajax()
                ? response()->json(['error' => $msg], 400)
                : redirect()->back()->with('error', $msg);
        }

        // Custom validation
        $request->validate([
            'file' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!($value instanceof \Illuminate\Http\UploadedFile)) {
                        return $fail('The field must contain a valid file.');
                    }

                    $ext = strtolower($value->getClientOriginalExtension());
                    $allowed = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xlsx', 'xls', 'catpart', 'stl', 'igs', 'iges', 'stp', 'step'];
                    if (!in_array($ext, $allowed)) {
                        $fail('Unauthorized extension: .' . $ext);
                    }

                    // Max size 20 MB
                    if ($value->getSize() > 20 * 1024 * 1024) {
                        $fail('The file must not exceed 20 MB.');
                    }
                }
            ]
        ]);

        $file = $request->file('file');

        $oldName = strtolower(trim($document->name));
        $newName = strtolower(trim($file->getClientOriginalName()));

        if ($oldName !== $newName) {
            $msg = "The file name must be exactly the same as the old one: {$document->name}";
            return $request->ajax()
                ? response()->json(['error' => $msg], 400)
                : redirect()->back()->with('error', $msg);
        }

        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension !== $document->file_type) {
            $msg = "The file type must be the same as the old one: '{$document->file_type}'.";
            return $request->ajax()
                ? response()->json(['error' => $msg], 400)
                : redirect()->back()->with('error', $msg);
        }

        // Calculate the hash of the new file
        $newHash = hash_file('sha256', $file->getRealPath());

        // Check if the file is identical (same hash)
        if ($newHash === $document->file_hash) {
            $msg = "The file is identical to the original. No changes detected.";
            return $request->ajax()
                ? response()->json(['error' => $msg], 400)
                : redirect()->back()->with('error', $msg);
        }

        // Delete the old file
        $oldPath = $document->path;
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        // Store the new file
        $newStoragePath = 'documents/' . time() . '.' . $extension;
        Storage::disk('public')->put($newStoragePath, file_get_contents($file->getRealPath()));

        if (!Storage::disk('public')->exists($newStoragePath)) {
            throw new \Exception("The file could not be saved.");
        }

        $nameParts = explode('.', $document->name);
            $extension = array_pop($nameParts); // Get the extension (e.g., pptx)
            $baseNameWithDots = implode('.', array_slice($nameParts, 0, -1)); // Get the base name up to the date (e.g., FinalTest.)
            $baseName = $baseNameWithDots; // Keep the dots as per your example (FinalTest..)

            // Append the new date in DD-MM-YYYY format
            $newDate = now()->format('d-m-Y');
            $newNameWithDate = "{$baseName}.{$newDate}.{$extension}";

            // Update the document with the updated name
            $document->update([
                'name' => $newNameWithDate,
                'file_type' => $extension,
                'file_hash' => $newHash,
                'path' => $newStoragePath,
                'version' => $document->version + 1,
                'updated_at' => now(),
            ]);

        // Record the history
        History::recordAction($document->id, 'modify', $user->id);

        $history = History::firstOrNew(['document_id' => $document->id]);
        $history->document_name = $document->name;
        $history->last_modified_at = now();
        $history->last_modified_by = $user->id;
        $history->save();

        // Clear the download token from the session after successful upload
        session()->forget("download_token_{$id}");

        DB::commit();

        clearstatcache();
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return $request->ajax()
            ? response()->json(['success' => 'Content updated successfully!'])
            : redirect()->back()->with('success', 'Content updated successfully!');

    } catch (\Exception $e) {
        DB::rollBack();
        $msg = 'Update failed: ' . $e->getMessage();

        return $request->ajax()
            ? response()->json(['error' => $msg], 500)
            : redirect()->back()->with('error', $msg);
    }
}







    public function destroy($id)
{
    // Trouver le document dans la base de données
    $document = Document::findOrFail($id);

    try {
        // Supprimer le fichier du stockage (si il existe)
        if (Storage::disk('public')->exists($document->path)) {
            Storage::disk('public')->delete($document->path);
        } else {
            return redirect()->route('documents.index')->with('error', 'Le fichier n\'existe pas sur le disque');
        }

        // Utiliser forceDelete pour une suppression définitive (si SoftDeletes est activé)
        $document->forceDelete();

        // Retourner un message de succès
        return redirect()->route('documents.index')->with('success', 'Documents deleted successfully !');
        
    } catch (\Exception $e) {
        // Gestion des erreurs
        return redirect()->route('documents.index')->with('error', 'Erreur lors de la suppression du document: ' . $e->getMessage());
    }
}



public function lock(Request $request, $id)
{
    $document = Document::findOrFail($id);

    $document->is_locked = true;
    $document->save();

    Export::create([
        'name' => $document->name,
        'file_type' => $document->file_type,
        'project_id' => $document->project_id,
        'path' => $document->path,
        'date_added' => now(),
        'owner' => $document->owner,
        'company' => $document->company,
        'description' => $document->description,
    ]);

    // Réponse JSON pour les appels JS
    if ($request->expectsJson()) {
        return response()->json(['success' => true]);
    }

    // Fallback classique
    return redirect()->route('documents.index')->with('success', 'Document successfully locked and exported.');
}






    
}





