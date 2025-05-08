<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Document;
use App\Models\User;
use App\Models\Import;
use App\Models\Export;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
{
    $totalProjects = Project::count();
    $totalDocuments = Document::count();
    $totalUsers = User::count();
    $totalImports = Import::count();
    $totalExports = Export::count();

    // 🆕 Statistiques par type de projet
    $projectTypeStats = Project::select('type', DB::raw('count(*) as count'))
                               ->groupBy('type')
                               ->get()
                               ->pluck('count', 'type'); // Ex: ['in progress' => 5, 'completed' => 10, ...]

    $documentTypesStats = Document::select('file_type', DB::raw('count(*) as count'))
                                  ->groupBy('file_type')
                                  ->get();

    $topProjects = Project::withCount('documents')
                          ->orderBy('documents_count', 'desc')
                          ->take(5)
                          ->get();

    $recentDocuments = Document::latest()->take(5)->get();

    return view('dashboard.index', compact(
        'totalProjects',
        'totalDocuments',
        'totalUsers',
        'totalImports',
        'totalExports',
        'documentTypesStats',
        'topProjects',
        'recentDocuments',
        'projectTypeStats' // 🆕
    ));
}

    

}
