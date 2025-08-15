<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamMembershipResource\Pages;
use App\Filament\Resources\TeamMembershipResource\RelationManagers;
use App\Models\Team;
use App\Models\TeamMembership;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TeamMembershipResource extends Resource
{
    protected static ?string $model = TeamMembership::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    
    protected static ?string $navigationGroup = 'Gamificación';
    
    protected static ?string $navigationLabel = 'Membresías de Equipos';
    
    protected static ?string $modelLabel = 'Membresía';
    
    protected static ?string $pluralModelLabel = 'Membresías';
    
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de Membresía')
                    ->schema([
                        Forms\Components\Select::make('team_id')
                            ->label('Equipo')
                            ->options(Team::pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('user_id', null)),
                            
                        Forms\Components\Select::make('user_id')
                            ->label('Usuario')
                            ->options(function (callable $get) {
                                $teamId = $get('team_id');
                                if (!$teamId) {
                                    return User::pluck('name', 'id');
                                }
                                
                                // Excluir usuarios que ya son miembros activos del equipo
                                $existingMemberIds = TeamMembership::where('team_id', $teamId)
                                    ->whereNull('left_at')
                                    ->pluck('user_id');
                                    
                                return User::whereNotIn('id', $existingMemberIds)
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->required(),
                            
                        Forms\Components\Select::make('role')
                            ->label('Rol')
                            ->options([
                                'admin' => 'Administrador',
                                'moderator' => 'Moderador',
                                'member' => 'Miembro',
                            ])
                            ->default('member')
                            ->required(),
                            
                        Forms\Components\DateTimePicker::make('joined_at')
                            ->label('Fecha de Ingreso')
                            ->default(now())
                            ->required(),
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
                    
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('role')
                    ->label('Rol')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'moderator' => 'warning',
                        'member' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'Administrador',
                        'moderator' => 'Moderador',
                        'member' => 'Miembro',
                        default => $state,
                    }),
                    
                Tables\Columns\TextColumn::make('joined_at')
                    ->label('Fecha de Ingreso')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('left_at')
                    ->label('Fecha de Salida')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Activo')
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Estado')
                    ->getStateUsing(fn (TeamMembership $record): bool => $record->isActive())
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('team_id')
                    ->label('Equipo')
                    ->options(Team::pluck('name', 'id'))
                    ->placeholder('Todos los equipos'),
                    
                Tables\Filters\SelectFilter::make('role')
                    ->label('Rol')
                    ->options([
                        'admin' => 'Administrador',
                        'moderator' => 'Moderador',
                        'member' => 'Miembro',
                    ])
                    ->placeholder('Todos los roles'),
                    
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNull('left_at'),
                        false: fn (Builder $query) => $query->whereNotNull('left_at'),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('leave_team')
                    ->label('Marcar como Salido')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('warning')
                    ->visible(fn (TeamMembership $record): bool => $record->isActive())
                    ->requiresConfirmation()
                    ->action(fn (TeamMembership $record) => $record->leave()),
                    
                Tables\Actions\Action::make('rejoin_team')
                    ->label('Reactivar Membresía')
                    ->icon('heroicon-o-arrow-left-on-rectangle')
                    ->color('success')
                    ->visible(fn (TeamMembership $record): bool => !$record->isActive())
                    ->requiresConfirmation()
                    ->action(fn (TeamMembership $record) => $record->rejoin()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('bulk_leave')
                        ->label('Marcar como Salidos')
                        ->icon('heroicon-o-arrow-right-on-rectangle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if ($record->isActive()) {
                                    $record->leave();
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('joined_at', 'desc');
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
            'index' => Pages\ListTeamMemberships::route('/'),
            'create' => Pages\CreateTeamMembership::route('/create'),
            'edit' => Pages\EditTeamMembership::route('/{record}/edit'),
        ];
    }
}
