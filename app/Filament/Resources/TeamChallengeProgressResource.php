<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamChallengeProgressResource\Pages;
use App\Filament\Resources\TeamChallengeProgressResource\RelationManagers;
use App\Models\Challenge;
use App\Models\Team;
use App\Models\TeamChallengeProgress;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TeamChallengeProgressResource extends Resource
{
    protected static ?string $model = TeamChallengeProgress::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    
    protected static ?string $navigationGroup = 'Gamificación';
    
    protected static ?string $navigationLabel = 'Progreso de Desafíos';
    
    protected static ?string $modelLabel = 'Progreso';
    
    protected static ?string $pluralModelLabel = 'Progreso de Desafíos';
    
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Progreso')
                    ->schema([
                        Forms\Components\Select::make('team_id')
                            ->label('Equipo')
                            ->options(Team::pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->reactive(),
                            
                        Forms\Components\Select::make('challenge_id')
                            ->label('Desafío')
                            ->options(Challenge::pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->reactive(),
                            
                        Forms\Components\TextInput::make('progress_kwh')
                            ->label('Progreso kWh')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                            
                        Forms\Components\DateTimePicker::make('completed_at')
                            ->label('Fecha de Finalización')
                            ->nullable()
                            ->helperText('Se establece automáticamente cuando se alcanza la meta'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('team.logo_path')
                    ->label('Logo')
                    ->circular()
                    ->defaultImageUrl(fn (): string => 'https://ui-avatars.com/api/?name=Team&color=7F9CF5&background=EBF4FF')
                    ->size(32),
                    
                Tables\Columns\TextColumn::make('team.name')
                    ->label('Equipo')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                Tables\Columns\TextColumn::make('challenge.name')
                    ->label('Desafío')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                Tables\Columns\TextColumn::make('challenge.type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'individual' => 'info',
                        'team' => 'success',
                        'organization' => 'warning',
                        default => 'gray',
                    }),
                    
                Tables\Columns\TextColumn::make('progress_kwh')
                    ->label('Progreso kWh')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->color('success'),
                    
                Tables\Columns\TextColumn::make('challenge.target_kwh')
                    ->label('Meta kWh')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->placeholder('Sin meta'),
                    
                Tables\Columns\TextColumn::make('progress_percentage')
                    ->label('Porcentaje')
                    ->getStateUsing(function (TeamChallengeProgress $record): string {
                        if (!$record->challenge->target_kwh) {
                            return 'N/A';
                        }
                        $percentage = ($record->progress_kwh / $record->challenge->target_kwh) * 100;
                        return number_format(min($percentage, 100), 1) . '%';
                    })
                    ->badge()
                    ->color(function (TeamChallengeProgress $record): string {
                        if (!$record->challenge->target_kwh) {
                            return 'gray';
                        }
                        $percentage = ($record->progress_kwh / $record->challenge->target_kwh) * 100;
                        return match (true) {
                            $percentage >= 100 => 'success',
                            $percentage >= 75 => 'warning',
                            $percentage >= 50 => 'info',
                            default => 'danger',
                        };
                    }),
                    
                Tables\Columns\TextColumn::make('team_rank')
                    ->label('Ranking')
                    ->getStateUsing(function (TeamChallengeProgress $record): string {
                        $rank = TeamChallengeProgress::where('challenge_id', $record->challenge_id)
                            ->where('progress_kwh', '>', $record->progress_kwh)
                            ->count() + 1;
                        return "#{$rank}";
                    })
                    ->badge()
                    ->color('info'),
                    
                Tables\Columns\IconColumn::make('is_completed')
                    ->label('Completado')
                    ->getStateUsing(fn (TeamChallengeProgress $record): bool => $record->isCompleted())
                    ->boolean()
                    ->trueIcon('heroicon-o-trophy')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),
                    
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completado el')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('En progreso'),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Actualización')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('team_id')
                    ->label('Equipo')
                    ->options(Team::pluck('name', 'id'))
                    ->placeholder('Todos los equipos'),
                    
                Tables\Filters\SelectFilter::make('challenge_id')
                    ->label('Desafío')
                    ->options(Challenge::pluck('name', 'id'))
                    ->placeholder('Todos los desafíos'),
                    
                Tables\Filters\TernaryFilter::make('completed')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Solo completados')
                    ->falseLabel('Solo en progreso')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('completed_at'),
                        false: fn (Builder $query) => $query->whereNull('completed_at'),
                        blank: fn (Builder $query) => $query,
                    ),
                    
                Tables\Filters\Filter::make('high_progress')
                    ->label('Alto progreso (>75%)')
                    ->query(function (Builder $query): Builder {
                        return $query->whereHas('challenge', function ($q) {
                            $q->whereNotNull('target_kwh');
                        })->whereRaw('progress_kwh >= (SELECT target_kwh * 0.75 FROM challenges WHERE challenges.id = team_challenge_progress.challenge_id)');
                    }),
                    
                Tables\Filters\Filter::make('near_completion')
                    ->label('Cerca de completar (>90%)')
                    ->query(function (Builder $query): Builder {
                        return $query->whereHas('challenge', function ($q) {
                            $q->whereNotNull('target_kwh');
                        })->whereRaw('progress_kwh >= (SELECT target_kwh * 0.9 FROM challenges WHERE challenges.id = team_challenge_progress.challenge_id)')
                        ->whereNull('completed_at');
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('update_progress')
                    ->label('Actualizar Progreso')
                    ->icon('heroicon-o-arrow-trending-up')
                    ->color('info')
                    ->form([
                        Forms\Components\TextInput::make('additional_kwh')
                            ->label('kWh Adicionales')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->required(),
                    ])
                    ->action(function (TeamChallengeProgress $record, array $data): void {
                        $newProgress = $record->progress_kwh + $data['additional_kwh'];
                        $record->updateProgress($newProgress);
                    }),
                    
                Tables\Actions\Action::make('mark_completed')
                    ->label('Marcar como Completado')
                    ->icon('heroicon-o-trophy')
                    ->color('success')
                    ->visible(fn (TeamChallengeProgress $record): bool => !$record->isCompleted())
                    ->requiresConfirmation()
                    ->action(fn (TeamChallengeProgress $record) => $record->update(['completed_at' => now()])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_completed')
                        ->label('Marcar como Completados')
                        ->icon('heroicon-o-trophy')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if (!$record->isCompleted()) {
                                    $record->update(['completed_at' => now()]);
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('updated_at', 'desc');
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
            'index' => Pages\ListTeamChallengeProgress::route('/'),
            'create' => Pages\CreateTeamChallengeProgress::route('/create'),
            'edit' => Pages\EditTeamChallengeProgress::route('/{record}/edit'),
        ];
    }
}
