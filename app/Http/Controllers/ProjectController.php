<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Document;
use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\PdfToText\Pdf;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProjectController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $user = auth()->user();
        $searchTerm = $request->input('search', '');

        $query = $user->hasRole(['admin', 'supervisor']) ?
            Project::query() :
            Project::whereHas('documents.accesses', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->whereIn('permission', ['read', 'write']);
            });

        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%$searchTerm%")
                  ->orWhere('type', 'LIKE', "%$searchTerm%");
            });
        }

        $projects = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('projects.index', compact('projects', 'searchTerm'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'date_added' => 'required|date',
        ]);

        $existingProject = Project::where('name', $validatedData['name'])->first();

        if ($existingProject) {
            return redirect()->route('projects.index')
                             ->withInput()
                             ->with('error', 'A project with this name already exists!');
        }

        Project::create($validatedData);

        return redirect()->route('projects.index')
                         ->with('success', 'Project created successfully!');
    }

    public function show($id)
    {
        $project = Project::findOrFail($id);
        return view('projects.show', compact('project'));
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

        return redirect()->route('projects.index')->with('success', 'Project updated successfully!');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted successfully!');
    }

    public function search(Request $request)
    {
        $searchTerm = trim($request->input('search'));
        $projects = Project::query();

        if (!empty($searchTerm)) {
            $projects->where('name', 'LIKE', "%$searchTerm%")
                     ->orWhere('description', 'LIKE', "%$searchTerm%");
        }

        $projects = $projects->latest()->get();

        if ($request->ajax()) {
            return response()->json([
                'table' => view('projects.partials.table', compact('projects'))->render()
            ]);
        }

        return view('projects.index', compact('projects'));
    }

    public function showDocuments($projectId)
    {
        $user = auth()->user();
        $project = Project::with('documents')->findOrFail($projectId);
        $projects = Project::all();

        $documents = $user->hasRole(['admin', 'supervisor']) ?
            $project->documents :
            $project->documents()->whereHas('accesses', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->whereIn('permission', ['read', 'write']);
            })->get();

        return view('projects.documents', compact('project', 'projects', 'documents'));
    }

    public function updateDocument(Request $request, $projectId, Document $document)
{
    if ($document->project_id != $projectId) {
        \Log::error('Document does not belong to project', ['document_id' => $document->id, 'project_id' => $projectId]);
        return redirect()->route('projects.documents', ['projectId' => $projectId])
                        ->with('error', 'Ce document n\'appartient pas à ce projet.');
    }

    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'owner' => 'required|string|max:255',
        'company' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'date_added' => 'required|date',
    ]);

    \Log::info('Updating document', ['document_id' => $document->id, 'data' => $validatedData]);

    $result = $document->update($validatedData);

    if ($result) {
        \Log::info('Document updated successfully', ['document_id' => $document->id]);
    } else {
        \Log::error('Failed to update document', ['document_id' => $document->id]);
    }

    // Debug the redirect
    $redirectUrl = route('projects.documents', ['projectId' => $projectId]);
    \Log::info('Redirecting to', ['url' => $redirectUrl]);

    return redirect()->route('projects.documents', ['projectId' => $projectId])
                    ->with('success', 'Document updated successfully !');
}
    public function download($id)
    {
        $document = Document::findOrFail($id);
        History::recordAction($document->id, 'view', auth()->id());

        $filePath = str_replace('\\', '/', ltrim($document->path, '/'));

        if (!Storage::disk('public')->exists($filePath)) {
            Log::error('File not found', [
                'file' => $filePath,
                'document' => $document->toArray()
            ]);

            abort(404, "The requested file could not be found.");
        }

        $downloadToken = Str::uuid()->toString();
        session()->put("download_token_{$id}", $downloadToken);

        $history = History::firstOrNew(['document_id' => $document->id]);
        $history->document_name = $document->name;
        $history->last_viewed_by = Auth::id();
        $history->last_viewed_at = now();
        $history->save();

        return Storage::disk('public')->download($filePath, $document->name);
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
                
                // Ensure content is partially modified (20-80% similarity)
                if ($similarityPercent > 90) {
                    $msg = "The uploaded file is too different from the original (more than 90% difference).";
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
                $msg = "The uploaded file appears too different from the original (size difference exceeds 80%).";
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
