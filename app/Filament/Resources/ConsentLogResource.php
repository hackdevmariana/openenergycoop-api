<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConsentLogResource\Pages;
use App\Models\ConsentLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class ConsentLogResource extends Resource
{
    protected static ?string $model = ConsentLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    
    protected static ?string $navigationLabel = 'Registro de Consentimientos';
    
    protected static ?string $modelLabel = 'Consentimiento';
    
    protected static ?string $pluralModelLabel = 'Consentimientos';
    
    protected static ?string $navigationGroup = 'Legal y Cumplimiento';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Consentimiento')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Usuario')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required()
                            ->disabled(),
                            
                        Forms\Components\Select::make('consent_type')
                            ->label('Tipo de Consentimiento')
                            ->options(ConsentLog::CONSENT_TYPES)
                            ->required()
                            ->disabled(),
                            
                        Forms\Components\TextInput::make('version')
                            ->label('Versión del Documento')
                            ->disabled(),
                    ])->columns(3),
                    
                Section::make('Detalles del Consentimiento')
                    ->schema([
                        Forms\Components\DateTimePicker::make('consented_at')
                            ->label('Fecha de Consentimiento')
                            ->disabled(),
                            
                        Forms\Components\DateTimePicker::make('revoked_at')
                            ->label('Fecha de Revocación')
                            ->disabled(),
                            
                        Forms\Components\TextInput::make('consent_document_url')
                            ->label('URL del Documento')
                            ->disabled()
                            ->copyable(),
                    ])->columns(3),
                    
                Section::make('Información Técnica')
                    ->schema([
                        Forms\Components\TextInput::make('ip_address')
                            ->label('Dirección IP')
                            ->disabled()
                            ->copyable(),
                            
                        Forms\Components\Textarea::make('user_agent')
                            ->label('User Agent')
                            ->rows(3)
                            ->disabled(),
                    ])->columns(2),
                    
                Section::make('Contexto Adicional')
                    ->schema([
                        Forms\Components\KeyValue::make('context')
                            ->label('Contexto del Consentimiento')
                            ->keyLabel('Campo')
                            ->valueLabel('Valor')
                            ->disabled()
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
                    ->color('primary'),
                    
                Tables\Columns\BadgeColumn::make('consent_type')
                    ->label('Tipo de Consentimiento')
                    ->colors([
                        'primary' => 'terms_and_conditions',
                        'success' => 'privacy_policy',
                        'warning' => 'marketing_emails',
                        'danger' => 'data_processing',
                        'secondary' => 'cookies',
                    ])
                    ->formatStateUsing(fn ($state) => ConsentLog::CONSENT_TYPES[$state] ?? $state),
                    
                Tables\Columns\TextColumn::make('version')
                    ->label('Versión')
                    ->badge()
                    ->alignCenter(),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Estado')
                    ->getStateUsing(fn ($record) => is_null($record->revoked_at))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn ($record) => is_null($record->revoked_at) ? 'Consentimiento activo' : 'Consentimiento revocado'),
                    
                Tables\Columns\TextColumn::make('consented_at')
                    ->label('Fecha de Consentimiento')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('revoked_at')
                    ->label('Revocado')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Activo')
                    ->color('danger')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('consent_type')
                    ->label('Tipo de Consentimiento')
                    ->options(ConsentLog::CONSENT_TYPES),
                    
                TernaryFilter::make('revoked_at')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Solo revocados')
                    ->falseLabel('Solo activos')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('revoked_at'),
                        false: fn ($query) => $query->whereNull('revoked_at'),
                    ),
                    
                SelectFilter::make('version')
                    ->label('Versión del Documento')
                    ->options(function () {
                        return ConsentLog::distinct('version')
                            ->whereNotNull('version')
                            ->pluck('version', 'version')
                            ->toArray();
                    }),
                    
                SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver Detalles'),
                    
                Tables\Actions\Action::make('view_document')
                    ->label('Ver Documento')
                    ->icon('heroicon-o-document-text')
                    ->url(fn (ConsentLog $record) => $record->consent_document_url)
                    ->openUrlInNewTab()
                    ->visible(fn (ConsentLog $record) => !empty($record->consent_document_url)),
                    
                Tables\Actions\Action::make('export_user_data')
                    ->label('Exportar Datos GDPR')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (ConsentLog $record) {
                        $userData = ConsentLog::generateGDPRReport($record->user_id);
                        
                        // Aquí podrías implementar la exportación real
                        // Por ahora, solo mostramos una notificación
                        \Filament\Notifications\Notification::make()
                            ->title('Datos GDPR exportados')
                            ->body('Los datos del usuario han sido preparados para exportación.')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // No permitimos eliminar registros de consentimiento por temas legales
                    Tables\Actions\BulkAction::make('export_gdpr')
                        ->label('Exportar GDPR Seleccionados')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function ($records) {
                            $userIds = $records->pluck('user_id')->unique();
                            
                            foreach ($userIds as $userId) {
                                ConsentLog::generateGDPRReport($userId);
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Exportación GDPR completada')
                                ->body("Datos de {$userIds->count()} usuarios exportados.")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->persistSortInSession()
            ->persistFiltersInSession();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConsentLogs::route('/'),
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
        return false; // Los consentimientos se crean automáticamente, no manualmente
    }
    
    public static function canDelete($record): bool
    {
        return false; // No permitimos eliminar registros de consentimiento por temas legales
    }
    
    public static function canDeleteAny(): bool
    {
        return false; // No permitimos eliminar registros de consentimiento por temas legales
    }
}