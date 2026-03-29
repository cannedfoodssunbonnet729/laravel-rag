<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class EmbeddingResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'RAG';

    protected static ?string $navigationLabel = 'Embedding Cache';

    protected static ?string $modelLabel = 'Cached Embedding';

    public static function getModel(): string
    {
        // Use a dynamic model for the embedding cache table
        return EmbeddingCacheModel::class;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('hash')
                    ->disabled(),
                Forms\Components\Textarea::make('text_preview')
                    ->disabled()
                    ->rows(4),
                Forms\Components\TextInput::make('created_at')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('hash')
                    ->limit(16)
                    ->searchable(),
                Tables\Columns\TextColumn::make('text_preview')
                    ->limit(80)
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \Moneo\LaravelRag\Filament\Resources\EmbeddingResource\Pages\ListEmbeddings::route('/'),
        ];
    }
}
