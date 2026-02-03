<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RestaurantResource\Pages;
use App\Models\Restaurant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class RestaurantResource extends Resource
{
    protected static ?string $model = Restaurant::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'Restaurants';
    protected static ?string $navigationGroup = 'Super Admin';

    /**
     * 🔐 Only Super Admin can see this resource
     */
    public static function canAccess(): bool
    {
        return auth()->check()
            && auth()->user()->is_super_admin === true;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) =>
                    $set('slug', Str::slug($state))
                ),

            Forms\Components\TextInput::make('slug')
                ->disabled()
                ->dehydrated()
                ->required()
                ->unique(ignoreRecord: true),

            Forms\Components\FileUpload::make('logo_path')
                ->label('Restaurant Logo')
                ->image()
                ->imageEditor()
                ->disk('public')
                ->directory(fn ($get) =>
                    'restaurants/' . ($get('slug') ?? 'temp') . '/LOGO'
                )
                ->getUploadedFileNameForStorageUsing(
                    fn ($file) => 'logo.' . $file->getClientOriginalExtension()
                )
                ->acceptedFileTypes([
                    'image/png',
                    'image/jpeg',
                    'image/jpg',
                    'image/svg+xml',
                    'image/heif',
                    'image/webp',
                ])
                ->visibility('public')
                ->maxSize(2048)
                ->required(fn (string $operation) => $operation === 'create'),

            Forms\Components\TextInput::make('user_limits')
                ->numeric()
                ->minValue(1)
                ->required(),

            Forms\Components\Toggle::make('is_active')
                ->default(true),
        ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo_path')
                    ->disk('public')
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('user_limits')
                    ->label('User Limit')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

                // // Optional: Soft disable instead of delete
                // Tables\Actions\Action::make('Deactivate')
                //     ->visible(fn ($record) => $record->is_active)
                //     ->action(fn ($record) => $record->update(['is_active' => false]))
                //     ->color('danger'),
                // Tables\Actions\Action::make('Activate')
                //     ->visible(fn ($record) => ! $record->is_active)
                //     ->action(fn ($record) => $record->update(['is_active' => true]))
                //     ->color('success'),
            ])
            ->bulkActions([]); // 🚫 no bulk delete
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRestaurants::route('/'),
            'create' => Pages\CreateRestaurant::route('/create'),
            'edit' => Pages\EditRestaurant::route('/{record}/edit'),
        ];
    }
}
