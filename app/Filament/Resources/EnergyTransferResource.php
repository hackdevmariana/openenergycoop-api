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

    protected static ?string $navigationGroup = 'Comercio de Energía';

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
                                Select::make('from_user_id')
                                    ->label('Usuario Origen')
                                    ->relationship('fromUser', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Usuario que envía la energía'),
                                Select::make('to_user_id')
                                    ->label('Usuario Destino')
                                    ->relationship('toUser', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Usuario que recibe la energía'),
                            ]),
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
                                TextInput::make('kwh_amount')
                                    ->label('Cantidad (kWh)')
                                    ->numeric()
                                    ->minValue(0.001)
                                    ->step(0.001)
                                    ->required()
                                    ->suffix(' kWh')
                                    ->helperText('Cantidad de energía a transferir'),
                                TextInput::make('transfer_rate')
                                    ->label('Tasa de Transferencia (kWh/h)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' kWh/h')
                                    ->helperText('Velocidad de la transferencia'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('estimated_duration_hours')
                                    ->label('Duración Estimada (horas)')
                                    ->numeric()
                                    ->minValue(0.1)
                                    ->step(0.1)
                                    ->suffix(' h')
                                    ->helperText('Tiempo estimado para completar'),
                                TextInput::make('actual_duration_hours')
                                    ->label('Duración Real (horas)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.1)
                                    ->suffix(' h')
                                    ->helperText('Tiempo real tomado'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Relaciones y Asignaciones')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('from_installation_id')
                                    ->label('Instalación Origen')
                                    ->relationship('fromInstallation', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Instalación de origen'),
                                Select::make('to_installation_id')
                                    ->label('Instalación Destino')
                                    ->relationship('toInstallation', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Instalación de destino'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('energy_source_id')
                                    ->label('Fuente de Energía')
                                    ->relationship('energySource', 'name')
                                    ->searchable()
                                    ->preload(),
                                Select::make('transfer_network_id')
                                    ->label('Red de Transferencia')
                                    ->relationship('transferNetwork', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('approved_by_user_id')
                                    ->label('Aprobado por')
                                    ->relationship('approvedByUser', 'name')
                                    ->searchable()
                                    ->preload(),
                                Select::make('created_by')
                                    ->label('Creado por')
                                    ->relationship('createdBy', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Detalles de la Transferencia')
                    ->schema([
                        RichEditor::make('message')
                            ->label('Mensaje')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('Mensaje o descripción de la transferencia'),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('transfer_fee')
                                    ->label('Tarifa de Transferencia (€)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->helperText('Tarifa por la transferencia'),
                                TextInput::make('network_fee')
                                    ->label('Tarifa de Red (€)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->helperText('Tarifa de la red de distribución'),
                                TextInput::make('total_fee')
                                    ->label('Tarifa Total (€)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->helperText('Tarifa total incluyendo todas las comisiones'),
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
                                DateTimePicker::make('scheduled_at')
                                    ->label('Programado para')
                                    ->helperText('Cuándo se programó la transferencia'),
                                DateTimePicker::make('started_at')
                                    ->label('Iniciado el')
                                    ->helperText('Cuándo comenzó la transferencia'),
                                DateTimePicker::make('completed_at')
                                    ->label('Completado el')
                                    ->helperText('Cuándo se completó la transferencia'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                DateTimePicker::make('confirmed_at')
                                    ->label('Confirmado el')
                                    ->helperText('Cuándo se confirmó la transferencia'),
                                DateTimePicker::make('approved_at')
                                    ->label('Aprobado el')
                                    ->helperText('Cuándo se aprobó la transferencia'),
                                DateTimePicker::make('cancelled_at')
                                    ->label('Cancelado el')
                                    ->helperText('Cuándo se canceló la transferencia'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('expires_at')
                                    ->label('Expira el')
                                    ->helperText('Cuándo expira la solicitud'),
                                DateTimePicker::make('last_activity_at')
                                    ->label('Última Actividad')
                                    ->helperText('Cuándo fue la última actividad'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Monitoreo y Estado')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('progress_percentage')
                                    ->label('Progreso (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Porcentaje de progreso de la transferencia'),
                                TextInput::make('current_kwh_transferred')
                                    ->label('kWh Transferidos Actualmente')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' kWh')
                                    ->helperText('Cantidad ya transferida'),
                                TextInput::make('remaining_kwh')
                                    ->label('kWh Restantes')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' kWh')
                                    ->helperText('Cantidad pendiente de transferir'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('transfer_speed')
                                    ->label('Velocidad de Transferencia (kWh/h)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix(' kWh/h')
                                    ->helperText('Velocidad actual de transferencia'),
                                TextInput::make('efficiency_rating')
                                    ->label('Calificación de Eficiencia')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Eficiencia de la transferencia'),
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
                TextColumn::make('fromUser.name')
                    ->label('Usuario Origen')
                    ->searchable()
                    ->sortable()
                    ->limit(20),
                TextColumn::make('toUser.name')
                    ->label('Usuario Destino')
                    ->searchable()
                    ->sortable()
                    ->limit(20),
                BadgeColumn::make('transfer_type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'peer_to_peer',
                        'success' => 'pool_to_user',
                        'warning' => 'user_to_pool',
                        'danger' => 'emergency',
                        'info' => 'scheduled',
                        'secondary' => 'bulk',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyTransfer::getTransferTypes()[$state] ?? $state),
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'completed',
                        'warning' => 'in_progress',
                        'danger' => 'cancelled',
                        'info' => 'pending',
                        'secondary' => 'approved',
                        'gray' => 'failed',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyTransfer::getStatuses()[$state] ?? $state),
                BadgeColumn::make('priority')
                    ->label('Prioridad')
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                        'secondary' => 'urgent',
                        'gray' => 'critical',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyTransfer::getPriorities()[$state] ?? $state),
                TextColumn::make('kwh_amount')
                    ->label('Cantidad (kWh)')
                    ->suffix(' kWh')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 1000 => 'success',
                        $state >= 500 => 'primary',
                        $state >= 100 => 'warning',
                        $state >= 10 => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('progress_percentage')
                    ->label('Progreso (%)')
                    ->suffix('%')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 100 => 'success',
                        $state >= 75 => 'primary',
                        $state >= 50 => 'warning',
                        $state >= 25 => 'info',
                        default => 'danger',
                    }),
                TextColumn::make('transfer_fee')
                    ->label('Tarifa (€)')
                    ->money('EUR')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->money('EUR'),
                    ])
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('transfer_rate')
                    ->label('Velocidad (kWh/h)')
                    ->suffix(' kWh/h')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('estimated_duration_hours')
                    ->label('Duración Est. (h)')
                    ->suffix(' h')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('energy_quality')
                    ->label('Calidad')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state === 'premium' => 'success',
                        $state === 'standard' => 'primary',
                        $state === 'basic' => 'warning',
                        $state === 'renewable' => 'info',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('verification_code')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->limit(6)
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('requires_2fa')
                    ->label('2FA'),
                ToggleColumn::make('admin_approval_required')
                    ->label('Aprob. Admin'),
                ToggleColumn::make('is_verified')
                    ->label('Verificado'),
                TextColumn::make('scheduled_at')
                    ->label('Programado')
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
                TextColumn::make('started_at')
                    ->label('Iniciado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('completed_at')
                    ->label('Completado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_active')
                    ->label('Activa'),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
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
                Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Solo Activas'),
                Filter::make('verified')
                    ->query(fn (Builder $query): Builder => $query->where('is_verified', true))
                    ->label('Solo Verificadas'),
                Filter::make('requires_2fa')
                    ->query(fn (Builder $query): Builder => $query->where('requires_2fa', true))
                    ->label('Requieren 2FA'),
                Filter::make('admin_approval_required')
                    ->query(fn (Builder $query): Builder => $query->where('admin_approval_required', true))
                    ->label('Requieren Aprobación Admin'),
                Filter::make('high_amount')
                    ->query(fn (Builder $query): Builder => $query->where('kwh_amount', '>=', 1000))
                    ->label('Alta Cantidad (≥1000 kWh)'),
                Filter::make('low_amount')
                    ->query(fn (Builder $query): Builder => $query->where('kwh_amount', '<', 100))
                    ->label('Baja Cantidad (<100 kWh)'),
                Filter::make('high_fee')
                    ->query(fn (Builder $query): Builder => $query->where('transfer_fee', '>=', 100))
                    ->label('Alta Tarifa (≥€100)'),
                Filter::make('low_fee')
                    ->query(fn (Builder $query): Builder => $query->where('transfer_fee', '<', 10))
                    ->label('Baja Tarifa (<€10)'),
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
                                fn (Builder $query, $amount): Builder => $query->where('kwh_amount', '>=', $amount),
                            )
                            ->when(
                                $data['max_amount'],
                                fn (Builder $query, $amount): Builder => $query->where('kwh_amount', '<=', $amount),
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
                    ->visible(fn (EnergyTransfer $record) => $record->status === 'pending' && $record->admin_approval_required)
                    ->action(function (EnergyTransfer $record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_at' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('start_transfer')
                    ->label('Iniciar')
                    ->icon('heroicon-o-play')
                    ->color('primary')
                    ->visible(fn (EnergyTransfer $record) => in_array($record->status, ['approved', 'pending']))
                    ->action(function (EnergyTransfer $record) {
                        $record->update([
                            'status' => 'in_progress',
                            'started_at' => now(),
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
                            'completed_at' => now(),
                            'progress_percentage' => 100,
                            'current_kwh_transferred' => $record->kwh_amount,
                            'remaining_kwh' => 0,
                        ]);
                    }),
                Tables\Actions\Action::make('cancel_transfer')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (EnergyTransfer $record) => in_array($record->status, ['pending', 'approved', 'in_progress']))
                    ->action(function (EnergyTransfer $record) {
                        $record->update([
                            'status' => 'cancelled',
                            'cancelled_at' => now(),
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
                                        'status' => 'approved',
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
                                if (in_array($record->status, ['approved', 'pending'])) {
                                    $record->update([
                                        'status' => 'in_progress',
                                        'started_at' => now(),
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
                                        'completed_at' => now(),
                                        'progress_percentage' => 100,
                                        'current_kwh_transferred' => $record->kwh_amount,
                                        'remaining_kwh' => 0,
                                    ]);
                                }
                            });
                        }),
                    Tables\Actions\BulkAction::make('cancel_all')
                        ->label('Cancelar Todas')
                        ->icon('heroicon-o-x-mark')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if (in_array($record->status, ['pending', 'approved', 'in_progress'])) {
                                    $record->update([
                                        'status' => 'cancelled',
                                        'cancelled_at' => now(),
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
