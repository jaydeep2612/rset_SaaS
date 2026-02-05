<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Categories';

    protected static ?string $navigationGroup = 'Menu Management';

    protected static ?int $navigationSort = 1;

    /* ---------------------------------------------------------
     | ACCESS CONTROL
     |----------------------------------------------------------*/
    public static function canAccess(): bool
    {
        return auth()->check()
            && auth()->user()->restaurant_id !== null
            && in_array(auth()->user()->role->name, [
                'restaurant_admin',
                'manager',
            ]);
    }

    /* ---------------------------------------------------------
     | TENANT SCOPING
     |----------------------------------------------------------*/
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('restaurant_id', auth()->user()->restaurant_id);
    }

    /* ---------------------------------------------------------
     | FORM
     |----------------------------------------------------------*/
    public static function form(Form $form): Form
    {
        return $form->schema([
            // Forced restaurant binding
            Forms\Components\Hidden::make('restaurant_id')
                ->default(fn () => auth()->user()->restaurant_id)
                ->required(),

            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(100)
                ->unique(
                    table: 'categories',
                    column: 'name',
                    ignoreRecord: true,
                    modifyRuleUsing: fn ($rule) =>
                        $rule->where('restaurant_id', auth()->user()->restaurant_id)
                ),

            Forms\Components\TextInput::make('sort_order')
                ->numeric()
                ->default(0)
                ->hidden(),

            Forms\Components\Toggle::make('is_active')
                ->default(true),
        ]);
    }

    /* ---------------------------------------------------------
     | TABLE
     |----------------------------------------------------------*/
    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn () =>
                        auth()->user()->role->name === 'restaurant_admin'
                    ),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn () =>
                        auth()->user()->role->name === 'restaurant_admin'
                    ),
            ]);
    }

    /* ---------------------------------------------------------
     | PAGES
     |----------------------------------------------------------*/
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
