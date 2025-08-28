<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CollaboratorResource\Pages;
use App\Models\Collaborator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;


class CollaboratorResource extends Resource
{
    protected static ?string $model = Collaborator::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    
    protected static ?string $navigationGroup = 'Comunicación';
    
    protected static ?string $navigationLabel = 'Colaboradores';
    
    protected static ?string $modelLabel = 'Colaborador';
    
    protected static ?string $pluralModelLabel = 'Colaboradores';
    
    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $model = static::getModel();
        $total = $model::count();
        
        if ($total === 0) {
            return 'gray';
        }
        
        $activeCount = $model::where('is_active', true)->count();
        $draftCount = $model::where('is_draft', true)->count();
        
        // Si hay más borradores que activos, mostrar warning
        if ($draftCount > $activeCount) {
            return 'warning';
        }
        
        // Si hay más inactivos que activos, mostrar danger
        $inactiveCount = $total - $activeCount;
        if ($inactiveCount > $activeCount) {
            return 'danger';
        }
        
        // Si todos están activos, mostrar success
        if ($activeCount === $total) {
            return 'success';
        }
        
        // Si la mayoría están activos, mostrar info
        return 'info';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Información Básica')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Section::make('Datos del Colaborador')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nombre')
                                            ->required()
                                            ->maxLength(255),
                                            
                                        Forms\Components\Textarea::make('description')
                                            ->label('Descripción')
                                            ->rows(3)
                                            ->maxLength(1000),
                                            
                                        Forms\Components\TextInput::make('url')
                                            ->label('Sitio Web')
                                            ->url()
                                            ->placeholder('https://ejemplo.com'),
                                    ])->columns(2),
                                    
                                Section::make('Logo del Colaborador')
                                    ->schema([
                                        Forms\Components\TextInput::make('logo')
                                            ->label('Ruta del Logo')
                                            ->helperText('Ruta del archivo de logo (ej: collaborators/logo.png)')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                            
                        Tabs\Tab::make('Configuración')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make('Posicionamiento')
                                    ->schema([
                                        Forms\Components\TextInput::make('order')
                                            ->label('Orden de Aparición')
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('Orden en que aparece (menor número = mayor prioridad)'),
                                            
                                        Forms\Components\Select::make('collaborator_type')
                                            ->label('Tipo de Colaborador')
                                            ->options([
                                                'partner' => 'Socio',
                                                'sponsor' => 'Patrocinador',
                                                'member' => 'Miembro',
                                                'supporter' => 'Proveedor',
                                            ])
                                            ->default('partner'),
                                    ])->columns(2),
                                    
                                Section::make('Estado')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_active')
                                            ->label('Activo')
                                            ->helperText('Solo los colaboradores activos se muestran públicamente')
                                            ->default(true),
                                            
                                        Forms\Components\Toggle::make('is_draft')
                                            ->label('Borrador')
                                            ->helperText('Los colaboradores en borrador no se muestran públicamente')
                                            ->default(false),
                                    ])->columns(2),
                                    
                                Section::make('Organización')
                                    ->schema([
                                        Forms\Components\Select::make('organization_id')
                                            ->label('Organización')
                                            ->relationship('organization', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Global (todas las organizaciones)'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('logo')
                    ->label('Logo')
                    ->formatStateUsing(fn ($state) => $state ? '📁 ' . basename($state) : '🖼️ Sin logo')
                    ->description(fn ($state) => $state ? 'Ruta: ' . $state : null)
                    ->wrap()
                    ->width(120),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium),
                    
                Tables\Columns\BadgeColumn::make('collaborator_type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'partner',
                        'success' => 'sponsor',
                        'warning' => 'member',
                        'danger' => 'supporter',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'partner' => 'Socio',
                        'sponsor' => 'Patrocinador',
                        'member' => 'Miembro',
                        'supporter' => 'Proveedor',
                        default => $state,
                    }),
                    
                Tables\Columns\TextColumn::make('order')
                    ->label('Orden')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('secondary'),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('gray'),
                    
                Tables\Columns\IconColumn::make('is_draft')
                    ->label('Borrador')
                    ->boolean()
                    ->trueIcon('heroicon-o-document')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('warning')
                    ->falseColor('success'),
                    
                Tables\Columns\TextColumn::make('url')
                    ->label('Web')
                    ->placeholder('Sin web')
                    ->url(fn ($record) => $record->url)
                    ->openUrlInNewTab()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('organization.name')
                    ->label('Organización')
                    ->placeholder('Global')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('collaborator_type')
                    ->label('Tipo')
                    ->options([
                        'partner' => 'Socio',
                        'sponsor' => 'Patrocinador',
                        'member' => 'Miembro',
                        'supporter' => 'Proveedor',
                    ]),
                    
                TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos'),
                    
                TernaryFilter::make('is_draft')
                    ->label('Borradores')
                    ->placeholder('Todos')
                    ->trueLabel('Solo borradores')
                    ->falseLabel('Solo publicados'),
                    
                SelectFilter::make('organization_id')
                    ->label('Organización')
                    ->relationship('organization', 'name')
                    ->placeholder('Todas las organizaciones'),
            ])
            ->actions([
                Tables\Actions\Action::make('visit_website')
                    ->label('Visitar Web')
                    ->icon('heroicon-o-globe-alt')
                    ->url(fn ($record) => $record->url)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => !empty($record->url)),
                    
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('toggle_active')
                    ->label(fn ($record) => $record->is_active ? 'Desactivar' : 'Activar')
                    ->icon(fn ($record) => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn ($record) => $record->is_active ? 'danger' : 'success')
                    ->action(fn ($record) => $record->update(['is_active' => !$record->is_active])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activar seleccionados')
                        ->icon('heroicon-o-eye')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Desactivar seleccionados')
                        ->icon('heroicon-o-eye-slash')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('order', 'asc')
            ->reorderable('order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCollaborators::route('/'),
            'create' => Pages\CreateCollaborator::route('/create'),
            'edit' => Pages\EditCollaborator::route('/{record}/edit'),
        ];
    }
}