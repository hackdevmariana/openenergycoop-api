<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerProfileResource\Pages;
use App\Models\CustomerProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Enums\CommonEnums;

class CustomerProfileResource extends Resource
{
    protected static ?string $model = CustomerProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Clientes y Proveedores';
    protected static ?string $navigationLabel = 'Perfiles de Cliente';
    protected static ?string $pluralModelLabel = 'Perfiles de Cliente';
    protected static ?string $modelLabel = 'Perfil de Cliente';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información básica')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required()
                            ->label('Usuario'),

                        Forms\Components\Select::make('organization_id')
                            ->relationship('organization', 'name')
                            ->searchable()
                            ->required()
                            ->label('Organización'),

                        Forms\Components\Select::make('profile_type')
                            ->options(CommonEnums::PROFILE_TYPES)
                            ->required()
                            ->label('Tipo de Perfil'),

                        Forms\Components\Select::make('legal_id_type')
                            ->options(CommonEnums::LEGAL_ID_TYPES)
                            ->required()
                            ->label('Tipo de Documento'),

                        Forms\Components\TextInput::make('legal_id_number')
                            ->required()
                            ->maxLength(255)
                            ->label('Número de Documento'),

                        Forms\Components\TextInput::make('legal_name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nombre Legal'),

                        Forms\Components\Select::make('contract_type')
                            ->options(CommonEnums::CONTRACT_TYPES)
                            ->required()
                            ->label('Tipo de Contrato'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('organization.name')
                    ->label('Organización')
                    ->sortable(),

                Tables\Columns\TextColumn::make('profile_type')
                    ->label('Tipo de Perfil')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'individual' => 'success',
                        'tenant' => 'warning',
                        'company' => 'info',
                        'ownership_change' => 'danger',
                    }),

                Tables\Columns\TextColumn::make('legal_id_type')
                    ->label('Documento')
                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),

                Tables\Columns\TextColumn::make('legal_name')
                    ->label('Nombre Legal')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('profile_type')
                    ->options(CommonEnums::PROFILE_TYPES)
                    ->label('Filtrar por Tipo'),

                Tables\Filters\SelectFilter::make('organization_id')
                    ->relationship('organization', 'name')
                    ->label('Filtrar por Organización'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListCustomerProfiles::route('/'),
            'create' => Pages\CreateCustomerProfile::route('/create'),
            'edit' => Pages\EditCustomerProfile::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $tenantCount = static::getModel()::where('profile_type', 'tenant')->count();
        
        if ($tenantCount > 0) {
            return 'warning';  // 🟡 Naranja para inquilinos (requiere atención)
        }
        
        $ownershipChangeCount = static::getModel()::where('profile_type', 'ownership_change')->count();
        
        if ($ownershipChangeCount > 0) {
            return 'danger';   // 🔴 Rojo para cambios de propiedad (crítico)
        }
        
        return 'success';      // 🟢 Verde cuando todo está bien
    }
}
