<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AchievementResource\Pages;
use App\Filament\Resources\AchievementResource\RelationManagers;
use App\Models\Achievement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AchievementResource extends Resource
{
    protected static ?string $model = Achievement::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Gamificación';

    protected static ?string $navigationLabel = 'Achievements';

    protected static ?string $modelLabel = 'Achievement';

    protected static ?string $pluralModelLabel = 'Achievements';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej: Primer kWh Generado'),

                        Forms\Components\Select::make('type')
                            ->label('Tipo')
                            ->options([
                                'energy' => 'Energía',
                                'participation' => 'Participación',
                                'community' => 'Comunidad',
                                'milestone' => 'Hito',
                            ])
                            ->required()
                            ->default('energy'),

                        Forms\Components\TextInput::make('icon')
                            ->label('Icono')
                            ->maxLength(255)
                            ->placeholder('Ej: ⚡, 🏆, 🌱')
                            ->helperText('Emoji o icono representativo'),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->maxLength(1000)
                            ->rows(3)
                            ->placeholder('Describe qué debe hacer el usuario para obtener este logro'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Configuración de Puntos y Criterios')
                    ->schema([
                        Forms\Components\TextInput::make('points_reward')
                            ->label('Puntos de Recompensa')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->step(1)
                            ->helperText('Puntos otorgados al usuario por obtener este logro'),

                        Forms\Components\KeyValue::make('criteria')
                            ->label('Criterios')
                            ->keyLabel('Tipo de Criterio')
                            ->valueLabel('Valor')
                            ->helperText('Define los criterios para desbloquear este logro (ej: kwh_produced: 1000)'),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Orden de Clasificación')
                            ->numeric()
                            ->default(0)
                            ->step(1)
                            ->helperText('Orden para mostrar en listas (menor número = mayor prioridad)'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true)
                            ->helperText('Solo los achievements activos pueden ser obtenidos por usuarios'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('icon')
                    ->label('Icono')
                    ->size('lg'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'energy' => 'success',
                        'participation' => 'info',
                        'community' => 'warning',
                        'milestone' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'energy' => 'Energía',
                        'participation' => 'Participación',
                        'community' => 'Comunidad',
                        'milestone' => 'Hito',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('points_reward')
                    ->label('Puntos')
                    ->numeric()
                    ->sortable()
                    ->suffix(' pts')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('total_unlocks')
                    ->label('Desbloqueados')
                    ->getStateUsing(fn (Achievement $record): int => $record->userAchievements()->count())
                    ->numeric()
                    ->sortable(false)
                    ->badge()
                    ->color('gray'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Orden')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'energy' => 'Energía',
                        'participation' => 'Participación',
                        'community' => 'Comunidad',
                        'milestone' => 'Hito',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activo'),

                Tables\Filters\Filter::make('high_points')
                    ->label('Alto Puntaje (>100 pts)')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('points_reward', '>', 100)
                    ),

                Tables\Filters\Filter::make('popular')
                    ->label('Populares (>10 usuarios)')
                    ->query(fn (Builder $query): Builder => 
                        $query->withCount('userAchievements')
                              ->having('user_achievements_count', '>', 10)
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('toggle_active')
                    ->label(fn (Achievement $record): string => $record->is_active ? 'Desactivar' : 'Activar')
                    ->icon(fn (Achievement $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Achievement $record): string => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(fn (Achievement $record) => $record->update(['is_active' => !$record->is_active])),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activar seleccionados')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(fn ($records) => 
                            Achievement::whereIn('id', $records->pluck('id'))
                                      ->update(['is_active' => true])
                        ),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Desactivar seleccionados')
                        ->icon('heroicon-o-eye-slash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => 
                            Achievement::whereIn('id', $records->pluck('id'))
                                      ->update(['is_active' => false])
                        ),
                ]),
            ])
            ->defaultSort('sort_order', 'asc');
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
            'index' => Pages\ListAchievements::route('/'),
            'create' => Pages\CreateAchievement::route('/create'),
            'edit' => Pages\EditAchievement::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::active()->count() ?: null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }
}
