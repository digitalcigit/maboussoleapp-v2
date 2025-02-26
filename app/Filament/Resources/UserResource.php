<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'Utilisateur';

    protected static ?string $pluralModelLabel = 'Utilisateurs';

    protected static ?string $navigationLabel = 'Utilisateurs';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create', User::class);
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()->hasRole('super-admin');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('viewAny', User::class);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Nom complet'),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                TextInput::make('avatar')
                    ->label('URL de l\'avatar')
                    ->url()
                    ->maxLength(2048)
                    ->placeholder('https://example.com/avatar.jpg'),

                TextInput::make('password')
                    ->password()
                    ->required(fn ($context) => $context === 'create')
                    ->minLength(8)
                    ->same('passwordConfirmation')
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state)),

                TextInput::make('passwordConfirmation')
                    ->password()
                    ->label('Confirmation du mot de passe')
                    ->required(fn ($context) => $context === 'create')
                    ->minLength(8)
                    ->dehydrated(false),

                Select::make('roles')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->preload()
                    ->options(function () {
                        // Si c'est un manager, on ne montre pas l'option super-admin
                        if (auth()->user()->hasRole('manager')) {
                            return Role::where('name', '!=', 'super-admin')
                                ->where('name', '!=', 'prospect')
                                ->where('name', '!=', 'portail_candidat')
                                ->where('name', '!=', 'manager')
                                ->pluck('name', 'id');
                        }
                        else if (auth()->user()->hasRole('super-admin')) {
                            return Role::where('name', '!=', 'prospect')
                            ->where('name', '!=', 'portail_candidat')
                            ->where('name', '!=', 'super-admin')
                            ->pluck('name', 'id');
                        }
                        else if (auth()->user()->hasRole('conseiller')) {
                            return Role::where('name', '!=', 'prospect')
                            ->where('name', '!=', 'portail_candidat')
                            ->where('name', '!=', 'super-admin')
                            ->where('name', '!=', 'manager')
                            ->where('name', '!=', 'conseiller')
                            ->pluck('name', 'id');
                        } else {
                            return Role::where('name', '!=', 'prospect')
                            ->where('name', '!=', 'portail_candidat')
                            ->where('name', '!=', 'super-admin')
                            ->where('name', '!=', 'manager')
                            ->where('name', '!=', 'conseiller')
                            ->pluck('name', 'id');
                        }

                    })
                    ->visible(fn () => auth()->user()->can('assignRole', auth()->user()))
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->label('ID'),

                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->label('Nom'),

                Tables\Columns\TextColumn::make('email')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Rôles')
                    ->badge(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->label('Date de création'),
            ])
            ->defaultSort('created_at', 'desc')
            ->persistSortInSession()
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function afterCreate(): void
    {
        $admins = \App\Models\User::role(['super-admin', 'manager'])->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\NewUserNotification(auth()->user()));
        }
    }
}
