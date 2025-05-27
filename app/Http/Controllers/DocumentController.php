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
use Spatie\PdfToText\Pdf;
use PhpOffice\PhpWord\IOFactory;

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

    if ($user->hasRole(['admin', 'supervisor'])) {
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
        // Find the document or fail
        $document = Document::findOrFail($id);

        $user = auth()->user();

        // Check for download token
        if (!session()->has("download_token_{$id}")) {
            $msg = "You must download the original file before modifying and re-uploading it.";
            return $request->ajax()
                ? response()->json(['error' => $msg], 400)
                : redirect()->back()->with('error', $msg);
        }

        // Validate the uploaded file
        $request->validate([
            'file' => [
                'required',
                'file',
                function ($attribute, $value, $fail) use ($document) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    $allowed = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xlsx', 'xls', 'catpart', 'catproduct', 'stl', 'igs', 'iges', 'stp', 'step'];

                    // Check allowed extensions
                    if (!in_array($ext, $allowed)) {
                        $fail('Unauthorized file extension: .' . $ext);
                    }

                    // Check if extension matches the original
                    if ($ext !== $document->file_type) {
                        $fail("The file extension must match the original: '{$document->file_type}'.");
                    }

                    // Check file name matches the original
                    $newName = strtolower(trim($value->getClientOriginalName()));
                    $oldName = strtolower(trim($document->name));
                    if ($newName !== $oldName) {
                        $fail("The file name must be exactly the same as the original: '{$document->name}'.");
                    }

                    // Check file size (20 MB max)
                    if ($value->getSize() > 20 * 1024 * 1024) {
                        $fail('The file must not exceed 20 MB.');
                    }
                }
            ]
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        // Calculate the hash of the new file
        $newHash = hash_file('sha256', $file->getRealPath());

        // Check if the file is identical to the original
        if ($newHash === $document->file_hash) {
            $msg = "The uploaded file is identical to the original. No changes detected.";
            return $request->ajax()
                ? response()->json(['error' => $msg], 400)
                : redirect()->back()->with('error', $msg);
        }

        // Content similarity check for text-based files (PDF, DOCX)
        $isSimilar = true;
        if (in_array($extension, ['pdf', 'docx'])) {
            try {
                $originalContent = $this->extractContent(Storage::disk('public')->path($document->path), $extension);
                $newContent = $this->extractContent($file->getRealPath(), $extension);

                // Calculate similarity (0 = identical, 100 = completely different)
                $similarityPercent = $this->calculateTextSimilarity($originalContent, $newContent);
                
                
                if ($similarityPercent > 90) {
                    $msg = "The uploaded file is too different from the original (more than 80% difference).";
                    return $request->ajax()
                        ? response()->json(['error' => $msg], 400)
                        : redirect()->back()->with('error', $msg);
                }
            } catch (\Exception $e) {
                // Log extraction errors and proceed
                \Log::warning("Content similarity check failed: {$e->getMessage()}");
                $isSimilar = true;
            }
        } else {
            // For CAD files, use size-based fallback
            $originalSize = Storage::disk('public')->size($document->path);
            $newSize = $file->getSize();
            $sizeDiffPercent = abs($newSize - $originalSize) / $originalSize * 100;
            if ($sizeDiffPercent > 90) {
                $msg = "The uploaded file appears too different from the original (size difference exceeds 90%).";
                return $request->ajax()
                    ? response()->json(['error' => $msg], 400)
                    : redirect()->back()->with('error', $msg);
            }
        }

        // Delete the old file
        if ($document->path && Storage::disk('public')->exists($document->path)) {
            Storage::disk('public')->delete($document->path);
        }

        // Store the new file
        $newStoragePath = 'documents/' . time() . '.' . $extension;
        $fileContent = file_get_contents($file->getRealPath());
        if (!Storage::disk('public')->put($newStoragePath, $fileContent)) {
            throw new \Exception("Failed to save the file.");
        }

        // Generate new file name with date
        $originalName = pathinfo($document->name, PATHINFO_FILENAME);
        // Remove any existing date suffix (e.g., .DD-MM-YYYY)
        $cleanBaseName = preg_replace('/\.\d{2}-\d{2}-\d{4}$/', '', $originalName);
        $newDate = now()->format('d-m-Y');
        $newName = "{$cleanBaseName}.{$newDate}.{$extension}";

        // Update document
        $document->update([
            'name' => $newName,
            'file_type' => $extension,
            'file_hash' => $newHash,
            'path' => $newStoragePath,
            'version' => $document->version + 1,
            'updated_at' => now(),
        ]);

        // Record history
        History::updateOrCreate(
            ['document_id' => $document->id],
            [
                'document_name' => $document->name,
                'last_modified_at' => now(),
                'last_modified_by' => $user->id,
            ]
        );

        History::recordAction($document->id, 'modify', $user->id);

        // Clear download token
        session()->forget("download_token_{$id}");

        DB::commit();

        return $request->ajax()
            ? response()->json(['success' => 'Document updated successfully!'])
            : redirect()->back()->with('success', 'Document updated successfully!');

    } catch (\Exception $e) {
        DB::rollBack();
        $msg = 'Update failed: ' . $e->getMessage();
        return $request->ajax()
            ? response()->json(['error' => $msg], 500)
            : redirect()->back()->with('error', $msg);
    }
}

/**
 * Extract text content from a file based on its type.
 *
 * @param string $filePath
 * @param string $extension
 * @return string
 */
private function extractContent($filePath, $extension)
{
    if ($extension === 'pdf') {
        return Pdf::getText($filePath); // Requires pdftotext binary
    } elseif ($extension === 'docx') {
        $phpWord = IOFactory::load($filePath);
        $text = '';
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText();
                }
            }
        }
        return $text;
    }
    return '';
}

/**
 * Calculate text similarity using Levenshtein distance.
 *
 * @param string $text1
 * @param string $text2
 * @return float Similarity percentage (0 = identical, 100 = completely different)
 */
private function calculateTextSimilarity($text1, $text2)
{
    if (empty($text1) && empty($text2)) {
        return 0;
    }
    if (empty($text1) || empty($text2)) {
        return 100;
    }

    $len1 = strlen($text1);
    $len2 = strlen($text2);
    $maxLen = max($len1, $len2);

    // Use Levenshtein distance for short texts
    $lev = levenshtein($text1, $text2);

    // Normalize to percentage (0 = identical, 100 = completely different)
    return ($lev / $maxLen) * 100;
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

public function export(Request $request, $id)
    {
        try {
            $document = Document::findOrFail($id);
            Log::info("Starting export for document ID {$id}: {$document->name}");

            // Validate project_id
            if (!$document->project_id) {
                Log::warning("Document ID {$id} has no project_id");
                return $this->errorResponse($request, 'Document must be associated with a project.', 422);
            }

            // Normalize document name
            $normalizedName = strtolower($document->name);

            // Extract base, date, and extension
            preg_match('/^(.*?)(?:\.(\d{2}-\d{2}-\d{4}))?\.([a-zA-Z0-9]+)$/i', $normalizedName, $matches);

            $baseName = $matches[1] ? trim($matches[1]) : null;
            $date = $matches[2] ?? null;
            $extension = $matches[3] ? strtolower($matches[3]) : null;

            Log::info("Parsed document name: base={$baseName}, date={$date}, extension={$extension}");

            // Validate name format
            if (!$baseName || !$extension) {
                Log::warning("Invalid document name format for ID {$id}: {$document->name}");
                return $this->errorResponse($request, 'Invalid document name format. Expected: <base>.<DD-MM-YYYY>.<extension>', 422);
            }

            // Check for duplicate export
            if ($baseName && $date) {
                $searchPattern = "{$baseName}.{$date}.%";
                $existingExport = Export::whereRaw('LOWER(name) LIKE ?', [strtolower($searchPattern)])
                    ->where('project_id', $document->project_id)
                    ->first();

                if ($existingExport) {
                    Log::warning("Duplicate export found for document ID {$id}: {$document->name}, existing export ID: {$existingExport->id}");
                    return $this->errorResponse($request, 'This document has already been exported for this date.', 422);
                }
            }

            // Create export record
            $export = Export::create([
                'name' => $document->name,
                'file_type' => $document->file_type,
                'project_id' => $document->project_id,
                'path' => $document->path,
                'date_added' => now(),
                'owner' => $document->owner,
                'company' => $document->company,
                'description' => $document->description,
            ]);
            Log::info("Export created for document ID {$id}, Export ID: {$export->id}");

            // Mark document as exported
            $document->update(['is_exported' => true]);
            Log::info("Document ID {$id} marked as exported");

            return $this->successResponse($request, 'Document exported successfully.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("Document ID {$id} not found");
            return $this->errorResponse($request, 'Document not found.', 404);
        } catch (\Exception $e) {
            Log::error("Error exporting document ID {$id}: {$e->getMessage()}");
            return $this->errorResponse($request, 'An error occurred while exporting the document.', 500);
        }
    }

    private function successResponse(Request $request, $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }
        return redirect()->route('documents.index')->with('success', $message);
    }

    private function errorResponse(Request $request, $message, $status)
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], $status);
        }
        return redirect()->back()->with('error', $message);
    }








    
}





