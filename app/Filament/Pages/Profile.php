<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\FileUpload;

class Profile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationGroup = 'Compte';
    protected static ?string $title = 'Mon profil';
    protected static ?string $slug = 'profile';
    protected static bool $shouldRegisterNavigation = false; // Cacher le lien dans la navigation principale
    protected static string $view = 'filament.pages.profile';
    
    public ?array $data = [];
    
    public function mount(): void
    {
        $this->form->fill([
            'email' => auth()->user()->email,
            'avatar' => auth()->user()->avatar,
        ]);
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations du compte')
                    ->description('Mettez à jour les informations de votre compte.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->default(auth()->user()->name)
                            ->disabled()
                            ->dehydrated(false),
                            
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique('users', 'email', ignorable: auth()->user())
                            ->autocomplete(false),
                            
                        TextInput::make('avatar')
                            ->label('URL de l\'avatar')
                            ->url()
                            ->maxLength(2048)
                            ->placeholder('https://example.com/avatar.jpg'),
                    ]),
                    
                Section::make('Mettre à jour le mot de passe')
                    ->description('Assurez-vous que votre compte utilise un mot de passe long et aléatoire pour rester sécurisé.')
                    ->schema([
                        TextInput::make('current_password')
                            ->label('Mot de passe actuel')
                            ->password()
                            ->dehydrated(false)
                            ->rules(['required_with:new_password']),
                            
                        TextInput::make('new_password')
                            ->label('Nouveau mot de passe')
                            ->password()
                            ->minLength(8)
                            ->dehydrated(false),
                            
                        TextInput::make('new_password_confirmation')
                            ->label('Confirmer le mot de passe')
                            ->password()
                            ->dehydrated(false)
                            ->rules(['same:new_password']),
                    ]),
            ])
            ->statePath('data');
    }
    
    public function submit(): void
    {
        $this->validate();
        
        $user = auth()->user();
        
        if ($this->data['email'] !== $user->email) {
            $user->email = $this->data['email'];
        }
        
        if (isset($this->data['avatar'])) {
            $user->avatar = $this->data['avatar'];
        }
        
        if (
            isset($this->data['current_password']) &&
            isset($this->data['new_password']) &&
            Hash::check($this->data['current_password'], $user->password)
        ) {
            $user->password = Hash::make($this->data['new_password']);
        } elseif (
            isset($this->data['new_password']) &&
            !Hash::check($this->data['current_password'], $user->password)
        ) {
            Notification::make()
                ->danger()
                ->title('Mot de passe incorrect')
                ->body('Le mot de passe actuel fourni est incorrect.')
                ->send();
                
            return;
        }
        
        $user->save();
        
        Notification::make()
            ->success()
            ->title('Profil mis à jour')
            ->body('Vos informations ont été mises à jour avec succès.')
            ->send();
            
        $this->reset(['data.current_password', 'data.new_password', 'data.new_password_confirmation']);
    }
}
