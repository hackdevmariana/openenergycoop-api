<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommentResource\Pages;
use App\Filament\Resources\CommentResource\RelationManagers;
use App\Models\Comment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Notifications\Notification;

class CommentResource extends Resource
{
    protected static ?string $model = Comment::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    
    protected static ?string $navigationLabel = 'Comentarios';
    
    protected static ?string $modelLabel = 'Comentario';
    
    protected static ?string $pluralModelLabel = 'Comentarios';
    
    protected static ?string $navigationGroup = 'Contenidos';
    
    protected static ?int $navigationSort = 5;
    
    protected static ?string $navigationBadge = null;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Comentario')
                    ->schema([
                        Forms\Components\TextInput::make('commentable_type')
                            ->label('Tipo de Contenido')
                            ->disabled()
                            ->formatStateUsing(fn ($state) => class_basename($state)),
                            
                        Forms\Components\TextInput::make('commentable_id')
                            ->label('ID del Contenido')
                            ->disabled(),
                            
                        Forms\Components\Select::make('user_id')
                            ->label('Usuario')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Usuario anónimo'),
                    ])->columns(3),
                    
                Section::make('Datos del Autor')
                    ->schema([
                        Forms\Components\TextInput::make('author_name')
                            ->label('Nombre del Autor')
                            ->maxLength(255)
                            ->placeholder('Solo para usuarios anónimos'),
                            
                        Forms\Components\TextInput::make('author_email')
                            ->label('Email del Autor')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('Solo para usuarios anónimos'),
                    ])->columns(2)
                    ->visible(fn ($record) => !$record?->user_id),
                    
                Section::make('Contenido')
                    ->schema([
                        Forms\Components\Textarea::make('content')
                            ->label('Contenido del Comentario')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
                    
                Section::make('Moderación')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options(Comment::STATUSES)
                            ->required()
                            ->default('pending'),
                            
                        Forms\Components\Select::make('parent_id')
                            ->label('Comentario Padre')
                            ->relationship('parent', 'content')
                            ->searchable()
                            ->placeholder('Sin comentario padre (comentario principal)')
                            ->formatStateUsing(fn ($state, $record) => 
                                $record?->parent ? \Str::limit($record->parent->content, 50) : null
                            ),
                            
                        Forms\Components\Toggle::make('is_pinned')
                            ->label('Fijado')
                            ->helperText('Los comentarios fijados aparecen en la parte superior'),
                    ])->columns(3),
                    
                Section::make('Estadísticas')
                    ->schema([
                        Forms\Components\TextInput::make('likes_count')
                            ->label('Me Gusta')
                            ->numeric()
                            ->default(0),
                            
                        Forms\Components\TextInput::make('dislikes_count')
                            ->label('No Me Gusta')
                            ->numeric()
                            ->default(0),
                    ])->columns(2),
                    
                Section::make('Información Técnica')
                    ->schema([
                        Forms\Components\TextInput::make('ip_address')
                            ->label('Dirección IP')
                            ->disabled(),
                            
                        Forms\Components\Textarea::make('user_agent')
                            ->label('User Agent')
                            ->disabled()
                            ->rows(2),
                    ])->columns(1)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('author_avatar')
                    ->label('Avatar')
                    ->getStateUsing(fn ($record) => $record->getAuthorAvatar())
                    ->circular()
                    ->width(40)
                    ->height(40),
                    
                Tables\Columns\TextColumn::make('author_name')
                    ->label('Autor')
                    ->getStateUsing(fn ($record) => $record->getAuthorName())
                    ->searchable(['author_name', 'user.name'])
                    ->sortable()
                    ->weight(FontWeight::Medium),
                    
                Tables\Columns\TextColumn::make('content')
                    ->label('Comentario')
                    ->limit(100)
                    ->searchable()
                    ->wrap(),
                    
                Tables\Columns\TextColumn::make('commentable_type')
                    ->label('Contenido')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'App\\Models\\Article' => 'Artículo',
                        'App\\Models\\Page' => 'Página',
                        'App\\Models\\Document' => 'Documento',
                        default => class_basename($state),
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'App\\Models\\Article' => 'success',
                        'App\\Models\\Page' => 'primary',
                        'App\\Models\\Document' => 'warning',
                        default => 'gray',
                    }),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'gray' => 'spam',
                    ])
                    ->formatStateUsing(fn ($state) => Comment::STATUSES[$state] ?? $state),
                    
                Tables\Columns\IconColumn::make('is_pinned')
                    ->label('Fijado')
                    ->boolean()
                    ->trueIcon('heroicon-o-bookmark')
                    ->falseIcon('heroicon-o-bookmark')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                    
                Tables\Columns\TextColumn::make('likes_count')
                    ->label('👍')
                    ->numeric()
                    ->alignCenter()
                    ->sortable()
                    ->color('success'),
                    
                Tables\Columns\TextColumn::make('dislikes_count')
                    ->label('👎')
                    ->numeric()
                    ->alignCenter()
                    ->sortable()
                    ->color('danger'),
                    
                Tables\Columns\TextColumn::make('depth')
                    ->label('Nivel')
                    ->getStateUsing(fn ($record) => $record->getDepth())
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('approved_at')
                    ->label('Aprobado')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('No aprobado')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(Comment::STATUSES)
                    ->default('pending'),
                    
                SelectFilter::make('commentable_type')
                    ->label('Tipo de Contenido')
                    ->options([
                        'App\\Models\\Article' => 'Artículos',
                        'App\\Models\\Page' => 'Páginas',
                        'App\\Models\\Document' => 'Documentos',
                    ]),
                    
                TernaryFilter::make('is_pinned')
                    ->label('Fijado')
                    ->placeholder('Todos')
                    ->trueLabel('Solo fijados')
                    ->falseLabel('No fijados'),
                    
                TernaryFilter::make('user_id')
                    ->label('Tipo de Usuario')
                    ->placeholder('Todos')
                    ->trueLabel('Usuarios registrados')
                    ->falseLabel('Usuarios anónimos')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('user_id'),
                        false: fn (Builder $query) => $query->whereNull('user_id'),
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (Comment $record) {
                        $record->approve(auth()->user());
                        Notification::make()
                            ->title('Comentario aprobado')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Comment $record) => $record->status === 'pending'),
                    
                Tables\Actions\Action::make('reject')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(function (Comment $record) {
                        $record->reject();
                        Notification::make()
                            ->title('Comentario rechazado')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Comment $record) => $record->status !== 'rejected')
                    ->requiresConfirmation(),
                    
                Tables\Actions\Action::make('spam')
                    ->label('Marcar como Spam')
                    ->icon('heroicon-o-shield-exclamation')
                    ->color('warning')
                    ->action(function (Comment $record) {
                        $record->markAsSpam();
                        Notification::make()
                            ->title('Comentario marcado como spam')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Comment $record) => $record->status !== 'spam')
                    ->requiresConfirmation(),
                    
                Tables\Actions\Action::make('pin')
                    ->label(fn (Comment $record) => $record->is_pinned ? 'Desfijar' : 'Fijar')
                    ->icon(fn (Comment $record) => $record->is_pinned ? 'heroicon-o-bookmark-slash' : 'heroicon-o-bookmark')
                    ->color('warning')
                    ->action(function (Comment $record) {
                        if ($record->is_pinned) {
                            $record->unpin();
                            $message = 'Comentario desfijado';
                        } else {
                            $record->pin();
                            $message = 'Comentario fijado';
                        }
                        
                        Notification::make()
                            ->title($message)
                            ->success()
                            ->send();
                    }),
                    
                Tables\Actions\EditAction::make()
                    ->label('Editar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar seleccionados'),
                    Tables\Actions\BulkAction::make('approve')
                        ->label('Aprobar seleccionados')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->approve(auth()->user());
                            Notification::make()
                                ->title('Comentarios aprobados')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('reject')
                        ->label('Rechazar seleccionados')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each->reject();
                            Notification::make()
                                ->title('Comentarios rechazados')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('spam')
                        ->label('Marcar como spam')
                        ->icon('heroicon-o-shield-exclamation')
                        ->color('warning')
                        ->action(function ($records) {
                            $records->each->markAsSpam();
                            Notification::make()
                                ->title('Comentarios marcados como spam')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s'); // Auto-refresh every 30 seconds
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
            'index' => Pages\ListComments::route('/'),
            'create' => Pages\CreateComment::route('/create'),
            'edit' => Pages\EditComment::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }
    
    public static function getNavigationBadgeColor(): string|array|null
    {
        $count = static::getNavigationBadge();
        
        if ($count > 10) {
            return 'danger';
        } elseif ($count > 5) {
            return 'warning';
        }
        
        return 'primary';
    }
}