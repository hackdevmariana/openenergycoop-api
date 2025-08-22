<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VendorResource\Pages;
use App\Filament\Resources\VendorResource\RelationManagers;
use App\Models\Vendor;
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

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Gestión de Proveedores';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Proveedores';

    protected static ?string $modelLabel = 'Proveedor';

    protected static ?string $pluralModelLabel = 'Proveedores';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información Básica')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nombre Comercial')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Nombre comercial del proveedor'),
                                TextInput::make('legal_name')
                                    ->label('Razón Social')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Nombre legal/razón social'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('tax_id')
                                    ->label('Identificación Fiscal')
                                    ->maxLength(255)
                                    ->helperText('Número de identificación fiscal'),
                                TextInput::make('registration_number')
                                    ->label('Número de Registro')
                                    ->maxLength(255)
                                    ->helperText('Número de registro comercial'),
                                Select::make('vendor_type')
                                    ->label('Tipo de Proveedor')
                                    ->options([
                                        'manufacturer' => 'Fabricante',
                                        'distributor' => 'Distribuidor',
                                        'service_provider' => 'Proveedor de Servicios',
                                        'consultant' => 'Consultor',
                                        'contractor' => 'Contratista',
                                        'supplier' => 'Suministrador',
                                        'other' => 'Otro',
                                    ])
                                    ->required()
                                    ->searchable(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('industry')
                                    ->label('Industria')
                                    ->maxLength(255)
                                    ->helperText('Sector industrial principal'),
                                TextInput::make('website')
                                    ->label('Sitio Web')
                                    ->url()
                                    ->maxLength(255)
                                    ->helperText('URL del sitio web corporativo'),
                            ]),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->maxLength(65535)
                            ->helperText('Descripción detallada del proveedor'),
                    ])
                    ->collapsible(),

                Section::make('Información de Contacto')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('contact_person')
                                    ->label('Persona de Contacto')
                                    ->maxLength(255)
                                    ->helperText('Nombre de la persona principal de contacto'),
                                TextInput::make('email')
                                    ->label('Correo Electrónico')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Correo electrónico principal'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('phone')
                                    ->label('Teléfono')
                                    ->tel()
                                    ->maxLength(255)
                                    ->helperText('Número de teléfono principal'),
                                TextInput::make('fax')
                                    ->label('Fax')
                                    ->tel()
                                    ->maxLength(255),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('mobile')
                                    ->label('Móvil')
                                    ->tel()
                                    ->maxLength(255),
                                TextInput::make('emergency_contact')
                                    ->label('Contacto de Emergencia')
                                    ->maxLength(255)
                                    ->helperText('Contacto para situaciones de emergencia'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Dirección y Ubicación')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('address')
                                    ->label('Dirección')
                                    ->maxLength(255)
                                    ->required()
                                    ->helperText('Dirección física principal'),
                                TextInput::make('city')
                                    ->label('Ciudad')
                                    ->maxLength(255)
                                    ->required(),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('state')
                                    ->label('Estado/Provincia')
                                    ->maxLength(255),
                                TextInput::make('postal_code')
                                    ->label('Código Postal')
                                    ->maxLength(20),
                                TextInput::make('country')
                                    ->label('País')
                                    ->maxLength(255)
                                    ->required(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('latitude')
                                    ->label('Latitud')
                                    ->numeric()
                                    ->step(0.000001)
                                    ->minValue(-90)
                                    ->maxValue(90),
                                TextInput::make('longitude')
                                    ->label('Longitud')
                                    ->numeric()
                                    ->step(0.000001)
                                    ->minValue(-180)
                                    ->maxValue(180),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Información Financiera')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('payment_terms')
                                    ->label('Términos de Pago')
                                    ->maxLength(255)
                                    ->helperText('Ej: Neto 30, 2/10 Neto 30'),
                                TextInput::make('credit_limit')
                                    ->label('Límite de Crédito')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01),
                                TextInput::make('current_balance')
                                    ->label('Saldo Actual')
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01),
                            ]),
                        Grid::make(3)
                            ->schema([
                                Select::make('currency')
                                    ->label('Moneda')
                                    ->options([
                                        'USD' => 'Dólar Estadounidense',
                                        'EUR' => 'Euro',
                                        'MXN' => 'Peso Mexicano',
                                        'CAD' => 'Dólar Canadiense',
                                        'GBP' => 'Libra Esterlina',
                                        'JPY' => 'Yen Japonés',
                                        'other' => 'Otra',
                                    ])
                                    ->default('USD')
                                    ->searchable(),
                                TextInput::make('tax_rate')
                                    ->label('Tasa de Impuesto (%)')
                                    ->numeric()
                                    ->suffix('%')
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01),
                                TextInput::make('discount_rate')
                                    ->label('Tasa de Descuento (%)')
                                    ->numeric()
                                    ->suffix('%')
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Clasificación y Evaluación')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('rating')
                                    ->label('Calificación')
                                    ->options([
                                        '5' => '5 - Excelente',
                                        '4' => '4 - Muy Bueno',
                                        '3' => '3 - Bueno',
                                        '2' => '2 - Regular',
                                        '1' => '1 - Malo',
                                    ])
                                    ->searchable(),
                                Select::make('status')
                                    ->label('Estado')
                                    ->options([
                                        'active' => 'Activo',
                                        'inactive' => 'Inactivo',
                                        'suspended' => 'Suspendido',
                                        'blacklisted' => 'En Lista Negra',
                                        'pending' => 'Pendiente',
                                        'approved' => 'Aprobado',
                                        'rejected' => 'Rechazado',
                                    ])
                                    ->required()
                                    ->searchable(),
                                Select::make('risk_level')
                                    ->label('Nivel de Riesgo')
                                    ->options([
                                        'low' => 'Bajo',
                                        'medium' => 'Medio',
                                        'high' => 'Alto',
                                        'very_high' => 'Muy Alto',
                                    ])
                                    ->required()
                                    ->searchable(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_verified')
                                    ->label('Verificado')
                                    ->helperText('¿El proveedor ha sido verificado?'),
                                Toggle::make('is_preferred')
                                    ->label('Preferido')
                                    ->helperText('¿Es un proveedor preferido?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_blacklisted')
                                    ->label('En Lista Negra')
                                    ->helperText('¿El proveedor está en lista negra?'),
                                Toggle::make('is_active')
                                    ->label('Activo')
                                    ->helperText('¿El proveedor está activo?'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Contratos y Relaciones')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('contract_start_date')
                                    ->label('Fecha de Inicio de Contrato'),
                                DatePicker::make('contract_end_date')
                                    ->label('Fecha de Fin de Contrato'),
                            ]),
                        RichEditor::make('contract_terms')
                            ->label('Términos del Contrato')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                        RichEditor::make('insurance_coverage')
                            ->label('Cobertura de Seguro')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                        RichEditor::make('warranty_terms')
                            ->label('Términos de Garantía')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Certificaciones y Cumplimiento')
                    ->schema([
                        RichEditor::make('certifications')
                            ->label('Certificaciones')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                        RichEditor::make('licenses')
                            ->label('Licencias')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                        Select::make('compliance_status')
                            ->label('Estado de Cumplimiento')
                            ->options([
                                'compliant' => 'Cumple',
                                'non_compliant' => 'No Cumple',
                                'pending_review' => 'Pendiente de Revisión',
                                'under_investigation' => 'Bajo Investigación',
                                'conditional' => 'Condicional',
                            ])
                            ->searchable(),
                        RichEditor::make('compliance_notes')
                            ->label('Notas de Cumplimiento')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Rendimiento y Métricas')
                    ->schema([
                        RichEditor::make('performance_metrics')
                            ->label('Métricas de Rendimiento')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                        RichEditor::make('quality_standards')
                            ->label('Estándares de Calidad')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                        RichEditor::make('delivery_terms')
                            ->label('Términos de Entrega')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                        RichEditor::make('return_policy')
                            ->label('Política de Devolución')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Auditoría y Revisión')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('audit_frequency')
                                    ->label('Frecuencia de Auditoría (meses)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->step(1)
                                    ->helperText('Cada cuántos meses se audita'),
                                DatePicker::make('last_audit_date')
                                    ->label('Última Fecha de Auditoría'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('next_audit_date')
                                    ->label('Próxima Fecha de Auditoría'),
                                DatePicker::make('last_review_date')
                                    ->label('Última Fecha de Revisión'),
                            ]),
                        RichEditor::make('audit_findings')
                            ->label('Hallazgos de Auditoría')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                        RichEditor::make('improvement_plans')
                            ->label('Planes de Mejora')
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
                        FileUpload::make('logo')
                            ->label('Logo')
                            ->image()
                            ->directory('vendors/logos')
                            ->maxSize(2048)
                            ->helperText('Logo del proveedor (máximo 2MB)'),
                        FileUpload::make('documents')
                            ->label('Documentos')
                            ->multiple()
                            ->directory('vendors/documents')
                            ->maxFiles(50)
                            ->maxSize(10240)
                            ->helperText('Documentos del proveedor (máximo 50 archivos de 10MB cada uno)'),
                        RichEditor::make('contact_history')
                            ->label('Historial de Contacto')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
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

                Section::make('Asignaciones del Sistema')
                    ->schema([
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('legal_name')
                    ->label('Razón Social')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                BadgeColumn::make('vendor_type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'manufacturer',
                        'success' => 'distributor',
                        'warning' => 'service_provider',
                        'danger' => 'consultant',
                        'info' => 'contractor',
                        'secondary' => 'supplier',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'manufacturer' => 'Fabricante',
                        'distributor' => 'Distribuidor',
                        'service_provider' => 'Servicios',
                        'consultant' => 'Consultor',
                        'contractor' => 'Contratista',
                        'supplier' => 'Suministrador',
                        'other' => 'Otro',
                        default => $state,
                    }),
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'inactive',
                        'warning' => 'suspended',
                        'secondary' => 'blacklisted',
                        'info' => 'pending',
                        'primary' => 'approved',
                        'gray' => 'rejected',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Activo',
                        'inactive' => 'Inactivo',
                        'suspended' => 'Suspendido',
                        'blacklisted' => 'Lista Negra',
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobado',
                        'rejected' => 'Rechazado',
                        default => $state,
                    }),
                TextColumn::make('contact_person')
                    ->label('Contacto')
                    ->searchable()
                    ->sortable()
                    ->limit(25),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->limit(25),
                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('city')
                    ->label('Ciudad')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('country')
                    ->label('País')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('rating')
                    ->label('Calificación')
                    ->colors([
                        'success' => '5',
                        'primary' => '4',
                        'warning' => '3',
                        'danger' => '2',
                        'secondary' => '1',
                    ])
                    ->formatStateUsing(fn (string $state): string => $state . ' ⭐'),
                BadgeColumn::make('risk_level')
                    ->label('Riesgo')
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                        'secondary' => 'very_high',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'low' => 'Bajo',
                        'medium' => 'Medio',
                        'high' => 'Alto',
                        'very_high' => 'Muy Alto',
                        default => $state,
                    }),
                TextColumn::make('credit_limit')
                    ->label('Límite Crédito')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('current_balance')
                    ->label('Saldo Actual')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_verified')
                    ->label('Verificado'),
                ToggleColumn::make('is_preferred')
                    ->label('Preferido'),
                ToggleColumn::make('is_active')
                    ->label('Activo'),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('vendor_type')
                    ->label('Tipo de Proveedor')
                    ->options([
                        'manufacturer' => 'Fabricante',
                        'distributor' => 'Distribuidor',
                        'service_provider' => 'Proveedor de Servicios',
                        'consultant' => 'Consultor',
                        'contractor' => 'Contratista',
                        'supplier' => 'Suministrador',
                        'other' => 'Otro',
                    ])
                    ->multiple(),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'active' => 'Activo',
                        'inactive' => 'Inactivo',
                        'suspended' => 'Suspendido',
                        'blacklisted' => 'En Lista Negra',
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobado',
                        'rejected' => 'Rechazado',
                    ])
                    ->multiple(),
                SelectFilter::make('risk_level')
                    ->label('Nivel de Riesgo')
                    ->options([
                        'low' => 'Bajo',
                        'medium' => 'Medio',
                        'high' => 'Alto',
                        'very_high' => 'Muy Alto',
                    ])
                    ->multiple(),
                SelectFilter::make('rating')
                    ->label('Calificación')
                    ->options([
                        '5' => '5 - Excelente',
                        '4' => '4 - Muy Bueno',
                        '3' => '3 - Bueno',
                        '2' => '2 - Regular',
                        '1' => '1 - Malo',
                    ])
                    ->multiple(),
                Filter::make('verified')
                    ->query(fn (Builder $query): Builder => $query->where('is_verified', true))
                    ->label('Solo Verificados'),
                Filter::make('preferred')
                    ->query(fn (Builder $query): Builder => $query->where('is_preferred', true))
                    ->label('Solo Preferidos'),
                Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Solo Activos'),
                Filter::make('high_risk')
                    ->query(fn (Builder $query): Builder => $query->whereIn('risk_level', ['high', 'very_high']))
                    ->label('Alto Riesgo'),
                Filter::make('country_filter')
                    ->form([
                        TextInput::make('country')
                            ->label('País')
                            ->placeholder('Ingrese el nombre del país'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['country'],
                            fn (Builder $query, $country): Builder => $query->where('country', 'like', "%{$country}%"),
                        );
                    })
                    ->label('Filtrar por País'),
                Filter::make('rating_range')
                    ->form([
                        TextInput::make('min_rating')
                            ->label('Calificación mínima')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5),
                        TextInput::make('max_rating')
                            ->label('Calificación máxima')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_rating'],
                                fn (Builder $query, $rating): Builder => $query->where('rating', '>=', $rating),
                            )
                            ->when(
                                $data['max_rating'],
                                fn (Builder $query, $rating): Builder => $query->where('rating', '<=', $rating),
                            );
                    })
                    ->label('Rango de Calificación'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->color('info'),
                Tables\Actions\EditAction::make()
                    ->color('warning'),
                Tables\Actions\DeleteAction::make()
                    ->color('danger'),
                Tables\Actions\Action::make('verify')
                    ->label('Verificar')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Vendor $record) => !$record->is_verified)
                    ->action(function (Vendor $record) {
                        $record->update(['is_verified' => true]);
                    }),
                Tables\Actions\Action::make('mark_preferred')
                    ->label('Marcar Preferido')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (Vendor $record) => !$record->is_preferred)
                    ->action(function (Vendor $record) {
                        $record->update(['is_preferred' => true]);
                    }),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (Vendor $record) {
                        $newRecord = $record->replicate();
                        $newRecord->name = $newRecord->name . ' (Copia)';
                        $newRecord->legal_name = $newRecord->legal_name . ' (Copia)';
                        $newRecord->status = 'pending';
                        $newRecord->is_verified = false;
                        $newRecord->is_preferred = false;
                        $newRecord->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('verify_all')
                        ->label('Verificar Todos')
                        ->icon('heroicon-o-check-badge')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_verified' => true]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('mark_preferred_all')
                        ->label('Marcar como Preferidos')
                        ->icon('heroicon-o-star')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_preferred' => true]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('activate_all')
                        ->label('Activar Todos')
                        ->icon('heroicon-o-play')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_active' => true, 'status' => 'active']);
                            });
                        }),
                    Tables\Actions\BulkAction::make('assign_rating')
                        ->label('Asignar Calificación')
                        ->icon('heroicon-o-star')
                        ->form([
                            Select::make('rating')
                                ->label('Calificación')
                                ->options([
                                    '5' => '5 - Excelente',
                                    '4' => '4 - Muy Bueno',
                                    '3' => '3 - Bueno',
                                    '2' => '2 - Regular',
                                    '1' => '1 - Malo',
                                ])
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['rating' => $data['rating']]);
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
            'index' => Pages\ListVendors::route('/'),
            'create' => Pages\CreateVendor::route('/create'),
            'edit' => Pages\EditVendor::route('/{record}/edit'),
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
        $blacklistedCount = static::getModel()::where('is_blacklisted', true)->count();
        
        if ($blacklistedCount > 0) {
            return 'danger';
        }
        
        $suspendedCount = static::getModel()::where('status', 'suspended')->count();
        
        if ($suspendedCount > 0) {
            return 'warning';
        }
        
        return 'success';
    }
}
