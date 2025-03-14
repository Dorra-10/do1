<?php
namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

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
    public function index()
    {
        // Récupérer tous les projets, ou vous pouvez ajouter une pagination pour de meilleures performances
        $projects = Project::paginate(10); // Utilisation de la pagination pour éviter des chargements lourds si vous avez beaucoup de projets

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


}

