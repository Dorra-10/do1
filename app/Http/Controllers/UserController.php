<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index(Request $request)
{
    // Récupérer la valeur de la recherche
    $search = $request->input('search');

    // Filtrer les utilisateurs en fonction du nom si un terme de recherche est fourni
    $users = User::when($search, function($query, $search) {
        return $query->where('name', 'like', "%{$search}%");
    })->paginate(10); // Pagination si nécessaire

    return view('role-permission.user.index', compact('users'));
}


    public function create()
    {
        $roles = Role::pluck('name','name')->all();
        return view('role-permission.user.create', ['roles' => $roles]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone_number' => 'nullable|string|max:30|unique:users,phone_number',
            'password' => 'required|string|min:8|max:20',
            'roles' => 'required'
        ], [
            'email.unique' => 'User already exists with this email.',
            'phone_number.unique' => 'User already exists with this phone number.',
        ]);
    
        // Création de l'utilisateur
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
        ]);
    
        // Synchronisation des rôles
        $user->syncRoles($request->roles); 
    
        // Notification
        $user->notify(new \App\Notifications\SendNewUserCredentials($request->password));
    
        // Redirection avec message de succès
        return redirect()->route('role-permission.user.index')->with('success', 'User created successfully with roles');
    }
    
    public function edit(User $user)
    {
        $roles = Role::pluck('name','name')->all();
        $userRoles = $user->roles->pluck('name','name')->all();
        return view('role-permission.user.edit', [
            'user' => $user,
            'roles' => $roles,
            'userRoles' => $userRoles
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'nullable|string|min:8|max:20',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone_number' => 'nullable|string|max:30',
            'roles' => 'required'
        ]);
    
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
        ];
    
        if(!empty($request->password)){
            $data += [
                'password' => Hash::make($request->password),
            ];
        }
    
        $user->update($data);
        $user->syncRoles($request->roles);
    
        return redirect()->route('role-permission.user.index')->with('success', 'User Updated Successfully with roles');
    }
    

    public function destroy($id)
    {
        $user = User::find($id);
        
        if (!$user) {
            return redirect()->route('role-permission.user.index')->with('error', 'User not found');
        }
    
        $user->delete();
        
        return redirect()->route('role-permission.user.index')->with('success', 'User deleted successfully');
    }
    

}