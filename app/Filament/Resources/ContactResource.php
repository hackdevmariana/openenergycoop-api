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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Información de Contacto')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Section::make('Datos Personales')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nombre')
                                            ->required()
                                            ->maxLength(255),
                                            
                                        Forms\Components\TextInput::make('position')
                                            ->label('Cargo/Posición')
                                            ->maxLength(255),
                                            
                                        Forms\Components\Select::make('department')
                                            ->label('Departamento')
                                            ->options([
                                                'general' => 'General',
                                                'administration' => 'Administración',
                                                'technical' => 'Técnico',
                                                'commercial' => 'Comercial',
                                                'marketing' => 'Marketing',
                                                'legal' => 'Legal',
                                                'support' => 'Soporte',
                                                'management' => 'Dirección',
                                            ])
                                            ->default('general'),
                                    ])->columns(3),
                                    
                                Section::make('Información de Contacto')
                                    ->schema([
                                        Forms\Components\TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->required(),
                                            
                                        Forms\Components\TextInput::make('phone')
                                            ->label('Teléfono')
                                            ->tel(),
                                            
                                        Forms\Components\TextInput::make('mobile')
                                            ->label('Móvil')
                                            ->tel(),
                                            
                                        Forms\Components\TextInput::make('fax')
                                            ->label('Fax')
                                            ->tel(),
                                    ])->columns(2),
                            ]),
                            
                        Tabs\Tab::make('Dirección')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Section::make('Dirección Física')
                                    ->schema([
                                        Forms\Components\Textarea::make('address')
                                            ->label('Dirección')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                            
                                        Forms\Components\TextInput::make('city')
                                            ->label('Ciudad')
                                            ->maxLength(255),
                                            
                                        Forms\Components\TextInput::make('state')
                                            ->label('Provincia/Estado')
                                            ->maxLength(255),
                                            
                                        Forms\Components\TextInput::make('postal_code')
                                            ->label('Código Postal')
                                            ->maxLength(20),
                                            
                                        Forms\Components\TextInput::make('country')
                                            ->label('País')
                                            ->maxLength(255)
                                            ->default('España'),
                                    ])->columns(2),
                            ]),
                            
                        Tabs\Tab::make('Horarios y Disponibilidad')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                Section::make('Horarios de Atención')
                                    ->schema([
                                        Forms\Components\KeyValue::make('office_hours')
                                            ->label('Horarios de Oficina')
                                            ->keyLabel('Día')
                                            ->valueLabel('Horario')
                                            ->helperText('Ej: Lunes: 9:00-17:00, Martes: 9:00-17:00')
                                            ->columnSpanFull(),
                                    ]),
                                    
                                Section::make('Idiomas')
                                    ->schema([
                                        Forms\Components\TagsInput::make('languages')
                                            ->label('Idiomas')
                                            ->placeholder('Español, Inglés, Catalán...')
                                            ->helperText('Idiomas que habla este contacto')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                            
                        Tabs\Tab::make('Configuración')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make('Visibilidad')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_public')
                                            ->label('Público')
                                            ->helperText('Si está activado, este contacto será visible en el sitio web')
                                            ->default(true),
                                            
                                        Forms\Components\Toggle::make('is_primary')
                                            ->label('Contacto Principal')
                                            ->helperText('Contacto principal del departamento')
                                            ->default(false),
                                            
                                        Forms\Components\Toggle::make('accepts_marketing')
                                            ->label('Acepta Marketing')
                                            ->helperText('Puede recibir comunicaciones de marketing')
                                            ->default(false),
                                    ])->columns(3),
                                    
                                Section::make('Organización')
                                    ->schema([
                                        Forms\Components\Select::make('organization_id')
                                            ->label('Organización')
                                            ->relationship('organization', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Global (todas las organizaciones)'),
                                    ]),
                                    
                                Section::make('Notas Internas')
                                    ->schema([
                                        Forms\Components\Textarea::make('notes')
                                            ->label('Notas')
                                            ->rows(4)
                                            ->helperText('Notas internas sobre este contacto')
                                            ->columnSpanFull(),
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
                    
                Tables\Columns\TextColumn::make('position')
                    ->label('Cargo')
                    ->searchable()
                    ->placeholder('Sin especificar'),
                    
                Tables\Columns\BadgeColumn::make('department')
                    ->label('Departamento')
                    ->colors([
                        'primary' => 'general',
                        'success' => 'administration',
                        'warning' => 'technical',
                        'danger' => 'commercial',
                        'secondary' => 'marketing',
                        'gray' => 'legal',
                        'info' => 'support',
                        'purple' => 'management',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'general' => 'General',
                        'administration' => 'Administración',
                        'technical' => 'Técnico',
                        'commercial' => 'Comercial',
                        'marketing' => 'Marketing',
                        'legal' => 'Legal',
                        'support' => 'Soporte',
                        'management' => 'Dirección',
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
                    
                Tables\Columns\TextColumn::make('city')
                    ->label('Ciudad')
                    ->searchable()
                    ->toggleable(),
                    
                Tables\Columns\IconColumn::make('is_primary')
                    ->label('Principal')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                    
                Tables\Columns\IconColumn::make('is_public')
                    ->label('Público')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
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
                SelectFilter::make('department')
                    ->label('Departamento')
                    ->options([
                        'general' => 'General',
                        'administration' => 'Administración',
                        'technical' => 'Técnico',
                        'commercial' => 'Comercial',
                        'marketing' => 'Marketing',
                        'legal' => 'Legal',
                        'support' => 'Soporte',
                        'management' => 'Dirección',
                    ]),
                    
                SelectFilter::make('city')
                    ->label('Ciudad')
                    ->options(function () {
                        return Contact::distinct('city')
                            ->whereNotNull('city')
                            ->pluck('city', 'city')
                            ->toArray();
                    }),
                    
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
                    
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('make_public')
                        ->label('Hacer públicos')
                        ->icon('heroicon-o-eye')
                        ->action(fn ($records) => $records->each->update(['is_public' => true]))
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
            ->defaultSort('name', 'asc');
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