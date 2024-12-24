<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class SystemInitializationController extends Controller
{
    public function showInitializationForm()
    {
        // Vérifier si un super admin existe déjà
        if (User::whereHas('roles', function ($query) {
            $query->where('name', 'super-admin');
        })->exists()) {
            return redirect('/admin');
        }

        return view('system.initialization');
    }

    public function initialize(Request $request)
    {
        // Valider les données
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            DB::beginTransaction();

            // Créer le rôle super-admin s'il n'existe pas
            $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);

            // Créer l'utilisateur super admin
            $superAdmin = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Assigner le rôle
            $superAdmin->assignRole($superAdminRole);

            DB::commit();

            return redirect('/admin')->with('success', 'Système initialisé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['system' => 'Une erreur est survenue lors de l\'initialisation.'])->withInput();
        }
    }
}
