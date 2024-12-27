<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CleanUsersExceptSuperAdmin extends Command
{
    protected $signature = 'users:clean-except-super-admin';
    protected $description = 'Supprime tous les utilisateurs sauf le super admin';

    public function handle()
    {
        $this->info('Nettoyage des utilisateurs en cours...');
        
        // Préserver le super admin
        $superAdmin = User::role('super-admin')->first();
        
        if (!$superAdmin) {
            $this->error('Super admin non trouvé !');
            return 1;
        }

        // Supprimer tous les autres utilisateurs
        User::where('id', '!=', $superAdmin->id)->delete();
        
        $this->info('Nettoyage terminé. Seul le super admin reste.');
        $this->info('Email du super admin préservé : ' . $superAdmin->email);
        
        return 0;
    }
}
