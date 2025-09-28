<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserSettingsResource\Pages;
use App\Models\UserSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Filters\SelectFilter;

class UserSettingsResource extends Resource
{
    protected static ?string $model = UserSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';
    
    protected static ?string $navigationGroup = 'Usuarios y Organizaciones';
    
    protected static ?string $navigationLabel = 'Configuraciones de Usuario';
    
    protected static ?string $modelLabel = 'Configuración';
    
    protected static ?string $pluralModelLabel = 'Configuraciones';
    
    protected static ?int $navigationSort = 6;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::count();
        
        return match (true) {
            $count >= 50 => 'success',
            $count >= 25 => 'warning',
            $count >= 10 => 'info',
            default => 'gray',
        };
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Usuario')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Section::make('Información del Usuario')
                                    ->schema([
                                        Forms\Components\Select::make('user_id')
                                            ->label('Usuario')
                                            ->relationship('user', 'name')
                                            ->searchable()
                                            ->required()
                                            ->disabled(),
                                    ]),
                            ]),
                            
                        Tabs\Tab::make('Preferencias Generales')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make('Idioma y Región')
                                    ->schema([
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
                                            
                                        Forms\Components\Select::make('timezone')
                                            ->label('Zona Horaria')
                                            ->options([
                                                'Europe/Madrid' => 'Madrid (UTC+1)',
                                                'Europe/London' => 'Londres (UTC+0)',
                                                'America/New_York' => 'Nueva York (UTC-5)',
                                                'America/Los_Angeles' => 'Los Ángeles (UTC-8)',
                                            ])
                                            ->default('Europe/Madrid')
                                            ->searchable(),
                                            
                                        Forms\Components\Select::make('date_format')
                                            ->label('Formato de Fecha')
                                            ->options([
                                                'd/m/Y' => '31/12/2023',
                                                'Y-m-d' => '2023-12-31',
                                                'm/d/Y' => '12/31/2023',
                                                'd-m-Y' => '31-12-2023',
                                            ])
                                            ->default('d/m/Y'),
                                    ])->columns(3),
                                    
                                Section::make('Tema y Apariencia')
                                    ->schema([
                                        Forms\Components\Select::make('theme')
                                            ->label('Tema')
                                            ->options([
                                                'light' => 'Claro',
                                                'dark' => 'Oscuro',
                                                'auto' => 'Automático',
                                            ])
                                            ->default('light'),
                                            
                                        Forms\Components\Select::make('sidebar_collapsed')
                                            ->label('Barra Lateral')
                                            ->options([
                                                '0' => 'Expandida',
                                                '1' => 'Colapsada',
                                            ])
                                            ->default('0'),
                                    ])->columns(2),
                            ]),
                            
                        Tabs\Tab::make('Notificaciones')
                            ->icon('heroicon-o-bell')
                            ->schema([
                                Section::make('Notificaciones por Email')
                                    ->schema([
                                        Forms\Components\Toggle::make('email_notifications')
                                            ->label('Notificaciones por Email')
                                            ->helperText('Recibir notificaciones generales por email')
                                            ->default(true),
                                            
                                        Forms\Components\Toggle::make('marketing_emails')
                                            ->label('Emails de Marketing')
                                            ->helperText('Recibir newsletters y promociones')
                                            ->default(false),
                                            
                                        Forms\Components\Toggle::make('security_alerts')
                                            ->label('Alertas de Seguridad')
                                            ->helperText('Notificaciones sobre actividad de seguridad')
                                            ->default(true),
                                    ])->columns(3),
                                    
                                Section::make('Notificaciones Push')
                                    ->schema([
                                        Forms\Components\Toggle::make('push_notifications')
                                            ->label('Notificaciones Push')
                                            ->helperText('Notificaciones push en dispositivos móviles')
                                            ->default(true),
                                            
                                        Forms\Components\Toggle::make('desktop_notifications')
                                            ->label('Notificaciones de Escritorio')
                                            ->helperText('Notificaciones del navegador web')
                                            ->default(false),
                                    ])->columns(2),
                                    
                                Section::make('Frecuencia de Notificaciones')
                                    ->schema([
                                        Forms\Components\Select::make('notification_frequency')
                                            ->label('Frecuencia')
                                            ->options([
                                                'instant' => 'Instantáneas',
                                                'daily' => 'Diarias',
                                                'weekly' => 'Semanales',
                                                'never' => 'Nunca',
                                            ])
                                            ->default('instant'),
                                    ]),
                            ]),
                            
                        Tabs\Tab::make('Privacidad')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                Section::make('Configuraciones de Privacidad')
                                    ->schema([
                                        Forms\Components\Toggle::make('profile_public')
                                            ->label('Perfil Público')
                                            ->helperText('Permitir que otros usuarios vean tu perfil')
                                            ->default(false),
                                            
                                        Forms\Components\Toggle::make('show_activity')
                                            ->label('Mostrar Actividad')
                                            ->helperText('Mostrar tu actividad reciente a otros usuarios')
                                            ->default(false),
                                            
                                        Forms\Components\Toggle::make('allow_contact')
                                            ->label('Permitir Contacto')
                                            ->helperText('Permitir que otros usuarios te contacten')
                                            ->default(true),
                                    ])->columns(3),
                                    
                                Section::make('Datos y Analytics')
                                    ->schema([
                                        Forms\Components\Toggle::make('analytics_enabled')
                                            ->label('Analytics Habilitado')
                                            ->helperText('Permitir recopilación de datos de uso para mejorar el servicio')
                                            ->default(true),
                                            
                                        Forms\Components\Toggle::make('personalized_ads')
                                            ->label('Publicidad Personalizada')
                                            ->helperText('Mostrar anuncios personalizados basados en tu actividad')
                                            ->default(false),
                                    ])->columns(2),
                            ]),
                            
                        Tabs\Tab::make('Configuraciones Avanzadas')
                            ->icon('heroicon-o-wrench-screwdriver')
                            ->schema([
                                Section::make('Configuraciones Personalizadas')
                                    ->schema([
                                        Forms\Components\KeyValue::make('custom_settings')
                                            ->label('Configuraciones Personalizadas')
                                            ->keyLabel('Configuración')
                                            ->valueLabel('Valor')
                                            ->helperText('Configuraciones específicas del usuario')
                                            ->columnSpanFull(),
                                    ]),
                                    
                                Section::make('Preferencias de Interfaz')
                                    ->schema([
                                        Forms\Components\TextInput::make('items_per_page')
                                            ->label('Elementos por Página')
                                            ->numeric()
                                            ->default(25)
                                            ->helperText('Número de elementos a mostrar en listas'),
                                            
                                        Forms\Components\Toggle::make('compact_mode')
                                            ->label('Modo Compacto')
                                            ->helperText('Usar diseño más compacto')
                                            ->default(false),
                                    ])->columns(2),
                            ]),
                    ])
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium),
                    
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->color('primary'),
                    
                Tables\Columns\BadgeColumn::make('language')
                    ->label('Idioma')
                    ->colors([
                        'primary' => 'es',
                        'success' => 'en',
                        'warning' => 'ca',
                        'danger' => 'eu',
                        'secondary' => 'gl',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'es' => 'Español',
                        'en' => 'English',
                        'ca' => 'Català',
                        'eu' => 'Euskera',
                        'gl' => 'Galego',
                        default => $state,
                    }),
                    
                Tables\Columns\BadgeColumn::make('theme')
                    ->label('Tema')
                    ->colors([
                        'warning' => 'light',
                        'primary' => 'dark',
                        'secondary' => 'auto',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'light' => 'Claro',
                        'dark' => 'Oscuro',
                        'auto' => 'Automático',
                        default => $state,
                    }),
                    
                Tables\Columns\IconColumn::make('email_notifications')
                    ->label('Email')
                    ->boolean()
                    ->trueIcon('heroicon-o-envelope')
                    ->falseIcon('heroicon-o-envelope-open')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip('Notificaciones por email'),
                    
                Tables\Columns\IconColumn::make('push_notifications')
                    ->label('Push')
                    ->boolean()
                    ->trueIcon('heroicon-o-bell')
                    ->falseIcon('heroicon-o-bell-slash')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip('Notificaciones push'),
                    
                Tables\Columns\IconColumn::make('profile_public')
                    ->label('Público')
                    ->boolean()
                    ->trueIcon('heroicon-o-globe-alt')
                    ->falseIcon('heroicon-o-lock-closed')
                    ->trueColor('warning')
                    ->falseColor('success')
                    ->tooltip(fn ($record) => $record->profile_public ? 'Perfil público' : 'Perfil privado'),
                    
                Tables\Columns\TextColumn::make('timezone')
                    ->label('Zona Horaria')
                    ->placeholder('No configurada')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('language')
                    ->label('Idioma')
                    ->options([
                        'es' => 'Español',
                        'en' => 'English',
                        'ca' => 'Català',
                        'eu' => 'Euskera',
                        'gl' => 'Galego',
                    ]),
                    
                SelectFilter::make('theme')
                    ->label('Tema')
                    ->options([
                        'light' => 'Claro',
                        'dark' => 'Oscuro',
                        'auto' => 'Automático',
                    ]),
                    
                SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('reset_to_defaults')
                    ->label('Restaurar Defaults')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (UserSettings $record) {
                        $record->update([
                            'language' => 'es',
                            'timezone' => 'Europe/Madrid',
                            'theme' => 'light',
                            'email_notifications' => true,
                            'push_notifications' => true,
                            'profile_public' => false,
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Configuraciones restauradas')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('enable_notifications')
                        ->label('Habilitar notificaciones')
                        ->icon('heroicon-o-bell')
                        ->action(fn ($records) => $records->each->update([
                            'email_notifications' => true,
                            'push_notifications' => true
                        ]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserSettings::route('/'),
            'create' => Pages\CreateUserSettings::route('/create'),
            'edit' => Pages\EditUserSettings::route('/{record}/edit'),
        ];
    }
}