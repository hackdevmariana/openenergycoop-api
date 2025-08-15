<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamResource\Pages;
use App\Filament\Resources\TeamResource\RelationManagers;
use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class TeamResource extends Resource
{
    protected static ?string $model = Team::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationGroup = 'Gamificación';
    
    protected static ?string $navigationLabel = 'Equipos';
    
    protected static ?string $modelLabel = 'Equipo';
    
    protected static ?string $pluralModelLabel = 'Equipos';
    
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Equipo')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $context, $state, Forms\Set $set) => 
                                $context === 'create' ? $set('slug', Str::slug($state)) : null
                            ),
                            
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->rules(['regex:/^[a-z0-9-]+$/']),
                            
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->columnSpanFull()
                            ->rows(3),
                            
                        Forms\Components\Select::make('created_by_user_id')
                            ->label('Creado por')
                            ->options(User::pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->default(auth()->id()),
                            
                        Forms\Components\Select::make('organization_id')
                            ->label('Organización')
                            ->options(Organization::pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Configuración del Equipo')
                    ->schema([
                        Forms\Components\Toggle::make('is_open')
                            ->label('Equipo Abierto')
                            ->helperText('Si está activado, cualquier usuario puede unirse al equipo')
                            ->default(false),
                            
                        Forms\Components\TextInput::make('max_members')
                            ->label('Máximo de Miembros')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(1000)
                            ->nullable()
                            ->helperText('Dejar vacío para sin límite'),
                            
                        Forms\Components\FileUpload::make('logo_path')
                            ->label('Logo del Equipo')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '1:1',
                            ])
                            ->maxSize(2048)
                            ->nullable(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo_path')
                    ->label('Logo')
                    ->circular()
                    ->defaultImageUrl(fn (): string => 'https://ui-avatars.com/api/?name=Team&color=7F9CF5&background=EBF4FF')
                    ->size(40),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->copyable()
                    ->size('sm')
                    ->color('gray'),
                    
                Tables\Columns\TextColumn::make('organization.name')
                    ->label('Organización')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Global'),
                    
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Creado por')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('members_count')
                    ->label('Miembros')
                    ->getStateUsing(fn (Team $record): int => $record->activeMemberships()->count())
                    ->badge()
                    ->color('success'),
                    
                Tables\Columns\TextColumn::make('max_members')
                    ->label('Límite')
                    ->placeholder('Sin límite')
                    ->alignCenter(),
                    
                Tables\Columns\IconColumn::make('is_open')
                    ->label('Abierto')
                    ->boolean()
                    ->alignCenter(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('organization_id')
                    ->label('Organización')
                    ->options(Organization::pluck('name', 'id'))
                    ->placeholder('Todas las organizaciones'),
                    
                Tables\Filters\TernaryFilter::make('is_open')
                    ->label('Equipo Abierto')
                    ->placeholder('Todos')
                    ->trueLabel('Solo abiertos')
                    ->falseLabel('Solo cerrados'),
                    
                Tables\Filters\Filter::make('has_space')
                    ->label('Con espacio disponible')
                    ->query(fn (Builder $query): Builder => 
                        $query->where(function ($q) {
                            $q->whereNull('max_members')
                              ->orWhereRaw('max_members > (SELECT COUNT(*) FROM team_memberships WHERE team_id = teams.id AND left_at IS NULL)');
                        })
                    ),
                    
                Tables\Filters\Filter::make('full_teams')
                    ->label('Equipos llenos')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereNotNull('max_members')
                              ->whereRaw('max_members <= (SELECT COUNT(*) FROM team_memberships WHERE team_id = teams.id AND left_at IS NULL)')
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('view_members')
                    ->label('Ver Miembros')
                    ->icon('heroicon-o-users')
                    ->color('info')
                    ->url(fn (Team $record): string => route('filament.admin.resources.team-memberships.index', [
                        'tableFilters[team_id][value]' => $record->id,
                    ])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListTeams::route('/'),
            'create' => Pages\CreateTeam::route('/create'),
            'edit' => Pages\EditTeam::route('/{record}/edit'),
        ];
    }
}
