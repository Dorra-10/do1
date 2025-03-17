<?php
namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
class ProjectController extends Controller
{
    
    // Méthode pour enregistrer un nouveau projet
    public function store(Request $request)
    {
        // Valider les données du formulaire
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'date_added' => 'required|date',
        ]);

        // Création du projet
        $project = Project::create([
            'name' => $validatedData['name'],
            'type' => $validatedData['type'],
            'date_added' => $validatedData['date_added'],
        ]);

        // Redirection avec un message de succès
        return redirect()->route('projects.index')
                         ->with('status', 'Project added successfully!');
    }
    public function show($id)
{
    $project = Project::findOrFail($id);
    return view('projects.show', compact('project'));
}


    // Méthode pour afficher tous les projets
    public function index(Request $request)
    {
        $query = Project::query();

        // Filtrer uniquement sur POST avec une recherche
        if ($request->isMethod('post') && $request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', '%' . $search . '%');
        }

        $projects = $query->paginate(10);

        return view('projects.index', compact('projects'));
    }
    
    // Méthode pour mettre à jour un projet
    public function update(Request $request, Project $project)
    {
        // Validation des données
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'date_added' => 'required|date',
        ]);
    
        // Mise à jour des données du projet
        $project->update([
            'name' => $request->name,
            'type' => $request->type,
            'date_added' => $request->date_added,
        ]);
    
        // Vérifier si la requête est AJAX
        if ($request->ajax()) {
            return response()->json([
                'message' => 'Project updated successfully!',
                'project' => $project, // Renvoie le projet mis à jour
            ]);
        }
    
        // Rediriger vers la liste des projets avec un message de succès
        return redirect()->route('projects.index')->with('status', 'Project updated successfully!');
    }
    

    // Méthode pour supprimer un projet
    // ProjectController.php
public function destroy($id)
{
    $project = Project::find($id);

    if ($project) {
        $project->delete();
        return redirect()->route('projects.index')
                         ->with('status', 'Project deleted successfully!');
    }

}
public function search(Request $request)
    {
        $search = $request->query('search');

        // Filtrer les projets par nom si une recherche est saisie
        if ($search) {
            $projects = Project::where('name', 'like', '%' . $search . '%')->get();
        } else {
            $projects = Project::all(); // Afficher tous les projets si pas de recherche
        }

        // Retourner la vue avec les projets
        return view('projects.index', compact('projects')); // Ajustez 'projects.index' selon votre vue
    }


}

