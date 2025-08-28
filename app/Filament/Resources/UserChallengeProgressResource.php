<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserChallengeProgressResource\Pages;
use App\Filament\Resources\UserChallengeProgressResource\RelationManagers;
use App\Models\UserChallengeProgress;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class UserChallengeProgressResource extends Resource
{
    protected static ?string $model = UserChallengeProgress::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    
    protected static ?string $navigationGroup = 'Gamificación';
    
    protected static ?string $modelLabel = 'Progreso de Usuario';
    
    protected static ?string $pluralModelLabel = 'Progresos de Usuarios';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Progreso')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Usuario')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->placeholder('Seleccionar usuario'),
                        
                        Forms\Components\Select::make('challenge_id')
                            ->label('Desafío')
                            ->relationship('challenge', 'title')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->placeholder('Seleccionar desafío'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Progreso')
                    ->schema([
                        Forms\Components\TextInput::make('progress_kwh')
                            ->label('Progreso (kWh)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->suffix('kWh')
                            ->placeholder('0.00')
                            ->helperText('Cantidad de kWh acumulados en este desafío'),
                        
                        Forms\Components\DateTimePicker::make('completed_at')
                            ->label('Fecha de Completado')
                            ->nullable()
                            ->helperText('Fecha cuando se completó el desafío (se llena automáticamente)'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Información Adicional')
                    ->schema([
                        Forms\Components\Placeholder::make('progress_percentage')
                            ->label('Porcentaje de Progreso')
                            ->content(function ($record) {
                                if (!$record || !$record->challenge) return 'N/A';
                                return number_format($record->progress_percentage, 1) . '%';
                            }),
                        
                        Forms\Components\Placeholder::make('remaining_kwh')
                            ->label('kWh Restantes')
                            ->content(function ($record) {
                                if (!$record || !$record->challenge) return 'N/A';
                                return number_format($record->remaining_kwh, 2) . ' kWh';
                            }),
                        
                        Forms\Components\Placeholder::make('days_remaining')
                            ->label('Días Restantes')
                            ->content(function ($record) {
                                if (!$record || !$record->challenge) return 'N/A';
                                return $record->days_remaining . ' días';
                            }),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                
                Tables\Columns\TextColumn::make('challenge.title')
                    ->label('Desafío')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                
                Tables\Columns\TextColumn::make('challenge.type')
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
                
                Tables\Columns\TextColumn::make('progress_kwh')
                    ->label('Progreso')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: ',',
                        thousandsSeparator: '.',
                    )
                    ->suffix(' kWh')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('progress_percentage')
                    ->label('% Progreso')
                    ->numeric(
                        decimalPlaces: 1,
                        decimalSeparator: ',',
                        thousandsSeparator: '.',
                    )
                    ->suffix('%')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('remaining_kwh')
                    ->label('Restante')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: ',',
                        thousandsSeparator: '.',
                    )
                    ->suffix(' kWh')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('days_remaining')
                    ->label('Días Rest.')
                    ->numeric()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('En progreso'),
                
                Tables\Columns\IconColumn::make('is_completed')
                    ->label('Estado')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->isCompleted())
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('challenge.type')
                    ->label('Tipo de Desafío')
                    ->options([
                        'individual' => 'Individual',
                        'colectivo' => 'Colectivo',
                    ]),
                
                Tables\Filters\TernaryFilter::make('completed_at')
                    ->label('Completado')
                    ->placeholder('Todos')
                    ->trueLabel('Solo completados')
                    ->falseLabel('Solo en progreso'),
                
                Tables\Filters\Filter::make('progress_range')
                    ->label('Rango de Progreso')
                    ->form([
                        Forms\Components\TextInput::make('min_progress')
                            ->label('Progreso mínimo (kWh)')
                            ->numeric()
                            ->placeholder('0'),
                        Forms\Components\TextInput::make('max_progress')
                            ->label('Progreso máximo (kWh)')
                            ->numeric()
                            ->placeholder('100'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['min_progress'], fn (Builder $query, $min) => $query->where('progress_kwh', '>=', $min))
                            ->when($data['max_progress'], fn (Builder $query, $max) => $query->where('progress_kwh', '<=', $max));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('update_progress')
                    ->label('Actualizar Progreso')
                    ->icon('heroicon-o-plus-circle')
                    ->form([
                        Forms\Components\TextInput::make('additional_kwh')
                            ->label('kWh adicionales')
                            ->required()
                            ->numeric()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->suffix('kWh')
                            ->placeholder('10.50'),
                    ])
                    ->action(function (UserChallengeProgress $record, array $data) {
                        $record->updateProgress($data['additional_kwh']);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Actualizar Progreso')
                    ->modalDescription('Agregar kWh al progreso actual del usuario en este desafío.')
                    ->successNotificationTitle('Progreso actualizado correctamente'),
                
                Tables\Actions\Action::make('complete')
                    ->label('Completar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (UserChallengeProgress $record) {
                        $record->complete();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Completar Desafío')
                    ->modalDescription('¿Estás seguro de que quieres marcar este desafío como completado?')
                    ->successNotificationTitle('Desafío completado correctamente')
                    ->visible(fn ($record) => !$record->isCompleted()),
                
                Tables\Actions\Action::make('reset')
                    ->label('Reiniciar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (UserChallengeProgress $record) {
                        $record->reset();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Reiniciar Progreso')
                    ->modalDescription('¿Estás seguro de que quieres reiniciar el progreso a 0?')
                    ->successNotificationTitle('Progreso reiniciado correctamente'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('complete_all')
                        ->label('Completar Seleccionados')
                        ->icon('heroicon-o-check-circle')
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                if (!$record->isCompleted()) {
                                    $record->complete();
                                }
                            });
                        }),
                    Tables\Actions\BulkAction::make('reset_all')
                        ->label('Reiniciar Seleccionados')
                        ->icon('heroicon-o-arrow-path')
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->reset();
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
            RelationManagers\UserRelationManager::class,
            RelationManagers\ChallengeRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserChallengeProgress::route('/'),
            'create' => Pages\CreateUserChallengeProgress::route('/create'),
            'edit' => Pages\EditUserChallengeProgress::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $completedCount = static::getModel()::whereNotNull('completed_at')->count();
        $totalCount = static::getModel()::count();
        
        if ($totalCount === 0) {
            return 'gray';         // ⚫ Gris cuando no hay progresos
        }
        
        $completionRate = $completedCount / $totalCount;
        
        if ($completionRate >= 0.8) {
            return 'success';      // 🟢 Verde cuando el 80%+ está completado
        }
        
        if ($completionRate >= 0.5) {
            return 'info';         // 🔵 Azul cuando el 50%+ está completado
        }
        
        if ($completionRate >= 0.2) {
            return 'warning';      // 🟡 Naranja cuando el 20%+ está completado
        }
        
        return 'danger';           // 🔴 Rojo cuando menos del 20% está completado
    }
}
