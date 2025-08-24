<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnergyChallengeResource\Pages;
use App\Filament\Resources\EnergyChallengeResource\RelationManagers;
use App\Models\EnergyChallenge;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class EnergyChallengeResource extends Resource
{
    protected static ?string $model = EnergyChallenge::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';
    
    protected static ?string $navigationGroup = 'Gamificación';
    
    protected static ?string $modelLabel = 'Desafío Energético';
    
    protected static ?string $pluralModelLabel = 'Desafíos Energéticos';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej: Ahorro Energético Mensual'),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->required()
                            ->rows(3)
                            ->placeholder('Describe el desafío y sus objetivos'),
                        
                        Forms\Components\Select::make('type')
                            ->label('Tipo')
                            ->required()
                            ->options([
                                'individual' => 'Individual',
                                'colectivo' => 'Colectivo',
                            ])
                            ->default('individual'),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true)
                            ->helperText('Determina si el desafío está disponible para los usuarios'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Objetivos y Fechas')
                    ->schema([
                        Forms\Components\TextInput::make('goal_kwh')
                            ->label('Meta (kWh)')
                            ->required()
                            ->numeric()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->suffix('kWh')
                            ->placeholder('100.50'),
                        
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Fecha de Inicio')
                            ->required()
                            ->default(now())
                            ->helperText('Cuándo comienza el desafío'),
                        
                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('Fecha de Fin')
                            ->required()
                            ->default(now()->addDays(30))
                            ->helperText('Cuándo termina el desafío'),
                    ])->columns(3),
                
                Forms\Components\Section::make('Recompensas')
                    ->schema([
                        Forms\Components\Select::make('reward_type')
                            ->label('Tipo de Recompensa')
                            ->required()
                            ->options([
                                'symbolic' => 'Simbólica',
                                'energy_donation' => 'Donación Energética',
                                'badge' => 'Insignia',
                            ])
                            ->default('symbolic'),
                        
                        Forms\Components\KeyValue::make('reward_details')
                            ->label('Detalles de Recompensa')
                            ->keyLabel('Campo')
                            ->valueLabel('Valor')
                            ->addActionLabel('Agregar Campo')
                            ->helperText('Configuración específica de la recompensa (puntos, descripción, etc.)'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'individual' => 'info',
                        'colectivo' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'individual' => 'Individual',
                        'colectivo' => 'Colectivo',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('goal_kwh')
                    ->label('Meta')
                    ->numeric()
                    ->suffix(' kWh')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Inicio')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Fin')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('reward_type')
                    ->label('Recompensa')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'symbolic' => 'gray',
                        'energy_donation' => 'warning',
                        'badge' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'symbolic' => 'Simbólica',
                        'energy_donation' => 'Donación',
                        'badge' => 'Insignia',
                        default => $state,
                    }),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'upcoming' => 'info',
                        'draft' => 'gray',
                        'active' => 'success',
                        'completed' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'upcoming' => 'Próximo',
                        'draft' => 'Borrador',
                        'active' => 'Activo',
                        'completed' => 'Completado',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('total_participants')
                    ->label('Participantes')
                    ->numeric()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('progress_percentage')
                    ->label('Progreso')
                    ->numeric(
                        decimalPlaces: 1,
                        decimalSeparator: ',',
                        thousandsSeparator: '.',
                    )
                    ->suffix('%')
                    ->sortable(),
                
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
                        'individual' => 'Individual',
                        'colectivo' => 'Colectivo',
                    ]),
                
                Tables\Filters\SelectFilter::make('reward_type')
                    ->label('Tipo de Recompensa')
                    ->options([
                        'symbolic' => 'Simbólica',
                        'energy_donation' => 'Donación Energética',
                        'badge' => 'Insignia',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activo'),
                
                Tables\Filters\Filter::make('status')
                    ->label('Estado')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'upcoming' => 'Próximo',
                                'draft' => 'Borrador',
                                'active' => 'Activo',
                                'completed' => 'Completado',
                            ])
                            ->multiple()
                            ->placeholder('Seleccionar estados'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['status'], function (Builder $query, array $statuses): Builder {
                                return $query->where(function (Builder $query) use ($statuses): Builder {
                                    foreach ($statuses as $status) {
                                        $query->orWhere(function (Builder $query) use ($status): Builder {
                                            $now = now();
                                            switch ($status) {
                                                case 'upcoming':
                                                    return $query->where('starts_at', '>', $now);
                                                case 'draft':
                                                    return $query->where('starts_at', '>', $now);
                                                case 'active':
                                                    return $query->where('starts_at', '<=', $now)
                                                               ->where('ends_at', '>=', $now);
                                                case 'completed':
                                                    return $query->where('ends_at', '<', $now);
                                                default:
                                                    return $query;
                                            }
                                        });
                                    }
                                    return $query;
                                });
                            });
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function (EnergyChallenge $record) {
                        $newChallenge = $record->replicate();
                        $newChallenge->title = $record->title . ' (Copia)';
                        $newChallenge->starts_at = now()->addDays(7);
                        $newChallenge->ends_at = now()->addDays(37);
                        $newChallenge->is_active = false;
                        $newChallenge->save();
                        
                        return redirect()->route('filament.admin.resources.energy-challenges.edit', $newChallenge);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activar')
                        ->icon('heroicon-o-play')
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->update(['is_active' => true]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Desactivar')
                        ->icon('heroicon-o-pause')
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->update(['is_active' => false]);
                            });
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UserProgressRelationManager::class,
            RelationManagers\ParticipantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEnergyChallenges::route('/'),
            'create' => Pages\CreateEnergyChallenge::route('/create'),
            'edit' => Pages\EditEnergyChallenge::route('/{record}/edit'),
        ];
    }
}
