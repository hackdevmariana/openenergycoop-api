<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Collection;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Eventos';
    protected static ?string $modelLabel = 'Evento';
    protected static ?string $pluralModelLabel = 'Eventos';
    protected static ?string $navigationGroup = 'Eventos y Proyectos';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Evento')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej: Conferencia sobre Energía Renovable'),
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->required()
                            ->rows(4)
                            ->placeholder('Descripción detallada del evento...')
                            ->columnSpanFull(),
                        Forms\Components\DateTimePicker::make('date')
                            ->label('Fecha y Hora')
                            ->required()
                            ->displayFormat('d/m/Y H:i')
                            ->native(false)
                            ->seconds(false),
                        Forms\Components\TextInput::make('location')
                            ->label('Ubicación')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej: Auditorio Principal, Calle Mayor 123'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Configuración')
                    ->schema([
                        Forms\Components\Select::make('organization_id')
                            ->label('Organización')
                            ->relationship('organization', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('language')
                            ->label('Idioma')
                            ->options([
                                'es' => 'Español',
                                'en' => 'English',
                                'ca' => 'Català',
                                'eu' => 'Euskara',
                                'gl' => 'Galego',
                            ])
                            ->default('es')
                            ->required(),
                        Forms\Components\Toggle::make('public')
                            ->label('Evento Público')
                            ->helperText('Los eventos públicos son visibles para todos los usuarios')
                            ->default(true),
                        Forms\Components\Toggle::make('is_draft')
                            ->label('Es Borrador')
                            ->helperText('Los borradores no son visibles para los usuarios')
                            ->default(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Estadísticas de Asistencia')
                    ->schema([
                        Forms\Components\Placeholder::make('total_registered')
                            ->label('Total Registrados')
                            ->content(fn (Event $record): string => $record?->attendances()->where('status', 'registered')->count() ?? '0'),
                        Forms\Components\Placeholder::make('total_attended')
                            ->label('Total Asistieron')
                            ->content(fn (Event $record): string => $record?->attendances()->where('status', 'attended')->count() ?? '0'),
                        Forms\Components\Placeholder::make('attendance_rate')
                            ->label('Tasa de Asistencia')
                            ->content(fn (Event $record): string => $record ? round($record->getAttendanceStatsAttribute()['attendance_rate'], 1) . '%' : '0%'),
                    ])
                    ->columns(3)
                    ->visible(fn (?Event $record) => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha y Hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->badge()
                    ->color(fn (Event $record): string => match (true) {
                        $record->isPast() => 'danger',
                        $record->isToday() => 'warning',
                        $record->isUpcoming() => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('location')
                    ->label('Ubicación')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('organization.name')
                    ->label('Organización')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('language')
                    ->label('Idioma')
                    ->colors([
                        'primary' => 'es',
                        'success' => 'en',
                        'warning' => 'ca',
                        'info' => 'eu',
                        'secondary' => 'gl',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'es' => 'ES',
                        'en' => 'EN',
                        'ca' => 'CA',
                        'eu' => 'EU',
                        'gl' => 'GL',
                        default => $state,
                    }),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'info' => 'upcoming',
                        'success' => 'ongoing',
                        'secondary' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (Event $record): string => match ($record->status) {
                        'upcoming' => 'Próximo',
                        'ongoing' => 'En Curso',
                        'completed' => 'Finalizado',
                        'cancelled' => 'Cancelado',
                        default => $record->status,
                    }),
                Tables\Columns\IconColumn::make('public')
                    ->label('Público')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\IconColumn::make('is_draft')
                    ->label('Borrador')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-text')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('warning')
                    ->falseColor('success'),
                Tables\Columns\TextColumn::make('attendances_count')
                    ->label('Asistentes')
                    ->counts('attendances')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'upcoming' => 'Próximo',
                        'ongoing' => 'En Curso',
                        'completed' => 'Finalizado',
                        'cancelled' => 'Cancelado',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value']) {
                        'upcoming' => $query->upcoming(),
                        'ongoing' => $query->where('date', '<=', now())->where('date', '>=', now()->subHours(2)),
                        'completed' => $query->past(),
                        'cancelled' => $query->onlyTrashed(),
                        default => $query,
                    }),
                Tables\Filters\SelectFilter::make('public')
                    ->label('Visibilidad')
                    ->options([
                        '1' => 'Público',
                        '0' => 'Privado',
                    ]),
                Tables\Filters\SelectFilter::make('is_draft')
                    ->label('Estado de Publicación')
                    ->options([
                        '0' => 'Publicado',
                        '1' => 'Borrador',
                    ]),
                Tables\Filters\SelectFilter::make('language')
                    ->label('Idioma')
                    ->options([
                        'es' => 'Español',
                        'en' => 'English',
                        'ca' => 'Català',
                        'eu' => 'Euskara',
                        'gl' => 'Galego',
                    ]),
                Tables\Filters\SelectFilter::make('organization_id')
                    ->label('Organización')
                    ->relationship('organization', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('date_range')
                    ->label('Rango de Fechas')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
                Tables\Filters\Filter::make('search')
                    ->label('Búsqueda')
                    ->form([
                        Forms\Components\TextInput::make('search')
                            ->label('Buscar en título, descripción o ubicación')
                            ->placeholder('Texto a buscar...'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['search'],
                            fn (Builder $query, $search): Builder => $query->search($search),
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view_attendances')
                    ->label('Ver Asistentes')
                    ->icon('heroicon-o-users')
                    ->color('info')
                    ->url(fn (Event $record): string => route('filament.admin.resources.events.edit', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('toggle_public')
                    ->label(fn (Event $record): string => $record->public ? 'Hacer Privado' : 'Hacer Público')
                    ->icon(fn (Event $record): string => $record->public ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Event $record): string => $record->public ? 'warning' : 'success')
                    ->action(function (Event $record): void {
                        $record->update(['public' => !$record->public]);
                    })
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('toggle_draft')
                    ->label(fn (Event $record): string => $record->is_draft ? 'Publicar' : 'Marcar como Borrador')
                    ->icon(fn (Event $record): string => $record->is_draft ? 'heroicon-o-check-circle' : 'heroicon-o-document-text')
                    ->color(fn (Event $record): string => $record->is_draft ? 'success' : 'warning')
                    ->action(function (Event $record): void {
                        $record->update(['is_draft' => !$record->is_draft]);
                    })
                    ->requiresConfirmation(),
                Tables\Actions\EditAction::make()
                    ->label('Editar'),
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('make_public')
                        ->label('Hacer Públicos')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each(function ($record) {
                                $record->update(['public' => true]);
                            });
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('make_private')
                        ->label('Hacer Privados')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each(function ($record) {
                                $record->update(['public' => false]);
                            });
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publicar')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each(function ($record) {
                                $record->update(['is_draft' => false]);
                            });
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('mark_as_draft')
                        ->label('Marcar como Borrador')
                        ->icon('heroicon-o-document-text')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each(function ($record) {
                                $record->update(['is_draft' => true]);
                            });
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar Seleccionados'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('attendances')
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $todayEvents = static::getModel()::whereDate('date', today())->count();
        $upcomingEvents = static::getModel()::upcoming()->count();
        $draftEvents = static::getModel()::where('is_draft', true)->count();
        
        // Prioridad: eventos de hoy > eventos próximos > borradores > total
        if ($todayEvents > 0) {
            return $todayEvents; // Muestra eventos de hoy
        }
        
        if ($upcomingEvents > 0) {
            return $upcomingEvents; // Muestra eventos próximos
        }
        
        if ($draftEvents > 0) {
            return $draftEvents; // Muestra borradores
        }
        
        return static::getModel()::count(); // Muestra total
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $upcomingEvents = static::getModel()::upcoming()->count();
        $todayEvents = static::getModel()::whereDate('date', today())->count();
        $draftEvents = static::getModel()::where('is_draft', true)->count();
        
        if ($todayEvents > 0) {
            return 'warning'; // Eventos de hoy - amarillo
        }
        
        if ($upcomingEvents > 0) {
            return 'success'; // Eventos próximos - verde
        }
        
        if ($draftEvents > 0) {
            return 'info'; // Hay borradores - azul
        }
        
        return 'gray'; // Sin eventos próximos - gris
    }
}
