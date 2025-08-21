<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SocialLinkResource\Pages;
use App\Models\SocialLink;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class SocialLinkResource extends Resource
{
    protected static ?string $model = SocialLink::class;

    protected static ?string $navigationIcon = 'heroicon-o-share';
    
    protected static ?string $navigationGroup = 'Comunicación';
    
    protected static ?string $navigationLabel = 'Redes Sociales';
    
    protected static ?string $modelLabel = 'Red Social';
    
    protected static ?string $pluralModelLabel = 'Redes Sociales';
    
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información de la Red Social')
                    ->schema([
                        Forms\Components\Select::make('platform')
                            ->label('Plataforma')
                            ->options([
                                'facebook' => 'Facebook',
                                'twitter' => 'Twitter/X',
                                'instagram' => 'Instagram',
                                'linkedin' => 'LinkedIn',
                                'youtube' => 'YouTube',
                                'tiktok' => 'TikTok',
                                'telegram' => 'Telegram',
                                'whatsapp' => 'WhatsApp',
                                'discord' => 'Discord',
                                'github' => 'GitHub',
                                'mastodon' => 'Mastodon',
                                'other' => 'Otra',
                            ])
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Auto-completar algunos campos basado en la plataforma
                                $icons = [
                                    'facebook' => 'fab-facebook',
                                    'twitter' => 'fab-twitter',
                                    'instagram' => 'fab-instagram',
                                    'linkedin' => 'fab-linkedin',
                                    'youtube' => 'fab-youtube',
                                    'tiktok' => 'fab-tiktok',
                                    'telegram' => 'fab-telegram',
                                    'whatsapp' => 'fab-whatsapp',
                                    'discord' => 'fab-discord',
                                    'github' => 'fab-github',
                                    'mastodon' => 'fab-mastodon',
                                ];
                                
                                if (isset($icons[$state])) {
                                    $set('icon', $icons[$state]);
                                }
                            }),
                            
                        Forms\Components\TextInput::make('url')
                            ->label('URL')
                            ->url()
                            ->required()
                            ->placeholder('https://facebook.com/tu-pagina')
                            ->helperText('URL completa del perfil o página'),
                            
                        Forms\Components\TextInput::make('username')
                            ->label('Usuario/Handle')
                            ->placeholder('@usuario')
                            ->helperText('Nombre de usuario en la plataforma'),
                    ])->columns(3),
                    
                Section::make('Personalización')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Título Personalizado')
                            ->placeholder('Síguenos en Facebook')
                            ->helperText('Título personalizado para mostrar'),
                            
                        Forms\Components\TextInput::make('icon')
                            ->label('Icono')
                            ->placeholder('fab-facebook, heroicon-o-share, etc.')
                            ->helperText('Clase CSS del icono (FontAwesome, Heroicons)'),
                            
                        Forms\Components\ColorPicker::make('color')
                            ->label('Color')
                            ->helperText('Color personalizado para esta red social'),
                    ])->columns(3),
                    
                Section::make('Configuración')
                    ->schema([
                        Forms\Components\TextInput::make('position')
                            ->label('Orden')
                            ->numeric()
                            ->default(0)
                            ->helperText('Orden de aparición (menor número = mayor prioridad)'),
                            
                        Forms\Components\Toggle::make('active')
                            ->label('Activo')
                            ->helperText('Solo las redes sociales activas se muestran')
                            ->default(true),
                            
                        Forms\Components\Toggle::make('show_in_footer')
                            ->label('Mostrar en Footer')
                            ->helperText('Mostrar en el pie de página')
                            ->default(true),
                            
                        Forms\Components\Toggle::make('show_in_header')
                            ->label('Mostrar en Header')
                            ->helperText('Mostrar en la cabecera')
                            ->default(false),
                    ])->columns(4),
                    
                Section::make('Estadísticas')
                    ->schema([
                        Forms\Components\TextInput::make('followers_count')
                            ->label('Número de Seguidores')
                            ->numeric()
                            ->placeholder('0')
                            ->helperText('Número de seguidores (opcional)'),
                            
                        Forms\Components\DateTimePicker::make('last_updated')
                            ->label('Última Actualización')
                            ->helperText('Cuándo se actualizaron las estadísticas'),
                    ])->columns(2),
                    
                Section::make('Organización')
                    ->schema([
                        Forms\Components\Select::make('organization_id')
                            ->label('Organización')
                            ->relationship('organization', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Global (todas las organizaciones)'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('platform_icon')
                    ->label('')
                    ->getStateUsing(function ($record) {
                        $icons = [
                            'facebook' => 'heroicon-o-globe-alt',
                            'twitter' => 'heroicon-o-globe-alt',
                            'instagram' => 'heroicon-o-camera',
                            'linkedin' => 'heroicon-o-building-office',
                            'youtube' => 'heroicon-o-play',
                            'telegram' => 'heroicon-o-chat-bubble-left-right',
                            'whatsapp' => 'heroicon-o-phone',
                            'github' => 'heroicon-o-code-bracket',
                        ];
                        return $icons[$record->platform] ?? 'heroicon-o-share';
                    })
                    ->color(fn ($record) => match($record->platform) {
                        'facebook' => 'primary',
                        'twitter' => 'info',
                        'instagram' => 'danger',
                        'linkedin' => 'primary',
                        'youtube' => 'danger',
                        'telegram' => 'info',
                        'whatsapp' => 'success',
                        'github' => 'gray',
                        default => 'secondary',
                    }),
                    
                Tables\Columns\BadgeColumn::make('platform')
                    ->label('Plataforma')
                    ->colors([
                        'primary' => 'facebook',
                        'info' => 'twitter',
                        'danger' => ['instagram', 'youtube'],
                        'success' => 'whatsapp',
                        'warning' => 'tiktok',
                        'secondary' => ['telegram', 'discord'],
                        'gray' => 'github',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'facebook' => 'Facebook',
                        'twitter' => 'Twitter/X',
                        'instagram' => 'Instagram',
                        'linkedin' => 'LinkedIn',
                        'youtube' => 'YouTube',
                        'tiktok' => 'TikTok',
                        'telegram' => 'Telegram',
                        'whatsapp' => 'WhatsApp',
                        'discord' => 'Discord',
                        'github' => 'GitHub',
                        'mastodon' => 'Mastodon',
                        'other' => 'Otra',
                        default => $state,
                    }),
                    
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->placeholder('Sin título personalizado')
                    ->limit(30),
                    
                Tables\Columns\TextColumn::make('username')
                    ->label('Usuario')
                    ->searchable()
                    ->placeholder('Sin usuario')
                    ->copyable(),
                    
                Tables\Columns\TextColumn::make('followers_count')
                    ->label('Seguidores')
                    ->numeric()
                    ->placeholder('—')
                    ->alignCenter()
                    ->badge()
                    ->color('success')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('position')
                    ->label('Orden')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('secondary'),
                    
                Tables\Columns\IconColumn::make('show_in_footer')
                    ->label('Footer')
                    ->boolean()
                    ->trueIcon('heroicon-o-check')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('gray'),
                    
                Tables\Columns\IconColumn::make('show_in_header')
                    ->label('Header')
                    ->boolean()
                    ->trueIcon('heroicon-o-check')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('gray'),
                    
                Tables\Columns\IconColumn::make('active')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('gray'),
                    
                Tables\Columns\TextColumn::make('organization.name')
                    ->label('Organización')
                    ->placeholder('Global')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('platform')
                    ->label('Plataforma')
                    ->options([
                        'facebook' => 'Facebook',
                        'twitter' => 'Twitter/X',
                        'instagram' => 'Instagram',
                        'linkedin' => 'LinkedIn',
                        'youtube' => 'YouTube',
                        'tiktok' => 'TikTok',
                        'telegram' => 'Telegram',
                        'whatsapp' => 'WhatsApp',
                        'discord' => 'Discord',
                        'github' => 'GitHub',
                        'mastodon' => 'Mastodon',
                        'other' => 'Otra',
                    ]),
                    
                TernaryFilter::make('active')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos'),
                    
                TernaryFilter::make('show_in_footer')
                    ->label('En Footer')
                    ->placeholder('Todos')
                    ->trueLabel('Mostrar en footer')
                    ->falseLabel('No mostrar en footer'),
                    
                SelectFilter::make('organization_id')
                    ->label('Organización')
                    ->relationship('organization', 'name')
                    ->placeholder('Todas las organizaciones'),
            ])
            ->actions([
                Tables\Actions\Action::make('visit')
                    ->label('Visitar')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record) => $record->url)
                    ->openUrlInNewTab()
                    ->color('primary'),
                    
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('toggle_footer')
                    ->label(fn ($record) => $record->show_in_footer ? 'Ocultar Footer' : 'Mostrar Footer')
                    ->icon('heroicon-o-bars-3-bottom-left')
                    ->action(fn ($record) => $record->update(['show_in_footer' => !$record->show_in_footer])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activar seleccionados')
                        ->icon('heroicon-o-eye')
                        ->action(fn ($records) => $records->each->update(['active' => true]))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('show_in_footer')
                        ->label('Mostrar en footer')
                        ->icon('heroicon-o-bars-3-bottom-left')
                        ->action(fn ($records) => $records->each->update(['show_in_footer' => true]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('position', 'asc')
            ->reorderable('position');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSocialLinks::route('/'),
            'create' => Pages\CreateSocialLink::route('/create'),
            'edit' => Pages\EditSocialLink::route('/{record}/edit'),
        ];
    }
}