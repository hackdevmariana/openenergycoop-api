<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BondDonationResource\Pages;
use App\Filament\Resources\BondDonationResource\RelationManagers;
use App\Models\BondDonation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BondDonationResource extends Resource
{
    protected static ?string $model = BondDonation::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Finanzas y Contabilidad';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Donaciones de Bonos';

    protected static ?string $modelLabel = 'Donación de Bono';

    protected static ?string $pluralModelLabel = 'Donaciones de Bonos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información Básica')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('bond_id')
                                    ->label('Bono')
                                    ->relationship('bond', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Bono al que se hace la donación'),
                                Select::make('benefactor_id')
                                    ->label('Benefactor')
                                    ->relationship('benefactor', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Persona o entidad que hace la donación'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                Select::make('donation_type')
                                    ->label('Tipo de Donación')
                                    ->options(BondDonation::getDonationTypes())
                                    ->required()
                                    ->searchable(),
                                Select::make('status')
                                    ->label('Estado')
                                    ->options(BondDonation::getStatuses())
                                    ->required()
                                    ->searchable(),
                                Select::make('priority')
                                    ->label('Prioridad')
                                    ->options(BondDonation::getPriorities())
                                    ->required()
                                    ->searchable(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('amount_kwh')
                                    ->label('Cantidad (kWh)')
                                    ->numeric()
                                    ->minValue(0.001)
                                    ->step(0.001)
                                    ->required()
                                    ->suffix(' kWh')
                                    ->helperText('Cantidad de energía donada'),
                                TextInput::make('monetary_value')
                                    ->label('Valor Monetario (€)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->helperText('Valor monetario equivalente'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Relaciones y Asignaciones')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('organization_id')
                                    ->label('Organización')
                                    ->relationship('organization', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Organización beneficiaria'),
                                Select::make('campaign_id')
                                    ->label('Campaña')
                                    ->relationship('campaign', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Campaña asociada a la donación'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('created_by')
                                    ->label('Creado por')
                                    ->relationship('createdBy', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Select::make('approved_by')
                                    ->label('Aprobado por')
                                    ->relationship('approvedBy', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Detalles de la Donación')
                    ->schema([
                        RichEditor::make('description')
                            ->label('Descripción')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Descripción detallada de la donación'),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('donation_reason')
                                    ->label('Motivo de la Donación')
                                    ->options([
                                        'charity' => 'Caridad',
                                        'emergency' => 'Emergencia',
                                        'community_support' => 'Apoyo Comunitario',
                                        'environmental' => 'Ambiental',
                                        'educational' => 'Educativo',
                                        'health' => 'Salud',
                                        'disaster_relief' => 'Alivio de Desastres',
                                        'other' => 'Otro',
                                    ])
                                    ->searchable(),
                                TextInput::make('impact_category')
                                    ->label('Categoría de Impacto')
                                    ->options([
                                        'local' => 'Local',
                                        'regional' => 'Regional',
                                        'national' => 'Nacional',
                                        'international' => 'Internacional',
                                        'global' => 'Global',
                                    ])
                                    ->searchable(),
                                TextInput::make('urgency_level')
                                    ->label('Nivel de Urgencia')
                                    ->options([
                                        'low' => 'Baja',
                                        'medium' => 'Media',
                                        'high' => 'Alta',
                                        'critical' => 'Crítica',
                                    ])
                                    ->searchable(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('target_beneficiaries')
                                    ->label('Beneficiarios Objetivo')
                                    ->numeric()
                                    ->minValue(1)
                                    ->step(1)
                                    ->helperText('Número de personas que se beneficiarán'),
                                TextInput::make('estimated_impact_duration_days')
                                    ->label('Duración del Impacto (días)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->step(1)
                                    ->suffix(' días')
                                    ->helperText('Cuánto tiempo durará el impacto'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Fechas y Programación')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                DateTimePicker::make('donated_at')
                                    ->label('Donado el')
                                    ->required()
                                    ->default(now())
                                    ->helperText('Cuándo se realizó la donación'),
                                DateTimePicker::make('effective_from')
                                    ->label('Efectivo desde')
                                    ->helperText('Cuándo comienza a ser efectiva'),
                                DateTimePicker::make('effective_until')
                                    ->label('Efectivo hasta')
                                    ->helperText('Cuándo termina de ser efectiva'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('approved_at')
                                    ->label('Aprobado el')
                                    ->helperText('Cuándo se aprobó la donación'),
                                DateTimePicker::make('processed_at')
                                    ->label('Procesado el')
                                    ->helperText('Cuándo se procesó la donación'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Requisitos y Condiciones')
                    ->schema([
                        RichEditor::make('requirements')
                            ->label('Requisitos')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Requisitos para recibir la donación'),
                        RichEditor::make('conditions')
                            ->label('Condiciones')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Condiciones de la donación'),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('requires_verification')
                                    ->label('Requiere Verificación')
                                    ->helperText('¿Se necesita verificar al beneficiario?'),
                                Toggle::make('requires_reporting')
                                    ->label('Requiere Reportes')
                                    ->helperText('¿Se necesita reportar el uso?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('verification_method')
                                    ->label('Método de Verificación')
                                    ->options([
                                        'document_review' => 'Revisión de Documentos',
                                        'site_visit' => 'Visita al Sitio',
                                        'reference_check' => 'Verificación de Referencias',
                                        'financial_audit' => 'Auditoría Financiera',
                                        'other' => 'Otro',
                                    ])
                                    ->searchable(),
                                TextInput::make('reporting_frequency')
                                    ->label('Frecuencia de Reportes')
                                    ->options([
                                        'weekly' => 'Semanal',
                                        'monthly' => 'Mensual',
                                        'quarterly' => 'Trimestral',
                                        'annually' => 'Anual',
                                        'upon_completion' => 'Al Completar',
                                        'other' => 'Otro',
                                    ])
                                    ->searchable(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Impacto y Seguimiento')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('actual_beneficiaries')
                                    ->label('Beneficiarios Reales')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(1)
                                    ->helperText('Número real de beneficiarios'),
                                TextInput::make('impact_score')
                                    ->label('Puntuación de Impacto')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Puntuación del impacto logrado'),
                                TextInput::make('sustainability_score')
                                    ->label('Puntuación de Sostenibilidad')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Puntuación de la sostenibilidad'),
                            ]),
                        RichEditor::make('impact_description')
                            ->label('Descripción del Impacto')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Descripción detallada del impacto logrado'),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('success_metrics')
                                    ->label('Métricas de Éxito')
                                    ->maxLength(255)
                                    ->helperText('Métricas para medir el éxito'),
                                TextInput::make('challenges_faced')
                                    ->label('Desafíos Enfrentados')
                                    ->maxLength(255)
                                    ->helperText('Desafíos encontrados durante la implementación'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Configuración del Sistema')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Activa')
                                    ->default(true)
                                    ->helperText('¿La donación está activa?'),
                                Toggle::make('is_public')
                                    ->label('Pública')
                                    ->helperText('¿La donación es visible públicamente?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('requires_approval')
                                    ->label('Requiere Aprobación')
                                    ->helperText('¿Se necesita aprobación para cambios?'),
                                Toggle::make('is_featured')
                                    ->label('Destacada')
                                    ->helperText('¿Mostrar como donación destacada?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('sort_order')
                                    ->label('Orden de Clasificación')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(1)
                                    ->default(0)
                                    ->helperText('Orden para mostrar en listas'),
                                TextInput::make('visibility_level')
                                    ->label('Nivel de Visibilidad')
                                    ->options([
                                        'public' => 'Público',
                                        'members_only' => 'Solo Miembros',
                                        'organization_only' => 'Solo Organización',
                                        'private' => 'Privado',
                                    ])
                                    ->searchable(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Metadatos y Etiquetas')
                    ->schema([
                        TagsInput::make('tags')
                            ->label('Etiquetas')
                            ->separator(','),
                        RichEditor::make('notes')
                            ->label('Notas')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Documentos y Archivos')
                    ->schema([
                        FileUpload::make('documents')
                            ->label('Documentos')
                            ->multiple()
                            ->directory('bond-donations')
                            ->maxFiles(20)
                            ->maxSize(10240)
                            ->helperText('Máximo 20 documentos de 10MB cada uno'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bond.name')
                    ->label('Bono')
                    ->searchable()
                    ->sortable()
                    ->limit(20),
                TextColumn::make('benefactor.name')
                    ->label('Benefactor')
                    ->searchable()
                    ->sortable()
                    ->limit(20),
                BadgeColumn::make('donation_type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'charity',
                        'success' => 'emergency',
                        'warning' => 'community_support',
                        'danger' => 'environmental',
                        'info' => 'educational',
                        'secondary' => 'health',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => BondDonation::getDonationTypes()[$state] ?? $state),
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'approved',
                        'warning' => 'pending',
                        'danger' => 'rejected',
                        'info' => 'processing',
                        'secondary' => 'completed',
                        'gray' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => BondDonation::getStatuses()[$state] ?? $state),
                BadgeColumn::make('priority')
                    ->label('Prioridad')
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                        'secondary' => 'urgent',
                        'gray' => 'critical',
                    ])
                    ->formatStateUsing(fn (string $state): string => BondDonation::getPriorities()[$state] ?? $state),
                TextColumn::make('amount_kwh')
                    ->label('Cantidad (kWh)')
                    ->suffix(' kWh')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 10000 => 'success',
                        $state >= 5000 => 'primary',
                        $state >= 1000 => 'warning',
                        $state >= 100 => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('monetary_value')
                    ->label('Valor (€)')
                    ->money('EUR')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->money('EUR'),
                    ])
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('target_beneficiaries')
                    ->label('Beneficiarios')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('impact_score')
                    ->label('Impacto (%)')
                    ->suffix('%')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 90 => 'success',
                        $state >= 75 => 'primary',
                        $state >= 50 => 'warning',
                        $state >= 25 => 'info',
                        default => 'danger',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('donated_at')
                    ->label('Donado')
                    ->dateTime()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, '2024') => 'success',
                        str_contains($state, '2025') => 'warning',
                        str_contains($state, '2026') => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('effective_from')
                    ->label('Efectivo desde')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('effective_until')
                    ->label('Efectivo hasta')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_active')
                    ->label('Activa'),
                ToggleColumn::make('is_public')
                    ->label('Pública'),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('donation_type')
                    ->label('Tipo de Donación')
                    ->options(BondDonation::getDonationTypes())
                    ->multiple(),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(BondDonation::getStatuses())
                    ->multiple(),
                SelectFilter::make('priority')
                    ->label('Prioridad')
                    ->options(BondDonation::getPriorities())
                    ->multiple(),
                SelectFilter::make('donation_reason')
                    ->label('Motivo de la Donación')
                    ->options([
                        'charity' => 'Caridad',
                        'emergency' => 'Emergencia',
                        'community_support' => 'Apoyo Comunitario',
                        'environmental' => 'Ambiental',
                        'educational' => 'Educativo',
                        'health' => 'Salud',
                        'disaster_relief' => 'Alivio de Desastres',
                        'other' => 'Otro',
                    ])
                    ->multiple(),
                Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Solo Activas'),
                Filter::make('public')
                    ->query(fn (Builder $query): Builder => $query->where('is_public', true))
                    ->label('Solo Públicas'),
                Filter::make('featured')
                    ->query(fn (Builder $query): Builder => $query->where('is_featured', true))
                    ->label('Solo Destacadas'),
                Filter::make('high_amount')
                    ->query(fn (Builder $query): Builder => $query->where('amount_kwh', '>=', 5000))
                    ->label('Alta Cantidad (≥5000 kWh)'),
                Filter::make('low_amount')
                    ->query(fn (Builder $query): Builder => $query->where('amount_kwh', '<', 100))
                    ->label('Baja Cantidad (<100 kWh)'),
                Filter::make('high_impact')
                    ->query(fn (Builder $query): Builder => $query->where('impact_score', '>=', 80))
                    ->label('Alto Impacto (≥80%)'),
                Filter::make('low_impact')
                    ->query(fn (Builder $query): Builder => $query->where('impact_score', '<', 50))
                    ->label('Bajo Impacto (<50%)'),
                Filter::make('recent_donations')
                    ->query(fn (Builder $query): Builder => $query->where('donated_at', '>=', now()->subDays(30)))
                    ->label('Donaciones Recientes (≤30 días)'),
                Filter::make('urgent_priority')
                    ->query(fn (Builder $query): Builder => $query->whereIn('priority', ['high', 'urgent', 'critical']))
                    ->label('Prioridad Urgente'),
                Filter::make('date_range')
                    ->form([
                        DateTimePicker::make('donated_from')
                            ->label('Donado desde'),
                        DateTimePicker::make('donated_until')
                            ->label('Donado hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['donated_from'],
                                fn (Builder $query, $date): Builder => $query->where('donated_at', '>=', $date),
                            )
                            ->when(
                                $data['donated_until'],
                                fn (Builder $query, $date): Builder => $query->where('donated_at', '<=', $date),
                            );
                    })
                    ->label('Rango de Fechas de Donación'),
                Filter::make('amount_range')
                    ->form([
                        TextInput::make('min_amount')
                            ->label('Cantidad mínima (kWh)')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('max_amount')
                            ->label('Cantidad máxima (kWh)')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_amount'],
                                fn (Builder $query, $amount): Builder => $query->where('amount_kwh', '>=', $amount),
                            )
                            ->when(
                                $data['max_amount'],
                                fn (Builder $query, $amount): Builder => $query->where('amount_kwh', '<=', $amount),
                            );
                    })
                    ->label('Rango de Cantidades'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->color('info'),
                Tables\Actions\EditAction::make()
                    ->color('warning'),
                Tables\Actions\DeleteAction::make()
                    ->color('danger'),
                Tables\Actions\Action::make('approve_donation')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (BondDonation $record) => $record->status === 'pending')
                    ->action(function (BondDonation $record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_at' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('process_donation')
                    ->label('Procesar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->visible(fn (BondDonation $record) => $record->status === 'approved')
                    ->action(function (BondDonation $record) {
                        $record->update([
                            'status' => 'processing',
                            'processed_at' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('complete_donation')
                    ->label('Completar')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (BondDonation $record) => $record->status === 'processing')
                    ->action(function (BondDonation $record) {
                        $record->update([
                            'status' => 'completed',
                        ]);
                    }),
                Tables\Actions\Action::make('reject_donation')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (BondDonation $record) => $record->status === 'pending')
                    ->action(function (BondDonation $record) {
                        $record->update([
                            'status' => 'rejected',
                        ]);
                    }),
                Tables\Actions\Action::make('mark_featured')
                    ->label('Destacar')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (BondDonation $record) => !$record->is_featured)
                    ->action(function (BondDonation $record) {
                        $record->update(['is_featured' => true]);
                    }),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (BondDonation $record) {
                        $newRecord = $record->replicate();
                        $newRecord->status = 'pending';
                        $newRecord->donated_at = now();
                        $newRecord->approved_at = null;
                        $newRecord->processed_at = null;
                        $newRecord->is_featured = false;
                        $newRecord->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approve_all')
                        ->label('Aprobar Todas')
                        ->icon('heroicon-o-check-badge')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->status === 'pending') {
                                    $record->update([
                                        'status' => 'approved',
                                        'approved_at' => now(),
                                    ]);
                                }
                            });
                        }),
                    Tables\Actions\BulkAction::make('process_all')
                        ->label('Procesar Todas')
                        ->icon('heroicon-o-arrow-path')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->status === 'approved') {
                                    $record->update([
                                        'status' => 'processing',
                                        'processed_at' => now(),
                                    ]);
                                }
                            });
                        }),
                    Tables\Actions\BulkAction::make('complete_all')
                        ->label('Completar Todas')
                        ->icon('heroicon-o-check')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->status === 'processing') {
                                    $record->update(['status' => 'completed']);
                                }
                            });
                        }),
                    Tables\Actions\BulkAction::make('mark_featured_all')
                        ->label('Destacar Todas')
                        ->icon('heroicon-o-star')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_featured' => true]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_status')
                        ->label('Actualizar Estado')
                        ->icon('heroicon-o-flag')
                        ->form([
                            Select::make('status')
                                ->label('Estado')
                                ->options(BondDonation::getStatuses())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['status' => $data['status']]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_priority')
                        ->label('Actualizar Prioridad')
                        ->icon('heroicon-o-flag')
                        ->form([
                            Select::make('priority')
                                ->label('Prioridad')
                                ->options(BondDonation::getPriorities())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['priority' => $data['priority']]);
                            });
                        }),
                ]),
            ])
            ->defaultSort('donated_at', 'desc')
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
            'index' => Pages\ListBondDonations::route('/'),
            'create' => Pages\CreateBondDonation::route('/create'),
            'edit' => Pages\EditBondDonation::route('/{record}/edit'),
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
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $pendingCount = static::getModel()::where('status', 'pending')->count();
        
        if ($pendingCount > 0) {
            return 'warning';
        }
        
        $urgentCount = static::getModel()::whereIn('priority', ['high', 'urgent', 'critical'])->count();
        
        if ($urgentCount > 0) {
            return 'danger';
        }
        
        return 'success';
    }
}
