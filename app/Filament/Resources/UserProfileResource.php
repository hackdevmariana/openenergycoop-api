<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserProfileResource\Pages;
use App\Filament\Resources\UserProfileResource\RelationManagers;
use App\Models\UserProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserProfileResource extends Resource
{
    protected static ?string $model = UserProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationGroup = 'Gestión de Usuarios';

    protected static ?string $navigationLabel = 'Perfiles de Usuario';

    protected static ?string $modelLabel = 'Perfil de Usuario';

    protected static ?string $pluralModelLabel = 'Perfiles de Usuario';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::count();
        
        return match (true) {
            $count >= 100 => 'success',
            $count >= 50 => 'warning',
            $count >= 10 => 'info',
            default => 'gray',
        };
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Usuario')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Usuario')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('organization_id')
                            ->label('Organización')
                            ->relationship('organization', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('team_id')
                            ->label('Equipo')
                            ->maxLength(255)
                            ->placeholder('ID del equipo dentro de la cooperativa'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Información Personal')
                    ->schema([
                        Forms\Components\FileUpload::make('avatar')
                            ->label('Avatar')
                            ->image()
                            ->directory('avatars')
                            ->visibility('public'),

                        Forms\Components\Textarea::make('bio')
                            ->label('Biografía')
                            ->maxLength(500)
                            ->rows(3),

                        Forms\Components\DatePicker::make('birth_date')
                            ->label('Fecha de Nacimiento')
                            ->maxDate(now()->subYears(18)),

                        Forms\Components\TextInput::make('municipality_id')
                            ->label('Municipio')
                            ->maxLength(255)
                            ->placeholder('ID del municipio para rankings locales'),

                        Forms\Components\DatePicker::make('join_date')
                            ->label('Fecha de Ingreso')
                            ->default(now()),

                        Forms\Components\Select::make('role_in_cooperative')
                            ->label('Rol en la Cooperativa')
                            ->options([
                                'miembro' => 'Miembro',
                                'voluntario' => 'Voluntario',
                                'promotor' => 'Promotor',
                                'coordinador' => 'Coordinador',
                            ])
                            ->placeholder('Seleccionar rol'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Configuraciones')
                    ->schema([
                        Forms\Components\Toggle::make('profile_completed')
                            ->label('Perfil Completado')
                            ->helperText('Se actualiza automáticamente'),

                        Forms\Components\Toggle::make('newsletter_opt_in')
                            ->label('Suscrito al Newsletter')
                            ->default(false),

                        Forms\Components\Toggle::make('show_in_rankings')
                            ->label('Mostrar en Rankings')
                            ->default(true)
                            ->helperText('Si aparece en la gamificación pública'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Estadísticas (Solo Lectura)')
                    ->schema([
                        Forms\Components\TextInput::make('co2_avoided_total')
                            ->label('CO₂ Evitado Total (kg)')
                            ->numeric()
                            ->step(0.01)
                            ->disabled(),

                        Forms\Components\TextInput::make('kwh_produced_total')
                            ->label('kWh Producidos Total')
                            ->numeric()
                            ->step(0.01)
                            ->disabled(),

                        Forms\Components\TextInput::make('points_total')
                            ->label('Puntos Total')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\TagsInput::make('badges_earned')
                            ->label('Badges Obtenidos')
                            ->disabled(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('Avatar')
                    ->circular()
                    ->defaultImageUrl('/images/default-avatar.png'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('organization.name')
                    ->label('Organización')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('role_in_cooperative')
                    ->label('Rol')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'coordinador' => 'success',
                        'promotor' => 'warning',
                        'voluntario' => 'info',
                        'miembro' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('profile_completed')
                    ->label('Completado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('points_total')
                    ->label('Puntos')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('kwh_produced_total')
                    ->label('kWh Producidos')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->suffix(' kWh'),

                Tables\Columns\IconColumn::make('show_in_rankings')
                    ->label('En Rankings')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('join_date')
                    ->label('Ingreso')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('organization_id')
                    ->label('Organización')
                    ->relationship('organization', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('role_in_cooperative')
                    ->label('Rol en Cooperativa')
                    ->options([
                        'miembro' => 'Miembro',
                        'voluntario' => 'Voluntario',
                        'promotor' => 'Promotor',
                        'coordinador' => 'Coordinador',
                    ]),

                Tables\Filters\TernaryFilter::make('profile_completed')
                    ->label('Perfil Completado'),

                Tables\Filters\TernaryFilter::make('show_in_rankings')
                    ->label('En Rankings'),

                Tables\Filters\TernaryFilter::make('newsletter_opt_in')
                    ->label('Suscrito Newsletter'),

                Tables\Filters\Filter::make('high_producers')
                    ->label('Altos Productores')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('kwh_produced_total', '>', 1000)
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('points_total', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserProfiles::route('/'),
            'create' => Pages\CreateUserProfile::route('/create'),
            'edit' => Pages\EditUserProfile::route('/{record}/edit'),
        ];
    }
}
