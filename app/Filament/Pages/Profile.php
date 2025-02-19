<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Concerns\InteractsWithForms;

class Profile extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static string $view = 'filament.pages.profile';
    protected static ?string $title = 'Mon profil';
    
    public ?array $data = [];
    public $avatar;
    public $email;
    public $current_password;
    public $new_password;
    public $new_password_confirmation;
    
    public function mount(): void
    {
        $user = auth()->user();
        $this->email = $user->email;
        $this->form->fill([
            'email' => $user->email,
            'avatar' => $user->avatar,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('email')
                    ->email()
                    ->disabled()
                    ->dehydrated(false)
                    ->default(fn () => $this->email),
                    
                FileUpload::make('avatar')
                    ->image()
                    ->disk('avatars')
                    ->directory('profiles')
                    ->visibility('public')
                    ->imageEditor()
                    ->circleCropper()
                    ->maxSize(5120),
                    
                TextInput::make('current_password')
                    ->password()
                    ->label('Mot de passe actuel')
                    ->dehydrated(false),
                    
                TextInput::make('new_password')
                    ->password()
                    ->label('Nouveau mot de passe')
                    ->dehydrated(false)
                    ->confirmed(),
                    
                TextInput::make('new_password_confirmation')
                    ->password()
                    ->label('Confirmer le nouveau mot de passe')
                    ->dehydrated(false)
                    ->requiredWith('new_password'),
            ]);
    }

    public function submit()
    {
        $data = $this->form->getState();
        
        $user = auth()->user();
        
        if (filled($data['avatar'])) {
            // Supprimer l'ancien avatar s'il existe et n'est pas une URL externe
            if ($user->avatar && !str_starts_with($user->avatar, 'http')) {
                Storage::disk('avatars')->delete($user->avatar);
            }
            $user->avatar = $data['avatar'];
        }
        
        if (
            filled($this->current_password) &&
            filled($this->new_password) &&
            Hash::check($this->current_password, $user->password)
        ) {
            $user->password = Hash::make($this->new_password);
        } elseif (
            filled($this->new_password) &&
            !Hash::check($this->current_password, $user->password)
        ) {
            Notification::make()
                ->danger()
                ->title('Mot de passe incorrect')
                ->body('Le mot de passe actuel fourni est incorrect.')
                ->send();
                
            return;
        }
        
        $user->save();
        
        // Réinitialiser les champs de mot de passe
        $this->current_password = null;
        $this->new_password = null;
        $this->new_password_confirmation = null;
        
        Notification::make()
            ->success()
            ->title('Profil mis à jour')
            ->body('Vos informations ont été mises à jour avec succès.')
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('submit')
                ->label('Sauvegarder les modifications')
                ->action('submit'),
        ];
    }
}
