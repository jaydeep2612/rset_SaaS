<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RestaurantTableResource\Pages;
use App\Filament\Resources\RestaurantTableResource\RelationManagers;
use App\Models\RestaurantTable;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use App\Services\Restaurant\QrCodeService;
use Filament\Tables\Columns\ImageColumn;
use App\Services\Restaurant\QrZipService;
use Filament\Tables\Actions\Action;
class RestaurantTableResource extends Resource
{
    protected static ?string $model = RestaurantTable::class;

    protected static ?string $navigationIcon = 'heroicon-o-qr-code';
    protected static ?string $navigationLabel = 'Tables & QR';
    protected static ?string $navigationGroup = 'Restaurant Setup';

    public static function canAccess(): bool
    {
        return auth()->check()
            && auth()->user()->restaurant_id !== null
            && in_array(auth()->user()->role->name, ['restaurant_admin', 'manager']);
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('restaurant_id', auth()->user()->restaurant_id);
    }

   /*
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('restaurant_id')
                    ->relationship('restaurant', 'name')
                    ->required(),
                Forms\Components\TextInput::make('table_number')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('seating_capacity')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
            ]);
    }
            */
     public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('table_number')
                    ->label('Table Number')
                    ->required()
                    ->maxLength(20),

                Forms\Components\TextInput::make('seating_capacity')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->required(),

                Forms\Components\Toggle::make('is_active')
                    ->default(true),
            ]);
    }

    // public static function table(Table $table): Table
    // {
    //     return $table
    //         ->columns([
    //             Tables\Columns\TextColumn::make('table_number')
    //                 ->label('Table No.')
    //                 ->sortable(),

    //             Tables\Columns\TextColumn::make('seating_capacity')
    //                 ->label('Capacity'),

    //             Tables\Columns\IconColumn::make('is_active')
    //                 ->boolean(),

    //             Tables\Columns\TextColumn::make('created_at')
    //                 ->dateTime(),
    //         ])
    //         ->actions([
    //             Tables\Actions\ViewAction::make(),
    //         ]);
    // }

    public static function table(Table $table): Table
    {
        return $table
            /*->columns([
                Tables\Columns\TextColumn::make('table_number'),
                Tables\Columns\TextColumn::make('seating_capacity'),
                ImageColumn::make('qr_path')
                        ->label('QR')
                        ->disk('public')
                        ->height(80)
                        ->visibility('public'),
                Tables\Columns\TextColumn::make('qr_token'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                // Tables\Actions\Action::make('download_qr')
                //         ->label('Download QR')
                //         ->icon('heroicon-o-arrow-down-tray')
                //         ->action(fn ($record) =>
                //             response()->download(
                //                 storage_path('app/public/' . $record->qr_path)
                //             )
                //         ),
            ])*/
                ->columns([
                        Tables\Columns\TextColumn::make('table_number')
                            ->label('Table No')
                            ->sortable(),

                        Tables\Columns\TextColumn::make('seating_capacity')
                            ->sortable(),

                        ImageColumn::make('qr_path')
                            ->label('QR')
                            ->disk('public')
                            ->height(80)
                            ->visibility('public'),

                        Tables\Columns\IconColumn::make('is_active')
                            ->boolean(),
                    ])
                    ->actions([
                        Tables\Actions\Action::make('download_qr')
                            ->label('Download QR')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->action(fn ($record) =>
                                response()->download(
                                    storage_path('app/public/' . $record->qr_path)
                                )
                            ),
                            

                        /*Tables\Actions\Action::make('regenerate_qr')
                            ->label('Regenerate QR')
                            ->icon('heroicon-o-arrow-path')
                            ->color('warning')
                            ->requiresConfirmation()
                            ->action(fn ($record) =>
                                app(\App\Services\Restaurant\QrCodeService::class)
                                    ->regenerate(auth()->user()->restaurant, $record)
                            ),*/
                        
                        Tables\Actions\EditAction::make(),
                        Tables\Actions\DeleteAction::make(),
                    ])
                    ->bulkActions([
                        Tables\Actions\BulkAction::make('download_selected_qr')
                                ->label('Download Selected QRs')
                                ->icon('heroicon-o-archive-box-arrow-down')
                                ->action(function ($records) {
                                    $zipPath = app(QrZipService::class)
                                        ->createForTables($records);

                                    return response()
                                        ->download($zipPath)
                                        ->deleteFileAfterSend(true);
                                })
                                ->requiresConfirmation(),
                            Tables\Actions\DeleteBulkAction::make(),
                    ])
            ->headerActions([
    //            Action::make('download_all_qr')
    // ->label('Download All Table QRs')
    // ->icon('heroicon-o-archive-box-arrow-down')
    // ->action(function () {
    //     $restaurant = auth()->user()->restaurant;

    //     $zipPath = app(QrZipService::class)->createZip($restaurant);

    //     return response()
    //         ->download($zipPath)
    //         ->deleteFileAfterSend(true);
    // }),
                    Tables\Actions\Action::make('download_all_qr')
                            ->label('Download All Table QRs')
                            ->icon('heroicon-o-archive-box-arrow-down')
                            ->action(function () {
                                // $record is RestaurantTable ✅
                                $restaurant = auth()->user()->restaurant;

                                $zipPath = app(QrZipService::class)
                                    ->createForRestaurant($restaurant);

                                return response()
                                    ->download($zipPath)
                                    ->deleteFileAfterSend(true);
                            }),
                Tables\Actions\Action::make('generateTables')
                    ->label('Generate Tables')
                    ->icon('heroicon-o-qr-code')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('total_tables')
                            ->numeric()
                            ->minValue(1)
                            ->required(),

                        \Filament\Forms\Components\TextInput::make('seating_capacity')
                            ->numeric()
                            ->default(1),
                    ])
                    ->action(function (array $data) {
                        $user = auth()->user();
                        $restaurant = $user->restaurant;

                        $start = RestaurantTable::where('restaurant_id', $restaurant->id)->count();

                        $qrService = app(QrCodeService::class);

                        for ($i = 1; $i <= $data['total_tables']; $i++) {
                            $table = RestaurantTable::create([
                                'restaurant_id' => $restaurant->id,
                                'table_number' => 'T' . ($start + $i),
                                'seating_capacity' => $data['seating_capacity'],
                            ]);

                            $qrService->generate($table);
                        }
                    }),
                    // Tables\Actions\BulkAction::make('download_all_qr')
                    // ->label('Download All QR')
                    // ->icon('heroicon-o-archive-box')
                    // ->action(function ($records) {
                    //     $restaurant = auth()->user()->restaurant;

                    //     $zipPath = app(QrZipService::class)
                    //         ->createZip($restaurant, $records);

                    //     return response()->download($zipPath);
                    // }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function cancreate(): bool
    {
        return false; // Disable manual creation
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRestaurantTables::route('/'),
           // 'create' => Pages\CreateRestaurantTable::route('/create'),
            'edit' => Pages\EditRestaurantTable::route('/{record}/edit'),
        ];
    }
}
