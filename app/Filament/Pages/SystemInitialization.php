<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SystemInitialization extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?string $navigationLabel = 'Initialisation Système';
    protected static ?string $slug = 'system-initialization';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?int $navigationSort = 0;
    protected static bool $shouldRegisterNavigation = false;
    
    protected static string $view = 'filament.pages.system-initialization';
    
    public $name;
    public $email;
    public $password;
    public $passwordConfirmation;
    
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
    
    public function mount()
    {
        // Vérifier si le système est déjà initialisé
        if (User::whereHas('roles', function ($query) {
            $query->where('name', 'super_admin');
        })->exists()) {
            return redirect()->route('filament.admin.auth.login');
        }
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Création du Super Administrateur')
                    ->description('Cette opération ne peut être effectuée qu\'une seule fois.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom complet')
                            ->required()
                            ->maxLength(255),
                            
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique('users'),
                            
                        TextInput::make('password')
                            ->label('Mot de passe')
                            ->password()
                            ->required()
                            ->minLength(8)
                            ->same('passwordConfirmation'),
                            
                        TextInput::make('passwordConfirmation')
                            ->label('Confirmation du mot de passe')
                            ->password()
                            ->required()
                            ->minLength(8),
                    ]),
            ]);
    }
    
    public function initialize()
    {
        $data = $this->form->getState();
        
        try {
            DB::beginTransaction();
            
            // Créer le rôle super_admin s'il n'existe pas
            $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
            
            // Créer l'utilisateur super admin
            $superAdmin = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
            
            // Assigner le rôle
            $superAdmin->assignRole($superAdminRole);
            
            DB::commit();
            
            Notification::make()
                ->success()
                ->title('Système initialisé avec succès')
                ->body('Le super administrateur a été créé. Vous pouvez maintenant vous connecter.')
                ->persistent()
                ->send();
                
            return redirect()->route('filament.admin.auth.login');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Notification::make()
                ->danger()
                ->title('Erreur lors de l\'initialisation')
                ->body('Une erreur est survenue. Veuillez réessayer.')
                ->persistent()
                ->send();
        }
    }
}
