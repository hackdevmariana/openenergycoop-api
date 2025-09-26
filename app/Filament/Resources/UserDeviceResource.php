<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserDeviceResource\Pages;
use App\Models\UserDevice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class UserDeviceResource extends Resource
{
    protected static ?string $model = UserDevice::class;

    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';
    
    protected static ?string $navigationGroup = 'Gestión de Usuarios';
    
    protected static ?string $navigationLabel = 'Dispositivos de Usuario';
    
    protected static ?string $modelLabel = 'Dispositivo';
    
    protected static ?string $pluralModelLabel = 'Dispositivos';
    
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
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
                    
                Section::make('Información del Dispositivo')
                    ->schema([
                        Forms\Components\TextInput::make('device_name')
                            ->label('Nombre del Dispositivo')
                            ->maxLength(255)
                            ->helperText('Nombre personalizable del dispositivo'),
                            
                        Forms\Components\Select::make('device_type')
                            ->label('Tipo de Dispositivo')
                            ->options(UserDevice::DEVICE_TYPES)
                            ->helperText('Tipo de dispositivo (web, mobile, tablet, desktop)'),
                            
                        Forms\Components\Select::make('platform')
                            ->label('Plataforma')
                            ->options(UserDevice::PLATFORMS)
                            ->helperText('Plataforma del dispositivo (iOS, Android, Windows, etc.)'),
                    ])->columns(3),
                    
                Section::make('Información Técnica')
                    ->schema([
                        Forms\Components\TextInput::make('device_id')
                            ->label('ID del Dispositivo')
                            ->disabled(),
                            
                        Forms\Components\TextInput::make('push_token')
                            ->label('Token de Push')
                            ->disabled(),
                            
                        Forms\Components\Textarea::make('user_agent')
                            ->label('User Agent')
                            ->rows(3)
                            ->disabled(),
                    ])->columns(2),
                    
                Section::make('Estado y Configuración')
                    ->schema([
                        Forms\Components\Toggle::make('is_current')
                            ->label('Dispositivo Actual')
                            ->helperText('Si este es el dispositivo que el usuario está usando actualmente'),
                            
                        Forms\Components\DateTimePicker::make('revoked_at')
                            ->label('Revocado el')
                            ->helperText('Fecha de revocación del dispositivo (vacío = activo)'),
                    ])->columns(3),
                    
                Section::make('Fechas de Actividad')
                    ->schema([
                        Forms\Components\DateTimePicker::make('last_seen_at')
                            ->label('Última Vez Visto')
                            ->disabled(),
                            
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Registrado el')
                            ->disabled(),
                    ])->columns(2),
                    
                Section::make('Configuraciones Adicionales')
                    ->schema([
                        Forms\Components\KeyValue::make('settings')
                            ->label('Configuraciones del Dispositivo')
                            ->keyLabel('Configuración')
                            ->valueLabel('Valor')
                            ->helperText('Configuraciones específicas del dispositivo')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
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
                    ->color('primary')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('device_name')
                    ->label('Nombre del Dispositivo')
                    ->searchable()
                    ->limit(30)
                    ->placeholder('Sin nombre'),
                    
                Tables\Columns\BadgeColumn::make('device_type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'mobile',
                        'success' => 'tablet',
                        'warning' => 'desktop',
                        'secondary' => 'tv',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn ($state) => UserDevice::DEVICE_TYPES[$state] ?? $state),
                    
                Tables\Columns\BadgeColumn::make('platform')
                    ->label('Plataforma')
                    ->colors([
                        'primary' => 'ios',
                        'success' => 'android',
                        'warning' => 'web',
                        'secondary' => 'windows',
                        'info' => 'macos',
                        'gray' => 'linux',
                    ])
                    ->formatStateUsing(fn ($state) => UserDevice::PLATFORMS[$state] ?? $state),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Estado')
                    ->getStateUsing(fn ($record) => is_null($record->revoked_at))
                    ->boolean()
                    ->trueIcon('heroicon-o-signal')
                    ->falseIcon('heroicon-o-signal-slash')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn ($record) => is_null($record->revoked_at) ? 'Activo' : 'Revocado'),
                    
                Tables\Columns\IconColumn::make('is_current')
                    ->label('Actual')
                    ->boolean()
                    ->trueIcon('heroicon-o-device-phone-mobile')
                    ->falseIcon('heroicon-o-device-phone-mobile')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->tooltip(fn ($record) => $record->is_current ? 'Dispositivo actual' : 'No es el dispositivo actual'),
                    
                Tables\Columns\IconColumn::make('has_push_token')
                    ->label('Push')
                    ->getStateUsing(fn ($record) => !empty($record->push_token))
                    ->boolean()
                    ->trueIcon('heroicon-o-bell')
                    ->falseIcon('heroicon-o-bell-slash')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn ($record) => !empty($record->push_token) ? 'Push token disponible' : 'Sin push token'),
                    
                Tables\Columns\TextColumn::make('last_seen_at')
                    ->label('Última Actividad')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->color(fn ($record) => $record->last_seen_at?->isAfter(now()->subDays(7)) ? 'success' : 'gray'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('revoked_at')
                    ->label('Revocado')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Activo')
                    ->color('danger')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('device_type')
                    ->label('Tipo de Dispositivo')
                    ->options(UserDevice::DEVICE_TYPES),
                    
                SelectFilter::make('platform')
                    ->label('Plataforma')
                    ->options(UserDevice::PLATFORMS),
                    
                TernaryFilter::make('revoked_at')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Solo revocados')
                    ->falseLabel('Solo activos')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('revoked_at'),
                        false: fn ($query) => $query->whereNull('revoked_at'),
                    ),
                    
                TernaryFilter::make('is_current')
                    ->label('Dispositivo Actual')
                    ->placeholder('Todos')
                    ->trueLabel('Solo dispositivos actuales')
                    ->falseLabel('Solo dispositivos no actuales'),
                    
                TernaryFilter::make('push_token')
                    ->label('Push Token')
                    ->placeholder('Todos')
                    ->trueLabel('Con push token')
                    ->falseLabel('Sin push token')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('push_token'),
                        false: fn ($query) => $query->whereNull('push_token'),
                    ),
                    
                SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('send_test_notification')
                    ->label('Enviar Notificación de Prueba')
                    ->icon('heroicon-o-bell')
                    ->color('primary')
                    ->action(function (UserDevice $record) {
                        // Aquí implementarías el envío de notificación de prueba
                        \Filament\Notifications\Notification::make()
                            ->title('Notificación de prueba enviada')
                            ->body("Notificación enviada a {$record->device_name}")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (UserDevice $record) => !empty($record->push_token) && is_null($record->revoked_at)),
                    
                Tables\Actions\Action::make('set_current')
                    ->label(fn ($record) => $record->is_current ? 'Ya es Actual' : 'Marcar como Actual')
                    ->icon(fn ($record) => $record->is_current ? 'heroicon-o-device-phone-mobile' : 'heroicon-o-device-phone-mobile')
                    ->color(fn ($record) => $record->is_current ? 'gray' : 'warning')
                    ->action(function (UserDevice $record) {
                        $record->setCurrent();
                        \Filament\Notifications\Notification::make()
                            ->title('Dispositivo marcado como actual')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (UserDevice $record) => !$record->is_current && is_null($record->revoked_at)),
                    
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('revoke')
                    ->label('Revocar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(function (UserDevice $record) {
                        $record->revoke();
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Dispositivo revocado')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (UserDevice $record) => is_null($record->revoked_at))
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('revoke')
                        ->label('Revocar seleccionados')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn ($records) => $records->each->revoke())
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('last_seen_at', 'desc')
            ->persistSortInSession()
            ->persistFiltersInSession();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserDevices::route('/'),
            'edit' => Pages\EditUserDevice::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereNull('revoked_at')->count();
    }
    
    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }
    
    public static function canCreate(): bool
    {
        return false; // Los dispositivos se registran automáticamente
    }
}