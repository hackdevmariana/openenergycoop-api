<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterSubscriptionResource\Pages;
use App\Models\NewsletterSubscription;
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

class NewsletterSubscriptionResource extends Resource
{
    protected static ?string $model = NewsletterSubscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    
    protected static ?string $navigationLabel = 'Newsletter';
    
    protected static ?string $modelLabel = 'Suscripción';
    
    protected static ?string $pluralModelLabel = 'Suscripciones';
    
    protected static ?string $navigationGroup = 'Comunicaciones y Contenido';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Información del Suscriptor')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Section::make('Datos Básicos')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nombre')
                                            ->maxLength(255),
                                            
                                        Forms\Components\TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->required()
                                            ->unique(NewsletterSubscription::class, 'email', ignoreRecord: true),
                                            
                                        Forms\Components\Select::make('language')
                                            ->label('Idioma')
                                            ->options([
                                                'es' => 'Español',
                                                'en' => 'English',
                                                'ca' => 'Català',
                                                'eu' => 'Euskera',
                                                'gl' => 'Galego',
                                            ])
                                            ->default('es')
                                            ->required(),
                                    ])->columns(3),
                                    
                                Section::make('Origen de la Suscripción')
                                    ->schema([
                                        Forms\Components\TextInput::make('subscription_source')
                                            ->label('Fuente')
                                            ->placeholder('website, api, import, evento, etc.')
                                            ->helperText('De dónde proviene esta suscripción'),
                                            
                                        Forms\Components\Select::make('organization_id')
                                            ->label('Organización')
                                            ->relationship('organization', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Global (todas las organizaciones)'),
                                    ])->columns(2),
                            ]),
                            
                        Tabs\Tab::make('Estado y Preferencias')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make('Estado de la Suscripción')
                                    ->schema([
                                        Forms\Components\Select::make('status')
                                            ->label('Estado')
                                            ->options([
                                                'pending' => 'Pendiente de Confirmación',
                                                'confirmed' => 'Confirmado',
                                                'unsubscribed' => 'Dado de Baja',
                                                'bounced' => 'Rebotado',
                                                'complained' => 'Marcado como Spam',
                                            ])
                                            ->required()
                                            ->default('pending'),
                                    ])->columns(1),
                                    
                                Section::make('Fechas Importantes')
                                    ->schema([
                                        Forms\Components\DateTimePicker::make('confirmed_at')
                                            ->label('Confirmado el')
                                            ->disabled(),
                                            
                                        Forms\Components\DateTimePicker::make('unsubscribed_at')
                                            ->label('Dado de baja el')
                                            ->disabled(),
                                    ])->columns(2),
                                    
                                Section::make('Preferencias')
                                    ->schema([
                                        Forms\Components\KeyValue::make('preferences')
                                            ->label('Preferencias de Newsletter')
                                            ->keyLabel('Tipo')
                                            ->valueLabel('Activo')
                                            ->helperText('Tipos de newsletter y configuraciones específicas'),
                                            
                                        Forms\Components\TagsInput::make('tags')
                                            ->label('Etiquetas')
                                            ->placeholder('Escriba etiquetas para segmentación')
                                            ->helperText('Etiquetas para segmentar campañas'),
                                    ])->columns(2),
                            ]),
                            
                        Tabs\Tab::make('Estadísticas')
                            ->icon('heroicon-o-chart-bar')
                            ->schema([
                                Section::make('Métricas de Engagement')
                                    ->schema([
                                        Forms\Components\TextInput::make('emails_sent')
                                            ->label('Emails Enviados')
                                            ->numeric()
                                            ->default(0)
                                            ->disabled(),
                                            
                                        Forms\Components\TextInput::make('emails_opened')
                                            ->label('Emails Abiertos')
                                            ->numeric()
                                            ->default(0)
                                            ->disabled(),
                                            
                                        Forms\Components\TextInput::make('links_clicked')
                                            ->label('Enlaces Clickeados')
                                            ->numeric()
                                            ->default(0)
                                            ->disabled(),
                                    ])->columns(3),
                                    
                                Section::make('Última Actividad')
                                    ->schema([
                                        Forms\Components\DateTimePicker::make('last_email_sent_at')
                                            ->label('Último Email Enviado')
                                            ->disabled(),
                                            
                                        Forms\Components\DateTimePicker::make('last_email_opened_at')
                                            ->label('Último Email Abierto')
                                            ->disabled(),
                                    ])->columns(2),
                            ]),
                            
                        Tabs\Tab::make('Información Técnica')
                            ->icon('heroicon-o-computer-desktop')
                            ->schema([
                                Section::make('Tokens de Gestión')
                                    ->schema([
                                        Forms\Components\TextInput::make('confirmation_token')
                                            ->label('Token de Confirmación')
                                            ->disabled()
                                            ->copyable(),
                                            
                                        Forms\Components\TextInput::make('unsubscribe_token')
                                            ->label('Token de Baja')
                                            ->disabled()
                                            ->copyable(),
                                    ])->columns(2),
                                    
                                Section::make('Datos de Origen')
                                    ->schema([
                                        Forms\Components\TextInput::make('ip_address')
                                            ->label('Dirección IP')
                                            ->disabled()
                                            ->copyable(),
                                            
                                        Forms\Components\Textarea::make('user_agent')
                                            ->label('User Agent')
                                            ->rows(2)
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
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->copyable(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->placeholder('Sin nombre'),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'danger' => 'unsubscribed',
                        'secondary' => 'bounced',
                        'gray' => 'complained',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmado',
                        'unsubscribed' => 'Dado de Baja',
                        'bounced' => 'Rebotado',
                        'complained' => 'Spam',
                        default => $state,
                    }),
                    
                Tables\Columns\BadgeColumn::make('language')
                    ->label('Idioma')
                    ->colors([
                        'primary' => 'es',
                        'success' => 'en',
                        'warning' => 'ca',
                        'danger' => 'eu',
                        'secondary' => 'gl',
                    ]),
                    
                Tables\Columns\TextColumn::make('subscription_source')
                    ->label('Fuente')
                    ->badge()
                    ->placeholder('Desconocida')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('emails_sent')
                    ->label('Enviados')
                    ->numeric()
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('emails_opened')
                    ->label('Abiertos')
                    ->numeric()
                    ->alignCenter()
                    ->sortable()
                    ->color('success')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('open_rate')
                    ->label('% Apertura')
                    ->getStateUsing(function ($record) {
                        if ($record->emails_sent == 0) return '0%';
                        return round(($record->emails_opened / $record->emails_sent) * 100, 1) . '%';
                    })
                    ->alignCenter()
                    ->color('primary')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('confirmed_at')
                    ->label('Confirmado')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('No confirmado')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Suscrito')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmado',
                        'unsubscribed' => 'Dado de Baja',
                        'bounced' => 'Rebotado',
                        'complained' => 'Spam',
                    ])
                    ->default('confirmed'),
                    
                SelectFilter::make('language')
                    ->label('Idioma')
                    ->options([
                        'es' => 'Español',
                        'en' => 'English',
                        'ca' => 'Català',
                        'eu' => 'Euskera',
                        'gl' => 'Galego',
                    ]),
                    
                SelectFilter::make('subscription_source')
                    ->label('Fuente')
                    ->options([
                        'website' => 'Sitio Web',
                        'api' => 'API',
                        'import' => 'Importación',
                        'evento' => 'Evento',
                        'referido' => 'Referido',
                    ]),
                    
                SelectFilter::make('organization_id')
                    ->label('Organización')
                    ->relationship('organization', 'name')
                    ->placeholder('Todas las organizaciones'),
            ])
            ->actions([
                Tables\Actions\Action::make('confirm')
                    ->label('Confirmar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (NewsletterSubscription $record) {
                        $record->update([
                            'status' => 'confirmed',
                            'confirmed_at' => now(),
                        ]);
                        
                        Notification::make()
                            ->title('Suscripción confirmada')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (NewsletterSubscription $record) => $record->status === 'pending'),
                    
                Tables\Actions\Action::make('unsubscribe')
                    ->label('Dar de Baja')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(function (NewsletterSubscription $record) {
                        $record->update([
                            'status' => 'unsubscribed',
                            'unsubscribed_at' => now(),
                        ]);
                        
                        Notification::make()
                            ->title('Suscripción cancelada')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (NewsletterSubscription $record) => in_array($record->status, ['pending', 'confirmed']))
                    ->requiresConfirmation(),
                    
                Tables\Actions\Action::make('resubscribe')
                    ->label('Reactivar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (NewsletterSubscription $record) {
                        $record->update([
                            'status' => 'confirmed',
                            'confirmed_at' => now(),
                            'unsubscribed_at' => null,
                        ]);
                        
                        Notification::make()
                            ->title('Suscripción reactivada')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (NewsletterSubscription $record) => $record->status === 'unsubscribed'),
                    
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('confirm')
                        ->label('Confirmar seleccionados')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update([
                            'status' => 'confirmed',
                            'confirmed_at' => now()
                        ]))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('unsubscribe')
                        ->label('Dar de baja seleccionados')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update([
                            'status' => 'unsubscribed',
                            'unsubscribed_at' => now()
                        ]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsletterSubscriptions::route('/'),
            'create' => Pages\CreateNewsletterSubscription::route('/create'),
            'edit' => Pages\EditNewsletterSubscription::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'confirmed')->count();
    }
    
    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }
}