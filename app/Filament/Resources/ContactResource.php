<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactResource\Pages;
use App\Models\Contact;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Filters\SelectFilter;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static ?string $navigationIcon = 'heroicon-o-phone';
    
    protected static ?string $navigationGroup = 'Comunicación';
    
    protected static ?string $navigationLabel = 'Contactos';
    
    protected static ?string $modelLabel = 'Contacto';
    
    protected static ?string $pluralModelLabel = 'Contactos';
    
    protected static ?int $navigationSort = 2;

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
        
        $publishedCount = $model::where('is_draft', false)->count();
        $primaryCount = $model::where('is_primary', true)->count();
        $withLocationCount = $model::whereNotNull('latitude')->whereNotNull('longitude')->count();
        
        // Si hay contactos de emergencia, mostrar danger
        $emergencyCount = $model::where('contact_type', 'emergency')->count();
        if ($emergencyCount > 0) {
            return 'danger';
        }
        
        // Si hay más borradores que publicados, mostrar warning
        $draftCount = $total - $publishedCount;
        if ($draftCount > $publishedCount) {
            return 'warning';
        }
        
        // Si todos están publicados y hay contactos principales, mostrar success
        if ($publishedCount === $total && $primaryCount > 0) {
            return 'success';
        }
        
        // Si la mayoría están publicados, mostrar info
        if ($publishedCount >= ($total * 0.7)) {
            return 'info';
        }
        
        // Si hay muchos sin ubicación, mostrar warning
        if ($withLocationCount < ($total * 0.5)) {
            return 'warning';
        }
        
        return 'info';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Información de Contacto')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Section::make('Datos Básicos')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nombre')
                                            ->maxLength(255),
                                            
                                        Forms\Components\Select::make('contact_type')
                                            ->label('Tipo de Contacto')
                                            ->options([
                                                'main' => 'Principal',
                                                'support' => 'Soporte',
                                                'sales' => 'Ventas',
                                                'media' => 'Medios',
                                                'technical' => 'Técnico',
                                                'billing' => 'Facturación',
                                                'emergency' => 'Emergencia',
                                            ])
                                            ->default('main'),
                                    ])->columns(2),
                                    
                                Section::make('Información de Contacto')
                                    ->schema([
                                        Forms\Components\TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->required(),
                                            
                                        Forms\Components\TextInput::make('phone')
                                            ->label('Teléfono')
                                            ->tel(),
                                    ])->columns(2),
                            ]),
                            
                        Tabs\Tab::make('Dirección y Ubicación')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Section::make('Dirección Física')
                                    ->schema([
                                        Forms\Components\Textarea::make('address')
                                            ->label('Dirección')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ]),
                                    
                                Section::make('Coordenadas GPS')
                                    ->schema([
                                        Forms\Components\TextInput::make('latitude')
                                            ->label('Latitud')
                                            ->numeric()
                                            ->step(0.00000001)
                                            ->helperText('Coordenada de latitud (ej: 40.4168)'),
                                            
                                        Forms\Components\TextInput::make('longitude')
                                            ->label('Longitud')
                                            ->numeric()
                                            ->step(0.00000001)
                                            ->helperText('Coordenada de longitud (ej: -3.7038)'),
                                    ])->columns(2),
                            ]),
                            
                        Tabs\Tab::make('Información Adicional')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Section::make('Horarios de Negocio')
                                    ->schema([
                                        Forms\Components\KeyValue::make('business_hours')
                                            ->label('Horarios de Negocio')
                                            ->keyLabel('Día')
                                            ->valueLabel('Horario')
                                            ->helperText('Ej: Lunes: 9:00-17:00, Martes: 9:00-17:00')
                                            ->columnSpanFull(),
                                    ]),
                                    
                                Section::make('Información Adicional')
                                    ->schema([
                                        Forms\Components\Textarea::make('additional_info')
                                            ->label('Información Adicional')
                                            ->rows(4)
                                            ->helperText('Información adicional sobre este contacto')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                            
                        Tabs\Tab::make('Configuración')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make('Estado')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_draft')
                                            ->label('Borrador')
                                            ->helperText('Si está activado, este contacto está en borrador')
                                            ->default(false),
                                            
                                        Forms\Components\Toggle::make('is_primary')
                                            ->label('Contacto Principal')
                                            ->helperText('Contacto principal de la organización')
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
                                    
                                Section::make('Usuario Creador')
                                    ->schema([
                                        Forms\Components\Select::make('created_by_user_id')
                                            ->label('Creado por')
                                            ->relationship('createdBy', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Usuario del sistema'),
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium),
                    
                Tables\Columns\BadgeColumn::make('contact_type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'main',
                        'success' => 'support',
                        'warning' => 'sales',
                        'danger' => 'emergency',
                        'info' => 'technical',
                        'gray' => 'billing',
                        'slate' => 'media',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'main' => 'Principal',
                        'support' => 'Soporte',
                        'sales' => 'Ventas',
                        'emergency' => 'Emergencia',
                        'technical' => 'Técnico',
                        'billing' => 'Facturación',
                        'media' => 'Medios',
                        default => $state,
                    }),
                    
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->color('primary'),
                    
                Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono')
                    ->copyable()
                    ->placeholder('Sin teléfono'),
                    
                Tables\Columns\TextColumn::make('address')
                    ->label('Dirección')
                    ->searchable()
                    ->placeholder('Sin dirección')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\IconColumn::make('has_location')
                    ->label('Ubicación GPS')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !is_null($record->latitude) && !is_null($record->longitude))
                    ->trueIcon('heroicon-o-map-pin')
                    ->falseIcon('heroicon-o-map-pin')
                    ->trueColor('success')
                    ->falseColor('gray'),
                    
                Tables\Columns\IconColumn::make('is_draft')
                    ->label('Borrador')
                    ->boolean()
                    ->trueIcon('heroicon-o-document')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('warning')
                    ->falseColor('success'),
                    
                Tables\Columns\IconColumn::make('is_primary')
                    ->label('Principal')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                    
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
                SelectFilter::make('contact_type')
                    ->label('Tipo de Contacto')
                    ->options([
                        'main' => 'Principal',
                        'support' => 'Soporte',
                        'sales' => 'Ventas',
                        'emergency' => 'Emergencia',
                        'technical' => 'Técnico',
                        'billing' => 'Facturación',
                        'media' => 'Medios',
                    ]),
                    
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'published' => 'Publicado',
                    ]),
                    
                TernaryFilter::make('is_primary')
                    ->label('Contacto Principal')
                    ->placeholder('Todos')
                    ->trueLabel('Solo principales')
                    ->falseLabel('No principales'),
                    
                SelectFilter::make('organization_id')
                    ->label('Organización')
                    ->relationship('organization', 'name')
                    ->placeholder('Todas las organizaciones'),
            ])
            ->actions([
                Tables\Actions\Action::make('send_email')
                    ->label('Enviar Email')
                    ->icon('heroicon-o-envelope')
                    ->url(fn ($record) => "mailto:{$record->email}")
                    ->color('primary'),
                    
                Tables\Actions\Action::make('call')
                    ->label('Llamar')
                    ->icon('heroicon-o-phone')
                    ->url(fn ($record) => "tel:{$record->phone}")
                    ->visible(fn ($record) => !empty($record->phone))
                    ->color('success'),
                    
                Tables\Actions\Action::make('view_location')
                    ->label('Ver Ubicación')
                    ->icon('heroicon-o-map-pin')
                    ->url(fn ($record) => "https://maps.google.com/?q={$record->latitude},{$record->longitude}")
                    ->visible(fn ($record) => !is_null($record->latitude) && !is_null($record->longitude))
                    ->openUrlInNewTab()
                    ->color('info'),
                    
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publicar')
                        ->icon('heroicon-o-eye')
                        ->action(fn ($records) => $records->each->update(['is_draft' => false]))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('make_draft')
                        ->label('Marcar como borrador')
                        ->icon('heroicon-o-document')
                        ->action(fn ($records) => $records->each->update(['is_draft' => true]))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('export_contacts')
                        ->label('Exportar Contactos')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function ($records) {
                            // Aquí implementarías la exportación
                            \Filament\Notifications\Notification::make()
                                ->title('Contactos exportados')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContacts::route('/'),
            'create' => Pages\CreateContact::route('/create'),
            'edit' => Pages\EditContact::route('/{record}/edit'),
        ];
    }
}