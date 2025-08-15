<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChallengeResource\Pages;
use App\Filament\Resources\ChallengeResource\RelationManagers;
use App\Models\Challenge;
use App\Models\Organization;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChallengeResource extends Resource
{
    protected static ?string $model = Challenge::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    
    protected static ?string $navigationGroup = 'Gamificación';
    
    protected static ?string $navigationLabel = 'Desafíos';
    
    protected static ?string $modelLabel = 'Desafío';
    
    protected static ?string $pluralModelLabel = 'Desafíos';
    
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Desafío')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                            
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->columnSpanFull(),
                            
                        Forms\Components\Select::make('type')
                            ->label('Tipo')
                            ->options([
                                'individual' => 'Individual',
                                'team' => 'Equipo',
                                'organization' => 'Organización',
                            ])
                            ->required()
                            ->default('team'),
                            
                        Forms\Components\Select::make('organization_id')
                            ->label('Organización')
                            ->options(Organization::pluck('name', 'id'))
                            ->searchable()
                            ->nullable()
                            ->helperText('Dejar vacío para desafío global'),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Configuración del Desafío')
                    ->schema([
                        Forms\Components\TextInput::make('target_kwh')
                            ->label('Meta kWh')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->nullable()
                            ->helperText('Meta de energía a alcanzar'),
                            
                        Forms\Components\TextInput::make('points_reward')
                            ->label('Puntos de Recompensa')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                            
                        Forms\Components\TextInput::make('icon')
                            ->label('Icono')
                            ->maxLength(255)
                            ->nullable()
                            ->helperText('Nombre del icono (ej: solar-panel)'),
                            
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Fechas del Desafío')
                    ->schema([
                        Forms\Components\DateTimePicker::make('start_date')
                            ->label('Fecha de Inicio')
                            ->required()
                            ->default(now()),
                            
                        Forms\Components\DateTimePicker::make('end_date')
                            ->label('Fecha de Fin')
                            ->nullable()
                            ->after('start_date')
                            ->helperText('Dejar vacío para desafío sin fecha límite'),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Criterios Adicionales')
                    ->schema([
                        Forms\Components\KeyValue::make('criteria')
                            ->label('Criterios')
                            ->keyLabel('Clave')
                            ->valueLabel('Valor')
                            ->addActionLabel('Agregar criterio')
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'individual' => 'info',
                        'team' => 'success',
                        'organization' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'individual' => 'Individual',
                        'team' => 'Equipo',
                        'organization' => 'Organización',
                        default => $state,
                    }),
                    
                Tables\Columns\TextColumn::make('organization.name')
                    ->label('Organización')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Global'),
                    
                Tables\Columns\TextColumn::make('target_kwh')
                    ->label('Meta kWh')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->placeholder('Sin meta'),
                    
                Tables\Columns\TextColumn::make('points_reward')
                    ->label('Puntos')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success'),
                    
                Tables\Columns\TextColumn::make('participants_count')
                    ->label('Participantes')
                    ->getStateUsing(fn (Challenge $record): int => $record->teamProgress()->count())
                    ->badge()
                    ->color('info'),
                    
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Inicio')
                    ->date('d/m/Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('Sin límite'),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->alignCenter(),
                    
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->getStateUsing(fn (Challenge $record): string => 
                        $record->isCurrentlyActive() ? 'En curso' : 
                        ($record->hasEnded() ? 'Finalizado' : 'Próximo')
                    )
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'En curso' => 'success',
                        'Finalizado' => 'gray',
                        'Próximo' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'individual' => 'Individual',
                        'team' => 'Equipo',
                        'organization' => 'Organización',
                    ])
                    ->placeholder('Todos los tipos'),
                    
                Tables\Filters\SelectFilter::make('organization_id')
                    ->label('Organización')
                    ->options(Organization::pluck('name', 'id'))
                    ->placeholder('Todas las organizaciones'),
                    
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos'),
                    
                Tables\Filters\Filter::make('current')
                    ->label('En curso')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('start_date', '<=', now())
                              ->where(function ($q) {
                                  $q->whereNull('end_date')
                                    ->orWhere('end_date', '>=', now());
                              })
                    ),
                    
                Tables\Filters\Filter::make('upcoming')
                    ->label('Próximos')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('start_date', '>', now())
                    ),
                    
                Tables\Filters\Filter::make('ended')
                    ->label('Finalizados')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereNotNull('end_date')
                              ->where('end_date', '<', now())
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('view_leaderboard')
                    ->label('Ver Ranking')
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->url(fn (Challenge $record): string => route('filament.admin.resources.team-challenge-progresses.index', [
                        'tableFilters[challenge_id][value]' => $record->id,
                    ])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activar')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => true])),
                        
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Desactivar')
                        ->icon('heroicon-o-pause')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),
                ]),
            ])
            ->defaultSort('start_date', 'desc');
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
            'index' => Pages\ListChallenges::route('/'),
            'create' => Pages\CreateChallenge::route('/create'),
            'edit' => Pages\EditChallenge::route('/{record}/edit'),
        ];
    }
}
