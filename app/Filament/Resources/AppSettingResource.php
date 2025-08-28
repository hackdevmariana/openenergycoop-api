<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppSettingResource\Pages;
use App\Filament\Resources\AppSettingResource\RelationManagers;
use App\Models\AppSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppSettingResource extends Resource
{
    protected static ?string $model = AppSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    
    protected static ?string $navigationGroup = 'Administración del Sistema';
    
    protected static ?string $navigationLabel = 'Configuraciones';
    
    protected static ?string $modelLabel = 'Configuración';
    
    protected static ?string $pluralModelLabel = 'Configuraciones';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('organization_id')
                    ->relationship('organization', 'name')
                    ->required(),
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('slogan')->maxLength(255),
                Forms\Components\TextInput::make('primary_color')->maxLength(7),
                Forms\Components\TextInput::make('secondary_color')->maxLength(7),
                Forms\Components\TextInput::make('locale')->maxLength(5),
                Forms\Components\Textarea::make('custom_js'),
                // Para manejar mediaLibrary (logo y favicon) puedes usar:
                Forms\Components\SpatieMediaLibraryFileUpload::make('logo')
                    ->collection('logo')
                    ->label('Logo')
                    ->image()
                    ->preserveFilenames()
                    ->openable()
                    ->downloadable(),
                Forms\Components\SpatieMediaLibraryFileUpload::make('favicon')
                    ->collection('favicon')
                    ->label('Favicon')
                    ->image()
                    ->preserveFilenames()
                    ->openable()
                    ->downloadable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('organization.name')->label('Organización'),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('slogan'),
                Tables\Columns\TextColumn::make('locale'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListAppSettings::route('/'),
            'create' => Pages\CreateAppSetting::route('/create'),
            'edit' => Pages\EditAppSetting::route('/{record}/edit'),
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
            return 'gray';         // ⚫ Gris cuando no hay configuraciones
        }
        
        $configuredCount = static::getModel()::whereNotNull('favicon_path')
            ->orWhereNotNull('primary_color')
            ->orWhereNotNull('secondary_color')
            ->orWhereNotNull('slogan')
            ->orWhereNotNull('name')
            ->count();
        
        $configurationRate = $configuredCount / $totalCount;
        
        if ($configurationRate >= 0.8) {
            return 'success';      // 🟢 Verde cuando el 80%+ está bien configurado
        }
        
        if ($configurationRate >= 0.6) {
            return 'info';         // 🔵 Azul cuando el 60%+ está bien configurado
        }
        
        if ($configurationRate >= 0.4) {
            return 'warning';      // 🟡 Naranja cuando el 40%+ está bien configurado
        }
        
        return 'danger';           // 🔴 Rojo cuando menos del 40% está bien configurado
    }
}
