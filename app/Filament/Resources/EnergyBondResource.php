<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnergyBondResource\Pages;
use App\Filament\Resources\EnergyBondResource\RelationManagers;
use App\Models\EnergyBond;
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
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EnergyBondResource extends Resource
{
    protected static ?string $model = EnergyBond::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Energía y Comercio';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Bonos de Energía';

    protected static ?string $modelLabel = 'Bono de Energía';

    protected static ?string $pluralModelLabel = 'Bonos de Energía';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información Básica')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('bond_number')
                                    ->label('Número de Bono')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->helperText('Identificador único del bono'),
                                TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Nombre descriptivo del bono'),
                            ]),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->maxLength(65535)
                            ->helperText('Descripción detallada del bono'),
                        Grid::make(3)
                            ->schema([
                                Select::make('bond_type')
                                    ->label('Tipo de Bono')
                                    ->options(EnergyBond::getBondTypes())
                                    ->required()
                                    ->searchable(),
                                Select::make('status')
                                    ->label('Estado')
                                    ->options(EnergyBond::getStatuses())
                                    ->required()
                                    ->searchable(),
                                Select::make('priority')
                                    ->label('Prioridad')
                                    ->options(EnergyBond::getPriorities())
                                    ->required()
                                    ->searchable(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Detalles Financieros')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('face_value')
                                    ->label('Valor Nominal')
                                    ->numeric()
                                    ->prefix('$')
                                    ->required()
                                    ->step(0.01)
                                    ->helperText('Valor nominal del bono'),
                                TextInput::make('current_value')
                                    ->label('Valor Actual')
                                    ->numeric()
                                    ->prefix('$')
                                    ->required()
                                    ->step(0.01)
                                    ->helperText('Valor actual de mercado'),
                                TextInput::make('interest_rate')
                                    ->label('Tasa de Interés (%)')
                                    ->numeric()
                                    ->suffix('%')
                                    ->required()
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->maxValue(100),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('interest_frequency')
                                    ->label('Frecuencia de Interés')
                                    ->options(EnergyBond::getInterestFrequencies())
                                    ->required()
                                    ->searchable(),
                                Select::make('payment_schedule')
                                    ->label('Programa de Pagos')
                                    ->options(EnergyBond::getPaymentSchedules())
                                    ->required()
                                    ->searchable(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('minimum_investment')
                                    ->label('Inversión Mínima')
                                    ->numeric()
                                    ->prefix('$')
                                    ->required()
                                    ->step(0.01),
                                TextInput::make('maximum_investment')
                                    ->label('Inversión Máxima')
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('outstanding_principal')
                                    ->label('Principal Pendiente')
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01),
                                TextInput::make('total_interest_paid')
                                    ->label('Interés Total Pagado')
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Fechas Importantes')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                DatePicker::make('issue_date')
                                    ->label('Fecha de Emisión')
                                    ->required()
                                    ->helperText('Fecha cuando se emitió el bono'),
                                DatePicker::make('maturity_date')
                                    ->label('Fecha de Vencimiento')
                                    ->required()
                                    ->helperText('Fecha de vencimiento del bono'),
                                DatePicker::make('first_interest_date')
                                    ->label('Primera Fecha de Interés')
                                    ->required(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('last_interest_payment_date')
                                    ->label('Última Fecha de Pago de Interés'),
                                DatePicker::make('next_interest_payment_date')
                                    ->label('Próxima Fecha de Pago de Interés'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Unidades y Precios')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('total_units_available')
                                    ->label('Unidades Totales Disponibles')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1),
                                TextInput::make('units_issued')
                                    ->label('Unidades Emitidas')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                                TextInput::make('units_reserved')
                                    ->label('Unidades Reservadas')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                            ]),
                        TextInput::make('unit_price')
                            ->label('Precio por Unidad')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->step(0.01),
                    ])
                    ->collapsible(),

                Section::make('Características Especiales')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_tax_free')
                                    ->label('Libre de Impuestos')
                                    ->helperText('¿El bono está exento de impuestos?'),
                                Toggle::make('is_guaranteed')
                                    ->label('Garantizado')
                                    ->helperText('¿El bono tiene garantía?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_collateralized')
                                    ->label('Con Colateral')
                                    ->helperText('¿El bono tiene colateral?'),
                                Toggle::make('is_public')
                                    ->label('Público')
                                    ->helperText('¿El bono es público?'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_featured')
                                    ->label('Destacado')
                                    ->helperText('¿Mostrar como destacado?'),
                                TextInput::make('priority_order')
                                    ->label('Orden de Prioridad')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Orden de prioridad para mostrar'),
                            ]),
                        TextInput::make('guarantor_name')
                            ->label('Nombre del Garante')
                            ->maxLength(255),
                        Textarea::make('guarantee_terms')
                            ->label('Términos de Garantía')
                            ->rows(2),
                        Textarea::make('collateral_description')
                            ->label('Descripción del Colateral')
                            ->rows(2),
                        TextInput::make('collateral_value')
                            ->label('Valor del Colateral')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01),
                    ])
                    ->collapsible(),

                Section::make('Clasificación y Riesgo')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('credit_rating')
                                    ->label('Calificación Crediticia')
                                    ->options([
                                        'aaa' => 'AAA - Excelente',
                                        'aa' => 'AA - Muy Buena',
                                        'a' => 'A - Buena',
                                        'bbb' => 'BBB - Satisfactoria',
                                        'bb' => 'BB - Especulativa',
                                        'b' => 'B - Altamente Especulativa',
                                        'ccc' => 'CCC - Muy Especulativa',
                                        'cc' => 'CC - Extremadamente Especulativa',
                                        'c' => 'C - En Default',
                                        'd' => 'D - En Default',
                                    ])
                                    ->searchable(),
                                Select::make('risk_level')
                                    ->label('Nivel de Riesgo')
                                    ->options(EnergyBond::getRiskLevels())
                                    ->required()
                                    ->searchable(),
                            ]),
                        Textarea::make('risk_disclosure')
                            ->label('Divulgación de Riesgos')
                            ->rows(3),
                        TextInput::make('tax_rate')
                            ->label('Tasa de Impuesto (%)')
                            ->numeric()
                            ->suffix('%')
                            ->default(0)
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(100),
                    ])
                    ->collapsible(),

                Section::make('Documentos y Términos')
                    ->schema([
                        RichEditor::make('terms_conditions')
                            ->label('Términos y Condiciones')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                        RichEditor::make('disclosure_documents')
                            ->label('Documentos de Divulgación')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                        RichEditor::make('legal_documents')
                            ->label('Documentos Legales')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                        RichEditor::make('financial_reports')
                            ->label('Reportes Financieros')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                        RichEditor::make('performance_metrics')
                            ->label('Métricas de Rendimiento')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Metadatos')
                    ->schema([
                        TagsInput::make('tags')
                            ->label('Etiquetas')
                            ->separator(','),
                        Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3),
                    ])
                    ->collapsible(),

                Section::make('Asignaciones')
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
                        Select::make('managed_by')
                            ->label('Gestionado por')
                            ->relationship('managedBy', 'name')
                            ->searchable()
                            ->preload(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bond_number')
                    ->label('Número')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->tooltip('Haz clic para copiar'),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                BadgeColumn::make('bond_type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'solar',
                        'success' => 'wind',
                        'warning' => 'hydro',
                        'danger' => 'biomass',
                        'info' => 'geothermal',
                        'secondary' => 'hybrid',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyBond::getBondTypes()[$state] ?? $state),
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'inactive',
                        'warning' => 'expired',
                        'info' => 'redeemed',
                        'secondary' => 'cancelled',
                        'primary' => 'pending_approval',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyBond::getStatuses()[$state] ?? $state),
                TextColumn::make('face_value')
                    ->label('Valor Nominal')
                    ->money('USD')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->money('USD'),
                    ]),
                TextColumn::make('interest_rate')
                    ->label('Tasa (%)')
                    ->suffix('%')
                    ->sortable()
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    ),
                TextColumn::make('maturity_date')
                    ->label('Vencimiento')
                    ->date()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, '2024') => 'success',
                        str_contains($state, '2025') => 'warning',
                        str_contains($state, '2026') => 'danger',
                        default => 'gray',
                    }),
                BadgeColumn::make('risk_level')
                    ->label('Riesgo')
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                        'secondary' => 'very_high',
                    ])
                    ->formatStateUsing(fn (string $state): string => EnergyBond::getRiskLevels()[$state] ?? $state),
                TextColumn::make('units_issued')
                    ->label('Unidades Emitidas')
                    ->numeric()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total'),
                    ]),
                ToggleColumn::make('is_featured')
                    ->label('Destacado'),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('bond_type')
                    ->label('Tipo de Bono')
                    ->options(EnergyBond::getBondTypes())
                    ->multiple(),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(EnergyBond::getStatuses())
                    ->multiple(),
                SelectFilter::make('risk_level')
                    ->label('Nivel de Riesgo')
                    ->options(EnergyBond::getRiskLevels())
                    ->multiple(),
                SelectFilter::make('interest_frequency')
                    ->label('Frecuencia de Interés')
                    ->options(EnergyBond::getInterestFrequencies())
                    ->multiple(),
                SelectFilter::make('priority')
                    ->label('Prioridad')
                    ->options(EnergyBond::getPriorities())
                    ->multiple(),
                Filter::make('featured')
                    ->query(fn (Builder $query): Builder => $query->where('is_featured', true))
                    ->label('Solo Destacados'),
                Filter::make('public')
                    ->query(fn (Builder $query): Builder => $query->where('is_public', true))
                    ->label('Solo Públicos'),
                Filter::make('tax_free')
                    ->query(fn (Builder $query): Builder => $query->where('is_tax_free', true))
                    ->label('Libres de Impuestos'),
                Filter::make('guaranteed')
                    ->query(fn (Builder $query): Builder => $query->where('is_guaranteed', true))
                    ->label('Con Garantía'),
                Filter::make('maturity_range')
                    ->form([
                        DatePicker::make('maturity_from')
                            ->label('Vencimiento desde'),
                        DatePicker::make('maturity_until')
                            ->label('Vencimiento hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['maturity_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('maturity_date', '>=', $date),
                            )
                            ->when(
                                $data['maturity_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('maturity_date', '<=', $date),
                            );
                    })
                    ->label('Rango de Vencimiento'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->color('info'),
                Tables\Actions\EditAction::make()
                    ->color('warning'),
                Tables\Actions\DeleteAction::make()
                    ->color('danger'),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('success')
                    ->action(function (EnergyBond $record) {
                        $newRecord = $record->replicate();
                        $newRecord->bond_number = $newRecord->bond_number . '_copy';
                        $newRecord->name = $newRecord->name . ' (Copia)';
                        $newRecord->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_featured')
                        ->label('Marcar como Destacado')
                        ->icon('heroicon-o-star')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_featured' => true]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('mark_public')
                        ->label('Marcar como Público')
                        ->icon('heroicon-o-globe-alt')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_public' => true]);
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
            'index' => Pages\ListEnergyBonds::route('/'),
            'create' => Pages\CreateEnergyBond::route('/create'),
            'edit' => Pages\EditEnergyBond::route('/{record}/edit'),
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
        return static::getModel()::where('status', 'active')->count() > 0 ? 'success' : 'danger';
    }
}
