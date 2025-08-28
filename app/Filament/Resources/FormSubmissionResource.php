<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormSubmissionResource\Pages;
use App\Models\FormSubmission;
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

class FormSubmissionResource extends Resource
{
    protected static ?string $model = FormSubmission::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    
    protected static ?string $navigationGroup = 'Comunicación';
    
    protected static ?string $navigationLabel = 'Envíos de Formularios';
    
    protected static ?string $modelLabel = 'Envío';
    
    protected static ?string $pluralModelLabel = 'Envíos';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Información del Formulario')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make('Datos Básicos')
                                    ->schema([
                                        Forms\Components\TextInput::make('form_name')
                                            ->label('Nombre del Formulario')
                                            ->disabled()
                                            ->helperText('Nombre interno del formulario'),
                                            
                                        Forms\Components\TextInput::make('form_type')
                                            ->label('Tipo de Formulario')
                                            ->disabled(),
                                            
                                        Forms\Components\TextInput::make('source_page')
                                            ->label('Página de Origen')
                                            ->disabled()
                                            ->copyable(),
                                    ])->columns(3),
                                    
                                Section::make('Información del Usuario')
                                    ->schema([
                                        Forms\Components\TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->disabled()
                                            ->copyable()
                                            ->helperText('Email extraído de los campos del formulario'),
                                            
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nombre')
                                            ->disabled()
                                            ->helperText('Nombre extraído de los campos del formulario'),
                                            
                                        Forms\Components\TextInput::make('phone')
                                            ->label('Teléfono')
                                            ->disabled()
                                            ->helperText('Teléfono extraído de los campos del formulario'),
                                    ])->columns(3),
                            ]),
                            
                        Tabs\Tab::make('Datos del Formulario')
                            ->icon('heroicon-o-clipboard-document-check')
                            ->schema([
                                Section::make('Datos Enviados')
                                    ->schema([
                                        Forms\Components\KeyValue::make('fields')
                                            ->label('Datos del Formulario')
                                            ->keyLabel('Campo')
                                            ->valueLabel('Valor')
                                            ->disabled()
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                            
                        Tabs\Tab::make('Gestión')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make('Estado del Envío')
                                    ->schema([
                                        Forms\Components\Select::make('status')
                                            ->label('Estado')
                                            ->options([
                                                'pending' => 'Pendiente',
                                                'reviewed' => 'Revisado',
                                                'processed' => 'Procesado',
                                                'archived' => 'Archivado',
                                                'spam' => 'Spam',
                                            ])
                                            ->required()
                                            ->default('pending'),
                                            
                                        Forms\Components\TextInput::make('assigned_to')
                                            ->label('Asignado a')
                                            ->disabled()
                                            ->helperText('Usuario asignado para procesar este envío'),
                                    ])->columns(2),
                                    
                                Section::make('Fechas de Seguimiento')
                                    ->schema([
                                        Forms\Components\DateTimePicker::make('reviewed_at')
                                            ->label('Revisado el')
                                            ->disabled(),
                                            
                                        Forms\Components\DateTimePicker::make('processed_at')
                                            ->label('Procesado el')
                                            ->disabled(),
                                            
                                        Forms\Components\Select::make('processed_by_user_id')
                                            ->label('Procesado por')
                                            ->relationship('processedBy', 'name')
                                            ->disabled(),
                                    ])->columns(3),
                                    
                                Section::make('Notas Internas')
                                    ->schema([
                                        Forms\Components\Textarea::make('internal_notes')
                                            ->label('Notas Internas')
                                            ->rows(4)
                                            ->helperText('Notas visibles solo para el equipo')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                            
                        Tabs\Tab::make('Información Técnica')
                            ->icon('heroicon-o-computer-desktop')
                            ->schema([
                                Section::make('Datos Técnicos')
                                    ->schema([
                                        Forms\Components\TextInput::make('ip_address')
                                            ->label('Dirección IP')
                                            ->disabled()
                                            ->copyable(),
                                            
                                        Forms\Components\Textarea::make('user_agent')
                                            ->label('User Agent')
                                            ->rows(3)
                                            ->disabled(),
                                            
                                        Forms\Components\TextInput::make('referrer')
                                            ->label('Página de Referencia')
                                            ->disabled()
                                            ->copyable(),
                                    ])->columns(1),
                                    
                                                                    Section::make('Metadatos')
                                        ->schema([
                                            Forms\Components\TextInput::make('source_url')
                                                ->label('URL de Origen')
                                                ->disabled()
                                                ->copyable(),
                                            
                                            Forms\Components\TextInput::make('referrer')
                                                ->label('Página de Referencia')
                                                ->disabled()
                                                ->copyable(),
                                        ])->columns(2),
                            ])
                            ->hidden(fn () => !auth()->user()->hasRole('admin')),
                    ])
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('form_name')
                    ->label('Formulario')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->badge()
                    ->color('primary'),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->placeholder('Sin nombre'),
                    
                Tables\Columns\TextColumn::make('fields')
                    ->label('Datos')
                    ->formatStateUsing(function ($state) {
                        if (is_array($state)) {
                            $email = $state['email'] ?? $state['Email'] ?? null;
                            $name = $state['name'] ?? $state['Name'] ?? null;
                            return $name ? "{$name} ({$email})" : $email;
                        }
                        return 'Sin datos';
                    })
                    ->searchable()
                    ->copyable()
                    ->color('primary'),
                    
                Tables\Columns\BadgeColumn::make('form_name')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'contact',
                        'success' => 'newsletter',
                        'warning' => 'support',
                        'danger' => 'spam',
                        'secondary' => 'survey',
                        'gray' => 'general',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'contact' => 'Contacto',
                        'newsletter' => 'Newsletter',
                        'support' => 'Soporte',
                        'survey' => 'Encuesta',
                        'registration' => 'Registro',
                        'quote' => 'Cotización',
                        'partnership' => 'Colaboración',
                        'job_application' => 'Trabajo',
                        'volunteer' => 'Voluntariado',
                        'general' => 'General',
                        default => $state,
                    }),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'processed',
                        'secondary' => 'archived',
                        'danger' => 'spam',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pending' => 'Pendiente',
                        'processed' => 'Procesado',
                        'archived' => 'Archivado',
                        'spam' => 'Spam',
                        default => $state,
                    }),
                    
                Tables\Columns\TextColumn::make('source_url')
                    ->label('Página')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->source_url)
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Enviado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('processed_at')
                    ->label('Procesado')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Sin procesar')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('form_name')
                    ->label('Tipo de Formulario')
                    ->options([
                        'contact' => 'Contacto',
                        'newsletter' => 'Newsletter',
                        'support' => 'Soporte',
                        'survey' => 'Encuesta',
                        'registration' => 'Registro',
                        'quote' => 'Cotización',
                        'partnership' => 'Colaboración',
                        'job_application' => 'Trabajo',
                        'volunteer' => 'Voluntariado',
                        'general' => 'General',
                    ]),
                    
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'processed' => 'Procesado',
                        'archived' => 'Archivado',
                        'spam' => 'Spam',
                    ])
                    ->default('pending'),
                    
                SelectFilter::make('organization_id')
                    ->label('Organización')
                    ->relationship('organization', 'name')
                    ->placeholder('Todas las organizaciones'),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_processed')
                    ->label('Marcar como Procesado')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (FormSubmission $record) {
                        $record->update([
                            'status' => 'processed',
                            'processed_at' => now(),
                            'processed_by_user_id' => auth()->id(),
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Envío marcado como procesado')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (FormSubmission $record) => $record->status === 'pending'),
                    
                Tables\Actions\Action::make('mark_spam')
                    ->label('Marcar como Spam')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->action(function (FormSubmission $record) {
                        $record->update(['status' => 'spam']);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Envío marcado como spam')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (FormSubmission $record) => $record->status === 'pending'),
                    
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_processed')
                        ->label('Marcar como procesados')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn ($records) => $records->each->update([
                            'status' => 'processed',
                            'processed_at' => now(),
                            'processed_by_user_id' => auth()->id()
                        ]))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('mark_spam')
                        ->label('Marcar como spam')
                        ->icon('heroicon-o-exclamation-triangle')
                        ->action(fn ($records) => $records->each->update(['status' => 'spam']))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('archive')
                        ->label('Archivar seleccionados')
                        ->icon('heroicon-o-archive-box')
                        ->action(fn ($records) => $records->each->update(['status' => 'archived']))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s'); // Auto-refresh every 30 seconds
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFormSubmissions::route('/'),
            'create' => Pages\CreateFormSubmission::route('/create'),
            'edit' => Pages\EditFormSubmission::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }
    
    public static function getNavigationBadgeColor(): string|array|null
    {
        $count = static::getNavigationBadge();
        
        if ($count > 50) {
            return 'danger';
        } elseif ($count > 20) {
            return 'warning';
        }
        
        return 'primary';
    }
}