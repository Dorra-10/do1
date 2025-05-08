<?php
namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        // Validation des champs
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . auth()->user()->id, // Email unique sauf pour l'utilisateur actuel
            'phone_number' => 'nullable|string|max:30',
        ]);
    
        // Récupérer l'utilisateur authentifié
        $user = auth()->user();
    
        // Préparer les données à mettre à jour
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
        ];
    
        // Mettre à jour les données si elles sont modifiées
        $user->update(array_filter($data)); // array_filter pour ne pas mettre à jour les champs vides
    
        // Rediriger avec un message de succès
        return redirect()->back()->with('success', 'Profile updated successfully');
    }
    public function changePassword(Request $request)
{
    $user = auth()->user();

    // Vérifier si l'utilisateur est authentifié via Microsoft (hypothèse basée sur la boîte de dialogue)
    if ($user->provider ?? '' === 'microsoft' || !empty($user->microsoft_id ?? '')) {
        return redirect()->away('https://account.microsoft.com/password')->with('info', 'Veuillez changer votre mot de passe via votre compte Microsoft.');
    }

    $validated = $request->validate([
        'current_password' => ['required'],
        'password' => [
            'required',
            'confirmed',
            Rules\Password::min(8)->mixedCase()->numbers()->symbols()
        ],
    ]);

    if (!Hash::check($request->current_password, $user->password)) {
        return redirect()->route('profile.edit')->with('erroe', 'Current Password incorrect');

    $user->update([
        'password' => Hash::make($request->password)
    ]);

    // Afficher le message de succès avant de déconnecter
    return redirect()->route('profile.edit')->with('success', 'Password Updated Successfully');
    // Note : La déconnexion automatique peut être gérée par l'utilisateur ou par un script côté client si nécessaire
}
}
}