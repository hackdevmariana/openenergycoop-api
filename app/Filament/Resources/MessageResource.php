<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MessageResource\Pages;
use App\Models\Message;
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
use Filament\Notifications\Notification;

class MessageResource extends Resource
{
    protected static ?string $model = Message::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    
    protected static ?string $navigationLabel = 'Mensajes';
    
    protected static ?string $modelLabel = 'Mensaje';
    
    protected static ?string $pluralModelLabel = 'Mensajes';
    
    protected static ?string $navigationGroup = 'Comunicaciones y Contenido';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Información del Contacto')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Section::make('Datos del Remitente')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nombre')
                                            ->maxLength(255)
                                            ->disabled(),
                                            
                                        Forms\Components\TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->disabled()
                                            ->copyable(),
                                            
                                        Forms\Components\TextInput::make('phone')
                                            ->label('Teléfono')
                                            ->disabled()
                                            ->copyable(),
                                    ])->columns(3),
                            ]),
                            
                        Tabs\Tab::make('Mensaje')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                Section::make('Contenido del Mensaje')
                                    ->schema([
                                        Forms\Components\TextInput::make('subject')
                                            ->label('Asunto')
                                            ->disabled()
                                            ->columnSpanFull(),
                                            
                                        Forms\Components\Textarea::make('message')
                                            ->label('Mensaje')
                                            ->rows(8)
                                            ->disabled()
                                            ->columnSpanFull(),
                                    ]),
                                    
                                Section::make('Clasificación')
                                    ->schema([
                                        Forms\Components\Select::make('message_type')
                                            ->label('Tipo de Mensaje')
                                            ->options([
                                                'contact' => 'Contacto General',
                                                'support' => 'Soporte Técnico',
                                                'complaint' => 'Queja',
                                                'suggestion' => 'Sugerencia',
                                                'partnership' => 'Colaboración',
                                                'media' => 'Medios de Comunicación',
                                            ])
                                            ->required(),
                                            
                                        Forms\Components\Select::make('priority')
                                            ->label('Prioridad')
                                            ->options([
                                                'low' => 'Baja',
                                                'normal' => 'Normal',
                                                'high' => 'Alta',
                                                'urgent' => 'Urgente',
                                            ])
                                            ->default('normal')
                                            ->required(),
                                    ])->columns(2),
                            ]),
                            
                        Tabs\Tab::make('Gestión')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make('Estado del Mensaje')
                                    ->schema([
                                        Forms\Components\Select::make('status')
                                            ->label('Estado')
                                            ->options([
                                                'pending' => 'Pendiente',
                                                'read' => 'Leído',
                                                'replied' => 'Respondido',
                                                'archived' => 'Archivado',
                                                'spam' => 'Spam',
                                            ])
                                            ->required()
                                            ->default('pending'),
                                            
                                        Forms\Components\Select::make('assigned_to_user_id')
                                            ->label('Asignado a')
                                            ->relationship('assignedTo', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Sin asignar'),
                                    ])->columns(2),
                                    
                                Section::make('Fechas Importantes')
                                    ->schema([
                                        Forms\Components\DateTimePicker::make('read_at')
                                            ->label('Leído el')
                                            ->disabled(),
                                            
                                        Forms\Components\DateTimePicker::make('replied_at')
                                            ->label('Respondido el')
                                            ->disabled(),
                                            
                                        Forms\Components\Select::make('replied_by_user_id')
                                            ->label('Respondido por')
                                            ->relationship('repliedBy', 'name')
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
                                    ])->columns(1),
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium),
                    
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->color('primary'),
                    
                Tables\Columns\TextColumn::make('subject')
                    ->label('Asunto')
                    ->searchable()
                    ->limit(50)
                    ->weight(FontWeight::Medium),
                    
                Tables\Columns\BadgeColumn::make('message_type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'contact',
                        'warning' => 'support',
                        'danger' => 'complaint',
                        'success' => 'suggestion',
                        'secondary' => 'partnership',
                        'gray' => 'media',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'contact' => 'Contacto',
                        'support' => 'Soporte',
                        'complaint' => 'Queja',
                        'suggestion' => 'Sugerencia',
                        'partnership' => 'Colaboración',
                        'media' => 'Medios',
                        default => $state,
                    }),
                    
                Tables\Columns\BadgeColumn::make('priority')
                    ->label('Prioridad')
                    ->colors([
                        'success' => 'low',
                        'primary' => 'normal',
                        'warning' => 'high',
                        'danger' => 'urgent',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'low' => 'Baja',
                        'normal' => 'Normal',
                        'high' => 'Alta',
                        'urgent' => 'Urgente',
                        default => $state,
                    }),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'read',
                        'success' => 'replied',
                        'secondary' => 'archived',
                        'danger' => 'spam',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pending' => 'Pendiente',
                        'read' => 'Leído',
                        'replied' => 'Respondido',
                        'archived' => 'Archivado',
                        'spam' => 'Spam',
                        default => $state,
                    }),
                    
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Asignado a')
                    ->placeholder('Sin asignar')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Recibido')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('replied_at')
                    ->label('Respondido')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Sin responder')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'read' => 'Leído',
                        'replied' => 'Respondido',
                        'archived' => 'Archivado',
                        'spam' => 'Spam',
                    ])
                    ->default('pending'),
                    
                SelectFilter::make('message_type')
                    ->label('Tipo')
                    ->options([
                        'contact' => 'Contacto General',
                        'support' => 'Soporte Técnico',
                        'complaint' => 'Queja',
                        'suggestion' => 'Sugerencia',
                        'partnership' => 'Colaboración',
                        'media' => 'Medios',
                    ]),
                    
                SelectFilter::make('priority')
                    ->label('Prioridad')
                    ->options([
                        'low' => 'Baja',
                        'normal' => 'Normal',
                        'high' => 'Alta',
                        'urgent' => 'Urgente',
                    ]),
                    
                SelectFilter::make('assigned_to_user_id')
                    ->label('Asignado a')
                    ->relationship('assignedTo', 'name')
                    ->placeholder('Todos'),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_read')
                    ->label('Marcar como Leído')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->action(function (Message $record) {
                        $record->update([
                            'status' => 'read',
                            'read_at' => now(),
                        ]);
                        
                        Notification::make()
                            ->title('Mensaje marcado como leído')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Message $record) => $record->status === 'pending'),
                    
                Tables\Actions\Action::make('mark_replied')
                    ->label('Marcar como Respondido')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->action(function (Message $record) {
                        $record->update([
                            'status' => 'replied',
                            'replied_at' => now(),
                            'replied_by_user_id' => auth()->id(),
                        ]);
                        
                        Notification::make()
                            ->title('Mensaje marcado como respondido')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Message $record) => in_array($record->status, ['pending', 'read'])),
                    
                Tables\Actions\Action::make('assign_to_me')
                    ->label('Asignarme')
                    ->icon('heroicon-o-user-plus')
                    ->color('warning')
                    ->action(function (Message $record) {
                        $record->update(['assigned_to_user_id' => auth()->id()]);
                        
                        Notification::make()
                            ->title('Mensaje asignado correctamente')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Message $record) => !$record->assigned_to_user_id),
                    
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_read')
                        ->label('Marcar como leídos')
                        ->icon('heroicon-o-eye')
                        ->action(fn ($records) => $records->each->update([
                            'status' => 'read',
                            'read_at' => now()
                        ]))
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
            'index' => Pages\ListMessages::route('/'),
            'create' => Pages\CreateMessage::route('/create'),
            'edit' => Pages\EditMessage::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }
    
    public static function getNavigationBadgeColor(): string|array|null
    {
        $count = static::getNavigationBadge();
        
        if ($count > 20) {
            return 'danger';
        } elseif ($count > 10) {
            return 'warning';
        }
        
        return 'primary';
    }
}