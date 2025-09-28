<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerProfileContactInfoResource\Pages;
use App\Models\CustomerProfileContactInfo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerProfileContactInfoResource extends Resource
{
    protected static ?string $model = CustomerProfileContactInfo::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Clientes y Proveedores';
    protected static ?string $navigationLabel = 'Información de Contacto';
    protected static ?string $pluralModelLabel = 'Informaciones de Contacto';
    protected static ?string $modelLabel = 'Información de Contacto';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de contacto')
                    ->schema([
                        Forms\Components\Select::make('customer_profile_id')
                            ->relationship('customerProfile', 'legal_name')
                            ->searchable()
                            ->required()
                            ->label('Perfil de Cliente'),

                        Forms\Components\Select::make('organization_id')
                            ->relationship('organization', 'name')
                            ->searchable()
                            ->required()
                            ->label('Organización'),

                        Forms\Components\TextInput::make('billing_email')
                            ->email()
                            ->label('Email de Facturación')
                            ->placeholder('billing@example.com'),

                        Forms\Components\TextInput::make('technical_email')
                            ->email()
                            ->label('Email Técnico')
                            ->placeholder('tech@example.com'),

                        Forms\Components\Textarea::make('address')
                            ->required()
                            ->label('Dirección')
                            ->rows(3)
                            ->placeholder('Calle, número, piso...'),

                        Forms\Components\TextInput::make('postal_code')
                            ->required()
                            ->label('Código Postal')
                            ->maxLength(10),

                        Forms\Components\TextInput::make('city')
                            ->required()
                            ->label('Ciudad')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('province')
                            ->required()
                            ->label('Provincia')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('iban')
                            ->label('IBAN')
                            ->maxLength(34)
                            ->placeholder('ES91 2100 0418 4502 0005 1332'),

                        Forms\Components\TextInput::make('cups')
                            ->label('CUPS')
                            ->maxLength(20)
                            ->placeholder('ES002100000000000000AA'),

                        Forms\Components\DateTimePicker::make('valid_from')
                            ->required()
                            ->label('Válido desde')
                            ->default(now()),

                        Forms\Components\DateTimePicker::make('valid_to')
                            ->label('Válido hasta')
                            ->after('valid_from'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customerProfile.legal_name')
                    ->label('Cliente')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('organization.name')
                    ->label('Organización')
                    ->sortable(),

                Tables\Columns\TextColumn::make('billing_email')
                    ->label('Email Facturación')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('city')
                    ->label('Ciudad')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('province')
                    ->label('Provincia')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('valid_from')
                    ->label('Válido desde')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('valid_to')
                    ->label('Válido hasta')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Sin fecha límite'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('organization_id')
                    ->relationship('organization', 'name')
                    ->label('Filtrar por Organización'),

                Tables\Filters\SelectFilter::make('province')
                    ->options(fn () => CustomerProfileContactInfo::distinct()
                        ->pluck('province', 'province')
                        ->toArray())
                    ->label('Filtrar por Provincia'),

                Tables\Filters\Filter::make('valid_now')
                    ->label('Válidos actualmente')
                    ->query(fn ($query) => $query->where('valid_from', '<=', now())
                        ->where(function($q) {
                            $q->whereNull('valid_to')
                              ->orWhere('valid_to', '>=', now());
                        })),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListCustomerProfileContactInfos::route('/'),
            'create' => Pages\CreateCustomerProfileContactInfo::route('/create'),
            'edit' => Pages\EditCustomerProfileContactInfo::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $expiredCount = static::getModel()::where('valid_to', '<', now())->count();
        
        if ($expiredCount > 0) {
            return 'danger';   // 🔴 Rojo para contactos expirados (crítico)
        }
        
        $expiringSoonCount = static::getModel()::where('valid_to', '>=', now())
            ->where('valid_to', '<=', now()->addDays(30))
            ->count();
        
        if ($expiringSoonCount > 0) {
            return 'warning';  // 🟡 Naranja para contactos que expiran pronto
        }
        
        return 'success';      // 🟢 Verde cuando todo está vigente
    }
}
