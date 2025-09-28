<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrganizationFeatureResource\Pages;
use App\Filament\Resources\OrganizationFeatureResource\RelationManagers;
use App\Models\OrganizationFeature;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class OrganizationFeatureResource extends Resource
{
    protected static ?string $model = OrganizationFeature::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?string $navigationGroup = 'Usuarios y Organizaciones';
    protected static ?string $navigationLabel = 'Organization Features';
    protected static ?string $pluralModelLabel = 'Organization Features';
    protected static ?string $modelLabel = 'Organization Feature';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('organization_id')
                    ->label('Organization')
                    ->relationship('organization', 'name')
                    ->required()
                    ->searchable(),

                TextInput::make('feature_key')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Ejemplo: wallet, surveys, events'),

                Toggle::make('enabled_dashboard')
                    ->label('Enabled in Dashboard')
                    ->default(true),

                Toggle::make('enabled_web')
                    ->label('Enabled on Web')
                    ->default(true),

                Textarea::make('notes')
                    ->label('Notes')
                    ->rows(3)
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('organization.name')
                    ->label('Organization')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('feature_key')->sortable()->searchable(),

                IconColumn::make('enabled_dashboard')
                    ->boolean()
                    ->label('Enabled Dashboard'),

                IconColumn::make('enabled_web')
                    ->boolean()
                    ->label('Enabled Web'),

                TextColumn::make('notes')
                    ->limit(50)
                    ->wrap()
                    ->label('Notes'),
            ])
            ->defaultSort('organization.name');
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
            'index' => Pages\ListOrganizationFeatures::route('/'),
            'create' => Pages\CreateOrganizationFeature::route('/create'),
            'edit' => Pages\EditOrganizationFeature::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $totalCount = static::getModel()::count();
        
        if ($totalCount === 0) {
            return 'gray';         // ⚫ Gris cuando no hay características
        }
        
        $fullyEnabledCount = static::getModel()::where('enabled_dashboard', true)
            ->where('enabled_web', true)
            ->count();
        
        $fullyEnabledRate = $fullyEnabledCount / $totalCount;
        
        if ($fullyEnabledRate >= 0.9) {
            return 'success';      // 🟢 Verde cuando el 90%+ está completamente habilitado
        }
        
        if ($fullyEnabledRate >= 0.7) {
            return 'info';         // 🔵 Azul cuando el 70%+ está completamente habilitado
        }
        
        $partiallyEnabledCount = static::getModel()::where('enabled_dashboard', true)
            ->orWhere('enabled_web', true)
            ->count();
        
        $partiallyEnabledRate = $partiallyEnabledCount / $totalCount;
        
        if ($partiallyEnabledRate >= 0.5) {
            return 'warning';      // 🟡 Naranja cuando el 50%+ está parcialmente habilitado
        }
        
        return 'danger';           // 🔴 Rojo cuando menos del 50% está habilitado
    }
}
