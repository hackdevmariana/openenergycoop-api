<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationSettingResource\Pages;
use App\Filament\Resources\NotificationSettingResource\RelationManagers;
use App\Models\NotificationSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NotificationSettingResource extends Resource
{
    protected static ?string $model = NotificationSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Comunicaciones';

    protected static ?string $navigationLabel = 'Configuración de Notificaciones';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Configuración de Notificación';

    protected static ?string $pluralModelLabel = 'Configuraciones de Notificaciones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Configuración de Notificación')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Usuario'),

                        Forms\Components\Select::make('channel')
                            ->label('Canal')
                            ->options([
                                'email' => 'Email',
                                'push' => 'Push',
                                'sms' => 'SMS',
                                'in_app' => 'En la Aplicación',
                            ])
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\Select::make('notification_type')
                            ->label('Tipo de Notificación')
                            ->options([
                                'wallet' => 'Billetera',
                                'event' => 'Eventos',
                                'message' => 'Mensajes',
                                'general' => 'General',
                            ])
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\Toggle::make('enabled')
                            ->label('Habilitado')
                            ->default(true)
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Información del Usuario')
                    ->schema([
                        Forms\Components\Placeholder::make('user_name')
                            ->label('Nombre del Usuario')
                            ->content(function ($record) {
                                return $record?->user?->name ?? 'N/A';
                            }),

                        Forms\Components\Placeholder::make('user_email')
                            ->label('Email del Usuario')
                            ->content(function ($record) {
                                return $record?->user?->email ?? 'N/A';
                            }),

                        Forms\Components\Placeholder::make('channel_info')
                            ->label('Información del Canal')
                            ->content(function ($record) {
                                if (!$record) return 'N/A';
                                return $record->getChannelLabelAttribute();
                            }),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->collapsed(),

                Forms\Components\Section::make('Estado y Fechas')
                    ->schema([
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Fecha de Creación')
                            ->disabled()
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('updated_at')
                            ->label('Fecha de Actualización')
                            ->disabled()
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('channel')
                    ->label('Canal')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'email' => 'info',
                        'push' => 'warning',
                        'sms' => 'success',
                        'in_app' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'email' => 'Email',
                        'push' => 'Push',
                        'sms' => 'SMS',
                        'in_app' => 'En la App',
                        default => $state,
                    })
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('notification_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'wallet' => 'success',
                        'event' => 'warning',
                        'message' => 'info',
                        'general' => 'gray',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'wallet' => 'Billetera',
                        'event' => 'Eventos',
                        'message' => 'Mensajes',
                        'general' => 'General',
                        default => $state,
                    })
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('enabled')
                    ->label('Habilitado')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('channel')
                    ->label('Canal')
                    ->options([
                        'email' => 'Email',
                        'push' => 'Push',
                        'sms' => 'SMS',
                        'in_app' => 'En la Aplicación',
                    ]),

                Tables\Filters\SelectFilter::make('notification_type')
                    ->label('Tipo de Notificación')
                    ->options([
                        'wallet' => 'Billetera',
                        'event' => 'Eventos',
                        'message' => 'Mensajes',
                        'general' => 'General',
                    ]),

                Tables\Filters\SelectFilter::make('user')
                    ->label('Usuario')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('enabled')
                    ->label('Estado')
                    ->placeholder('Todas')
                    ->trueLabel('Solo habilitadas')
                    ->falseLabel('Solo deshabilitadas'),

                Tables\Filters\Filter::make('mobile_channels')
                    ->label('Canales Móviles')
                    ->query(fn (Builder $query): Builder => $query->whereIn('channel', ['push', 'sms']))
                    ->toggle(),

                Tables\Filters\Filter::make('digital_channels')
                    ->label('Canales Digitales')
                    ->query(fn (Builder $query): Builder => $query->whereIn('channel', ['email', 'in_app']))
                    ->toggle(),

                Tables\Filters\Filter::make('financial_types')
                    ->label('Tipos Financieros')
                    ->query(fn (Builder $query): Builder => $query->whereIn('notification_type', ['wallet']))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\Action::make('toggle_enabled')
                    ->label('Cambiar Estado')
                    ->icon('heroicon-o-toggle-right')
                    ->color(fn ($record): string => $record->enabled ? 'warning' : 'success')
                    ->action(fn ($record) => $record->toggle())
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar Cambio')
                    ->modalDescription(fn ($record): string => 
                        $record->enabled 
                            ? '¿Estás seguro de que quieres deshabilitar esta configuración?' 
                            : '¿Estás seguro de que quieres habilitar esta configuración?'
                    )
                    ->modalSubmitActionLabel(fn ($record): string => 
                        $record->enabled ? 'Sí, Deshabilitar' : 'Sí, Habilitar'
                    ),

                Tables\Actions\ViewAction::make()
                    ->label('Ver'),

                Tables\Actions\EditAction::make()
                    ->label('Editar'),

                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('enable_all')
                        ->label('Habilitar Todas')
                        ->icon('heroicon-o-check-circle')
                        ->action(function ($records) {
                            $records->each->enable();
                        })
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('disable_all')
                        ->label('Deshabilitar Todas')
                        ->icon('heroicon-o-x-circle')
                        ->action(function ($records) {
                            $records->each->disable();
                        })
                        ->requiresConfirmation(),

                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar Seleccionadas'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
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
            'index' => Pages\ListNotificationSettings::route('/'),
            'create' => Pages\CreateNotificationSetting::route('/create'),
            'edit' => Pages\EditNotificationSetting::route('/{record}/edit'),
        ];
    }
}
