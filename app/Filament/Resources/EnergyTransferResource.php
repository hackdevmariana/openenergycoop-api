<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnergyTransferResource\Pages;
use App\Filament\Resources\EnergyTransferResource\RelationManagers;
use App\Models\EnergyTransfer;
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


class EnergyTransferResource extends Resource
{
    protected static ?string $model = EnergyTransfer::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static ?string $navigationGroup = 'Energía y Comercio';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Transferencias de Energía';

    protected static ?string $modelLabel = 'Transferencia de Energía';

    protected static ?string $pluralModelLabel = 'Transferencias de Energía';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información Básica')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('transfer_number')
                                    ->label('Número de Transferencia')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Número único de identificación'),
                                TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Nombre descriptivo de la transferencia'),
                            ]),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->helperText('Descripción detallada de la transferencia'),
                        Grid::make(3)
                            ->schema([
                                Select::make('transfer_type')
                                    ->label('Tipo de Transferencia')
                                    ->options(EnergyTransfer::getTransferTypes())
                                    ->required()
                                    ->searchable(),
                                Select::make('status')
                                    ->label('Estado')
                                    ->options(EnergyTransfer::getStatuses())
                                    ->required()
                                    ->searchable(),
                                Select::make('priority')
                                    ->label('Prioridad')
                                    ->options(EnergyTransfer::getPriorities())
                                    ->required()
                                    ->searchable(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('transfer_amount_kwh')
                                    ->label('Cantidad (kWh)')
                                    ->numeric()
                                    ->minValue(0.001)
                                    ->step(0.001)
                                    ->required()
                                    ->suffix(' kWh')
                                    ->helperText('Cantidad de energía a transferir'),
                                TextInput::make('transfer_rate_kw')
                                    ->label('Tasa de Transferencia (kW)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' kW')
                                    ->helperText('Potencia de transferencia'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('duration_hours')
                                    ->label('Duración (horas)')
                                    ->numeric()
                                    ->minValue(0.1)
                                    ->step(0.1)
                                    ->suffix(' h')
                                    ->helperText('Duración de la transferencia'),
                                TextInput::make('efficiency_percentage')
                                    ->label('Eficiencia (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Eficiencia de la transferencia'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Origen y Destino')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('source_id')
                                    ->label('ID Origen')
                                    ->numeric()
                                    ->helperText('ID del origen de la transferencia'),
                                Select::make('source_type')
                                    ->label('Tipo Origen')
                                    ->options([
                                        'App\Models\EnergyInstallation' => 'Instalación',
                                        'App\Models\EnergyCooperative' => 'Cooperativa',
                                        'App\Models\EnergyStorage' => 'Almacenamiento',
                                    ])
                                    ->searchable()
                                    ->helperText('Tipo de origen'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('destination_id')
                                    ->label('ID Destino')
                                    ->numeric()
                                    ->helperText('ID del destino de la transferencia'),
                                Select::make('destination_type')
                                    ->label('Tipo Destino')
                                    ->options([
                                        'App\Models\EnergyInstallation' => 'Instalación',
                                        'App\Models\EnergyCooperative' => 'Cooperativa',
                                        'App\Models\EnergyStorage' => 'Almacenamiento',
                                    ])
                                    ->searchable()
                                    ->helperText('Tipo de destino'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('source_meter_id')
                                    ->label('Medidor Origen')
                                    ->relationship('sourceMeter', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Medidor de origen'),
                                Select::make('destination_meter_id')
                                    ->label('Medidor Destino')
                                    ->relationship('destinationMeter', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Medidor de destino'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Usuarios Responsables')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('scheduled_by')
                                    ->label('Programado por')
                                    ->relationship('scheduledBy', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Usuario que programó la transferencia'),
                                Select::make('initiated_by')
                                    ->label('Iniciado por')
                                    ->relationship('initiatedBy', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Usuario que inició la transferencia'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('approved_by')
                                    ->label('Aprobado por')
                                    ->relationship('approvedBy', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Usuario que aprobó la transferencia'),
                                Select::make('verified_by')
                                    ->label('Verificado por')
                                    ->relationship('verifiedBy', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Usuario que verificó la transferencia'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('completed_by')
                                    ->label('Completado por')
                                    ->relationship('completedBy', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Usuario que completó la transferencia'),
                                Select::make('created_by')
                                    ->label('Creado por')
                                    ->relationship('createdBy', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Usuario que creó el registro'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Detalles de la Transferencia')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3)
                            ->helperText('Notas adicionales sobre la transferencia'),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('cost_per_kwh')
                                    ->label('Costo por kWh (€)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.0001)
                                    ->prefix('€')
                                    ->helperText('Costo por kilovatio hora'),
                                TextInput::make('total_cost')
                                    ->label('Costo Total (€)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->helperText('Costo total de la transferencia'),
                                TextInput::make('currency')
                                    ->label('Moneda')
                                    ->default('EUR')
                                    ->maxLength(3)
                                    ->helperText('Moneda del costo'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('energy_quality')
                                    ->label('Calidad de Energía')
                                    ->options([
                                        'premium' => 'Premium',
                                        'standard' => 'Estándar',
                                        'basic' => 'Básica',
                                        'renewable' => 'Renovable',
                                        'mixed' => 'Mixta',
                                        'other' => 'Otra',
                                    ])
                                    ->searchable(),
                                TextInput::make('carbon_footprint')
                                    ->label('Huella de Carbono (kg CO2/kWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' kg CO2/kWh')
                                    ->helperText('Emisiones de CO2 por kWh'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Seguridad y Verificación')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('verification_code')
                                    ->label('Código de Verificación')
                                    ->maxLength(6)
                                    ->helperText('Código de 6 dígitos para verificar'),
                                Toggle::make('requires_2fa')
                                    ->label('Requiere 2FA')
                                    ->default(true)
                                    ->required()
                                    ->helperText('¿Se requiere autenticación de dos factores?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('admin_approval_required')
                                    ->label('Requiere Aprobación de Admin')
                                    ->default(false)
                                    ->required()
                                    ->helperText('¿Se necesita aprobación administrativa?'),
                                Toggle::make('is_verified')
                                    ->label('Verificado')
                                    ->default(false)
                                    ->helperText('¿La transferencia ha sido verificada?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('security_level')
                                    ->label('Nivel de Seguridad')
                                    ->options([
                                        'low' => 'Bajo',
                                        'medium' => 'Medio',
                                        'high' => 'Alto',
                                        'critical' => 'Crítico',
                                    ])
                                    ->searchable(),
                                TextInput::make('encryption_method')
                                    ->label('Método de Encriptación')
                                    ->options([
                                        'aes_256' => 'AES-256',
                                        'aes_128' => 'AES-128',
                                        'rsa_2048' => 'RSA-2048',
                                        'rsa_4096' => 'RSA-4096',
                                        'other' => 'Otro',
                                    ])
                                    ->searchable(),
                            ]),
                        RichEditor::make('security_notes')
                            ->label('Notas de Seguridad')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Notas específicas sobre seguridad'),
                    ])
                    ->collapsible(),

                Section::make('Fechas y Programación')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                DateTimePicker::make('scheduled_start_time')
                                    ->label('Inicio Programado')
                                    ->required()
                                    ->helperText('Cuándo está programado el inicio'),
                                DateTimePicker::make('scheduled_end_time')
                                    ->label('Fin Programado')
                                    ->required()
                                    ->helperText('Cuándo está programado el fin'),
                                DateTimePicker::make('actual_start_time')
                                    ->label('Inicio Real')
                                    ->helperText('Cuándo comenzó realmente'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                DateTimePicker::make('actual_end_time')
                                    ->label('Fin Real')
                                    ->helperText('Cuándo terminó realmente'),
                                DateTimePicker::make('completion_time')
                                    ->label('Tiempo de Completado')
                                    ->helperText('Cuándo se completó la transferencia'),
                                DateTimePicker::make('approved_at')
                                    ->label('Aprobado el')
                                    ->helperText('Cuándo se aprobó la transferencia'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('verified_at')
                                    ->label('Verificado el')
                                    ->helperText('Cuándo se verificó la transferencia'),
                                DateTimePicker::make('completed_at')
                                    ->label('Completado el')
                                    ->helperText('Cuándo se marcó como completado'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Monitoreo y Estado')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('loss_percentage')
                                    ->label('Pérdidas (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Porcentaje de pérdidas'),
                                TextInput::make('loss_amount_kwh')
                                    ->label('Pérdidas (kWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' kWh')
                                    ->helperText('Cantidad perdida en kWh'),
                                TextInput::make('net_transfer_amount_kwh')
                                    ->label('Transferencia Neta (kWh)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' kWh')
                                    ->helperText('Cantidad neta transferida'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('transfer_method')
                                    ->label('Método de Transferencia')
                                    ->maxLength(255)
                                    ->helperText('Método utilizado para la transferencia'),
                                TextInput::make('transfer_medium')
                                    ->label('Medio de Transferencia')
                                    ->maxLength(255)
                                    ->helperText('Medio utilizado para la transferencia'),
                            ]),
                        RichEditor::make('monitoring_data')
                            ->label('Datos de Monitoreo')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'codeBlock',
                            ])
                            ->helperText('Datos de monitoreo en tiempo real'),
                    ])
                    ->collapsible(),

                Section::make('Configuración del Sistema')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Activa')
                                    ->default(true)
                                    ->helperText('¿La transferencia está activa?'),
                                Toggle::make('is_public')
                                    ->label('Pública')
                                    ->helperText('¿La transferencia es visible públicamente?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('auto_retry')
                                    ->label('Reintento Automático')
                                    ->helperText('¿Se reintenta automáticamente si falla?'),
                                Toggle::make('notifications_enabled')
                                    ->label('Notificaciones Habilitadas')
                                    ->default(true)
                                    ->helperText('¿Se envían notificaciones?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('max_retry_attempts')
                                    ->label('Máximo de Reintentos')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(10)
                                    ->step(1)
                                    ->default(3)
                                    ->helperText('Número máximo de reintentos'),
                                TextInput::make('retry_delay_minutes')
                                    ->label('Retraso entre Reintentos (min)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(1440)
                                    ->step(1)
                                    ->default(15)
                                    ->suffix(' min')
                                    ->helperText('Tiempo de espera entre reintentos'),
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
                            ->directory('energy-transfers')
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
                TextColumn::make('source_display')
                    ->label('EMISOR')
                    ->getStateUsing(function (EnergyTransfer $record): string {
                        if (!$record->source_id || !$record->source_type) {
                            return 'Sin origen';
                        }
                        
                        $sourceModel = $record->source_type::find($record->source_id);
                        if (!$sourceModel) {
                            return 'Origen no encontrado';
                        }
                        
                        $name = $sourceModel->name ?? $sourceModel->title ?? "ID: {$record->source_id}";
                        $type = class_basename($record->source_type);
                        
                        return "{$name} ({$type})";
                    })
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('success')
                    ->limit(30)
                    ->tooltip(function (EnergyTransfer $record): string {
                        if (!$record->source_id || !$record->source_type) {
                            return 'Sin información de origen';
                        }
                        return "Tipo: " . class_basename($record->source_type) . "\nID: {$record->source_id}";
                    }),
                TextColumn::make('destination_display')
                    ->label('RECEPTOR')
                    ->getStateUsing(function (EnergyTransfer $record): string {
                        if (!$record->destination_id || !$record->destination_type) {
                            return 'Sin destino';
                        }
                        
                        $destinationModel = $record->destination_type::find($record->destination_id);
                        if (!$destinationModel) {
                            return 'Destino no encontrado';
                        }
                        
                        $name = $destinationModel->name ?? $destinationModel->title ?? "ID: {$record->destination_id}";
                        $type = class_basename($record->destination_type);
                        
                        return "{$name} ({$type})";
                    })
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('warning')
                    ->limit(30)
                    ->tooltip(function (EnergyTransfer $record): string {
                        if (!$record->destination_id || !$record->destination_type) {
                            return 'Sin información de destino';
                        }
                        return "Tipo: " . class_basename($record->destination_type) . "\nID: {$record->destination_id}";
                    }),
                TextColumn::make('transfer_number')
                    ->label('Número')
                    ->searchable()
                    ->sortable()
                    ->limit(15),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                BadgeColumn::make('transfer_type')
                    ->label('Tipo')
                    ->colors([
                        'success' => 'generation',
                        'danger' => 'consumption',
                        'info' => 'storage',
                        'warning' => 'grid_import',
                        'primary' => 'grid_export',
                        'secondary' => 'peer_to_peer',
                        'gray' => 'virtual',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyTransfer::getTransferTypes()[$state] ?? $state),
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'completed',
                        'warning' => 'in_progress',
                        'danger' => 'cancelled',
                        'info' => 'pending',
                        'secondary' => 'scheduled',
                        'gray' => 'failed',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyTransfer::getStatuses()[$state] ?? $state),
                BadgeColumn::make('priority')
                    ->label('Prioridad')
                    ->colors([
                        'success' => 'low',
                        'warning' => 'normal',
                        'danger' => 'high',
                        'secondary' => 'urgent',
                        'gray' => 'critical',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyTransfer::getPriorities()[$state] ?? $state),
                TextColumn::make('transfer_amount_kwh')
                    ->label('ENERGÍA TRANSFERIDA')
                    ->suffix(' kWh')
                    ->numeric()
                    ->sortable()
                    ->weight('bold')
                    ->size('lg')
                    ->color(fn (string $state): string => match (true) {
                        $state >= 1000 => 'success',
                        $state >= 500 => 'primary',
                        $state >= 100 => 'warning',
                        $state >= 10 => 'info',
                        default => 'gray',
                    })
                    ->tooltip('Cantidad de energía transferida en kilovatios hora'),
                TextColumn::make('efficiency_percentage')
                    ->label('Eficiencia (%)')
                    ->suffix('%')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 95 => 'success',
                        $state >= 90 => 'primary',
                        $state >= 85 => 'warning',
                        $state >= 80 => 'info',
                        default => 'danger',
                    }),
                TextColumn::make('total_cost')
                    ->label('Costo Total (€)')
                    ->money('EUR')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->money('EUR'),
                    ])
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('transfer_rate_kw')
                    ->label('Potencia (kW)')
                    ->suffix(' kW')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('duration_hours')
                    ->label('Duración (h)')
                    ->suffix(' h')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('responsible_users')
                    ->label('RESPONSABLES')
                    ->getStateUsing(function (EnergyTransfer $record): string {
                        $users = [];
                        
                        if ($record->scheduledBy) {
                            $users[] = "Prog: {$record->scheduledBy->name}";
                        }
                        if ($record->initiatedBy) {
                            $users[] = "Inic: {$record->initiatedBy->name}";
                        }
                        if ($record->createdBy) {
                            $users[] = "Creado: {$record->createdBy->name}";
                        }
                        
                        return implode(' | ', $users) ?: 'Sin responsables';
                    })
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->weight('bold')
                    ->color('info')
                    ->tooltip(function (EnergyTransfer $record): string {
                        $details = [];
                        if ($record->scheduledBy) $details[] = "Programado por: {$record->scheduledBy->name}";
                        if ($record->initiatedBy) $details[] = "Iniciado por: {$record->initiatedBy->name}";
                        if ($record->approvedBy) $details[] = "Aprobado por: {$record->approvedBy->name}";
                        if ($record->verifiedBy) $details[] = "Verificado por: {$record->verifiedBy->name}";
                        if ($record->completedBy) $details[] = "Completado por: {$record->completedBy->name}";
                        if ($record->createdBy) $details[] = "Creado por: {$record->createdBy->name}";
                        
                        return implode("\n", $details) ?: 'Sin información de responsables';
                    }),
                ToggleColumn::make('is_automated')
                    ->label('Automático'),
                ToggleColumn::make('requires_approval')
                    ->label('Requiere Aprobación'),
                ToggleColumn::make('is_approved')
                    ->label('Aprobado'),
                ToggleColumn::make('is_verified')
                    ->label('Verificado'),
                TextColumn::make('scheduled_start_time')
                    ->label('Inicio Programado')
                    ->dateTime()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, '2024') => 'success',
                        str_contains($state, '2025') => 'warning',
                        str_contains($state, '2026') => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('actual_start_time')
                    ->label('Inicio Real')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('completion_time')
                    ->label('Completado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('by_source')
                    ->form([
                        TextInput::make('source_name')
                            ->label('Buscar por Emisor')
                            ->placeholder('Nombre del emisor...')
                            ->helperText('Busca por nombre del emisor'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!$data['source_name']) {
                            return $query;
                        }
                        
                        return $query->where(function ($q) use ($data) {
                            $searchTerm = $data['source_name'];
                            
                            // Buscar en diferentes tipos de modelos origen
                            $q->whereHasMorph('source', [
                                'App\Models\EnergyInstallation',
                                'App\Models\EnergyCooperative', 
                                'App\Models\EnergyStorage'
                            ], function ($query, $type) use ($searchTerm) {
                                $query->where('name', 'like', "%{$searchTerm}%");
                            });
                        });
                    })
                    ->label('Filtrar por Emisor'),
                Filter::make('by_destination')
                    ->form([
                        TextInput::make('destination_name')
                            ->label('Buscar por Receptor')
                            ->placeholder('Nombre del receptor...')
                            ->helperText('Busca por nombre del receptor'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!$data['destination_name']) {
                            return $query;
                        }
                        
                        return $query->where(function ($q) use ($data) {
                            $searchTerm = $data['destination_name'];
                            
                            // Buscar en diferentes tipos de modelos destino
                            $q->whereHasMorph('destination', [
                                'App\Models\EnergyInstallation',
                                'App\Models\EnergyCooperative', 
                                'App\Models\EnergyStorage'
                            ], function ($query, $type) use ($searchTerm) {
                                $query->where('name', 'like', "%{$searchTerm}%");
                            });
                        });
                    })
                    ->label('Filtrar por Receptor'),
                SelectFilter::make('transfer_type')
                    ->label('Tipo de Transferencia')
                    ->options(EnergyTransfer::getTransferTypes())
                    ->multiple(),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(EnergyTransfer::getStatuses())
                    ->multiple(),
                SelectFilter::make('priority')
                    ->label('Prioridad')
                    ->options(EnergyTransfer::getPriorities())
                    ->multiple(),
                SelectFilter::make('energy_quality')
                    ->label('Calidad de Energía')
                    ->options([
                        'premium' => 'Premium',
                        'standard' => 'Estándar',
                        'basic' => 'Básica',
                        'renewable' => 'Renovable',
                        'mixed' => 'Mixta',
                        'other' => 'Otra',
                    ])
                    ->multiple(),
                Filter::make('automated')
                    ->query(fn (Builder $query): Builder => $query->where('is_automated', true))
                    ->label('Solo Automáticas'),
                Filter::make('verified')
                    ->query(fn (Builder $query): Builder => $query->where('is_verified', true))
                    ->label('Solo Verificadas'),
                Filter::make('requires_approval')
                    ->query(fn (Builder $query): Builder => $query->where('requires_approval', true))
                    ->label('Requieren Aprobación'),
                Filter::make('approved')
                    ->query(fn (Builder $query): Builder => $query->where('is_approved', true))
                    ->label('Solo Aprobadas'),
                Filter::make('high_amount')
                    ->query(fn (Builder $query): Builder => $query->where('transfer_amount_kwh', '>=', 1000))
                    ->label('Alta Cantidad (≥1000 kWh)'),
                Filter::make('low_amount')
                    ->query(fn (Builder $query): Builder => $query->where('transfer_amount_kwh', '<', 100))
                    ->label('Baja Cantidad (<100 kWh)'),
                Filter::make('high_cost')
                    ->query(fn (Builder $query): Builder => $query->where('total_cost', '>=', 100))
                    ->label('Alto Costo (≥€100)'),
                Filter::make('low_cost')
                    ->query(fn (Builder $query): Builder => $query->where('total_cost', '<', 10))
                    ->label('Bajo Costo (<€10)'),
                Filter::make('in_progress')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'in_progress'))
                    ->label('En Progreso'),
                Filter::make('completed')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'completed'))
                    ->label('Completadas'),
                Filter::make('pending')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'pending'))
                    ->label('Pendientes'),
                Filter::make('recent_transfers')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(7)))
                    ->label('Transferencias Recientes (≤7 días)'),
                Filter::make('date_range')
                    ->form([
                        DateTimePicker::make('created_from')
                            ->label('Creado desde'),
                        DateTimePicker::make('created_until')
                            ->label('Creado hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->where('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->where('created_at', '<=', $date),
                            );
                    })
                    ->label('Rango de Fechas de Creación'),
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
                                fn (Builder $query, $amount): Builder => $query->where('transfer_amount_kwh', '>=', $amount),
                            )
                            ->when(
                                $data['max_amount'],
                                fn (Builder $query, $amount): Builder => $query->where('transfer_amount_kwh', '<=', $amount),
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
                Tables\Actions\Action::make('approve_transfer')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (EnergyTransfer $record) => $record->status === 'pending' && $record->requires_approval)
                    ->action(function (EnergyTransfer $record) {
                        $record->update([
                            'status' => 'scheduled',
                            'is_approved' => true,
                            'approved_at' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('start_transfer')
                    ->label('Iniciar')
                    ->icon('heroicon-o-play')
                    ->color('primary')
                    ->visible(fn (EnergyTransfer $record) => in_array($record->status, ['scheduled', 'pending']))
                    ->action(function (EnergyTransfer $record) {
                        $record->update([
                            'status' => 'in_progress',
                            'actual_start_time' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('complete_transfer')
                    ->label('Completar')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (EnergyTransfer $record) => $record->status === 'in_progress')
                    ->action(function (EnergyTransfer $record) {
                        $record->update([
                            'status' => 'completed',
                            'actual_end_time' => now(),
                            'completion_time' => now(),
                            'completed_at' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('cancel_transfer')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (EnergyTransfer $record) => in_array($record->status, ['pending', 'scheduled', 'in_progress']))
                    ->action(function (EnergyTransfer $record) {
                        $record->update([
                            'status' => 'cancelled',
                        ]);
                    }),
                Tables\Actions\Action::make('verify_transfer')
                    ->label('Verificar')
                    ->icon('heroicon-o-shield-check')
                    ->color('info')
                    ->visible(fn (EnergyTransfer $record) => !$record->is_verified)
                    ->action(function (EnergyTransfer $record) {
                        $record->update([
                            'is_verified' => true,
                        ]);
                    }),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (EnergyTransfer $record) {
                        $newRecord = $record->replicate();
                        $newRecord->status = 'pending';
                        $newRecord->progress_percentage = 0;
                        $newRecord->current_kwh_transferred = 0;
                        $newRecord->remaining_kwh = $record->kwh_amount;
                        $newRecord->started_at = null;
                        $newRecord->completed_at = null;
                        $newRecord->approved_at = null;
                        $newRecord->cancelled_at = null;
                        $newRecord->is_verified = false;
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
                                        'status' => 'scheduled',
                                        'is_approved' => true,
                                        'approved_at' => now(),
                                    ]);
                                }
                            });
                        }),
                    Tables\Actions\BulkAction::make('start_all')
                        ->label('Iniciar Todas')
                        ->icon('heroicon-o-play')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if (in_array($record->status, ['scheduled', 'pending'])) {
                                    $record->update([
                                        'status' => 'in_progress',
                                        'actual_start_time' => now(),
                                    ]);
                                }
                            });
                        }),
                    Tables\Actions\BulkAction::make('complete_all')
                        ->label('Completar Todas')
                        ->icon('heroicon-o-check')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->status === 'in_progress') {
                                    $record->update([
                                        'status' => 'completed',
                                        'actual_end_time' => now(),
                                        'completion_time' => now(),
                                        'completed_at' => now(),
                                    ]);
                                }
                            });
                        }),
                    Tables\Actions\BulkAction::make('cancel_all')
                        ->label('Cancelar Todas')
                        ->icon('heroicon-o-x-mark')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if (in_array($record->status, ['pending', 'scheduled', 'in_progress'])) {
                                    $record->update([
                                        'status' => 'cancelled',
                                    ]);
                                }
                            });
                        }),
                    Tables\Actions\BulkAction::make('verify_all')
                        ->label('Verificar Todas')
                        ->icon('heroicon-o-shield-check')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_verified' => true]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_status')
                        ->label('Actualizar Estado')
                        ->icon('heroicon-o-flag')
                        ->form([
                            Select::make('status')
                                ->label('Estado')
                                ->options(EnergyTransfer::getStatuses())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['status' => $data['status']]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('update_transfer_type')
                        ->label('Actualizar Tipo de Transferencia')
                        ->icon('heroicon-o-tag')
                        ->form([
                            Select::make('transfer_type')
                                ->label('Tipo de Transferencia')
                                ->options(EnergyTransfer::getTransferTypes())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['transfer_type' => $data['transfer_type']]);
                            });
                        }),
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
            'index' => Pages\ListEnergyTransfers::route('/'),
            'create' => Pages\CreateEnergyTransfer::route('/create'),
            'edit' => Pages\EditEnergyTransfer::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
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
        
        $inProgressCount = static::getModel()::where('status', 'in_progress')->count();
        
        if ($inProgressCount > 0) {
            return 'info';
        }
        
        return 'success';
    }
}
