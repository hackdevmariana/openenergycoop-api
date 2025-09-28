<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Filament\Resources\NotificationResource\RelationManagers;
use App\Models\Notification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static ?string $navigationGroup = 'Comunicaciones y Contenido';

    protected static ?string $navigationLabel = 'Notificaciones';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Notificación';

    protected static ?string $pluralModelLabel = 'Notificaciones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Notificación')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Usuario'),

                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('message')
                            ->label('Mensaje')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('type')
                            ->label('Tipo')
                            ->options([
                                'info' => 'Información',
                                'alert' => 'Alerta',
                                'success' => 'Éxito',
                                'warning' => 'Advertencia',
                                'error' => 'Error',
                            ])
                            ->default('info')
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\Toggle::make('is_read')
                            ->label('¿Leída?')
                            ->default(false)
                            ->afterStateHydrated(function ($component, $state, $record) {
                                if ($record) {
                                    $component->state($record->isRead());
                                }
                            })
                            ->live()
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Estado y Fechas')
                    ->schema([
                        Forms\Components\DateTimePicker::make('read_at')
                            ->label('Fecha de Lectura')
                            ->nullable()
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('delivered_at')
                            ->label('Fecha de Entrega')
                            ->nullable()
                            ->columnSpan(1),

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

                        Forms\Components\Placeholder::make('time_ago')
                            ->label('Tiempo Transcurrido')
                            ->content(function ($record) {
                                return $record?->getTimeAgoAttribute() ?? 'N/A';
                            }),
                    ])
                    ->columns(3)
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

                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->limit(50)
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'warning' => 'warning',
                        'error' => 'danger',
                        'alert' => 'warning',
                        default => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'info' => 'Información',
                        'alert' => 'Alerta',
                        'success' => 'Éxito',
                        'warning' => 'Advertencia',
                        'error' => 'Error',
                    })
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_read')
                    ->label('Leída')
                    ->boolean()
                    ->getStateUsing(fn ($record): bool => $record->isRead())
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_delivered')
                    ->label('Entregada')
                    ->boolean()
                    ->getStateUsing(fn ($record): bool => $record->isDelivered())
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('read_at')
                    ->label('Leída el')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->placeholder('No leída'),

                Tables\Columns\TextColumn::make('delivered_at')
                    ->label('Entregada el')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->placeholder('No entregada'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'info' => 'Información',
                        'alert' => 'Alerta',
                        'success' => 'Éxito',
                        'warning' => 'Advertencia',
                        'error' => 'Error',
                    ]),

                Tables\Filters\SelectFilter::make('user')
                    ->label('Usuario')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_read')
                    ->label('Estado de Lectura')
                    ->placeholder('Todas')
                    ->trueLabel('Solo leídas')
                    ->falseLabel('Solo no leídas'),

                Tables\Filters\TernaryFilter::make('is_delivered')
                    ->label('Estado de Entrega')
                    ->placeholder('Todas')
                    ->trueLabel('Solo entregadas')
                    ->falseLabel('Solo no entregadas'),

                Tables\Filters\Filter::make('recent')
                    ->label('Recientes (24h)')
                    ->query(fn (Builder $query): Builder => $query->recent(1))
                    ->toggle(),

                Tables\Filters\Filter::make('overdue')
                    ->label('Vencidas')
                    ->query(fn (Builder $query): Builder => $query->overdueStatus())
                    ->toggle(),

                Tables\Filters\Filter::make('urgent')
                    ->label('Urgentes')
                    ->query(fn (Builder $query): Builder => $query->whereIn('type', ['error', 'alert']))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_as_read')
                    ->label('Marcar como Leída')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record): bool => !$record->isRead())
                    ->action(fn ($record) => $record->markAsRead())
                    ->requiresConfirmation(),

                Tables\Actions\Action::make('mark_as_unread')
                    ->label('Marcar como No Leída')
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->visible(fn ($record): bool => $record->isRead())
                    ->action(fn ($record) => $record->update(['read_at' => null]))
                    ->requiresConfirmation(),

                Tables\Actions\Action::make('mark_as_delivered')
                    ->label('Marcar como Entregada')
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->visible(fn ($record): bool => !$record->isDelivered())
                    ->action(fn ($record) => $record->markAsDelivered())
                    ->requiresConfirmation(),

                Tables\Actions\ViewAction::make()
                    ->label('Ver'),

                Tables\Actions\EditAction::make()
                    ->label('Editar'),

                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('mark_as_read')
                        ->label('Marcar como Leídas')
                        ->icon('heroicon-o-check-circle')
                        ->action(function ($records) {
                            $records->each->markAsRead();
                        })
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('mark_as_delivered')
                        ->label('Marcar como Entregadas')
                        ->icon('heroicon-o-truck')
                        ->action(function ($records) {
                            $records->each->markAsDelivered();
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
            'index' => Pages\ListNotifications::route('/'),
            'create' => Pages\CreateNotification::route('/create'),
            'edit' => Pages\EditNotification::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::unread()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
