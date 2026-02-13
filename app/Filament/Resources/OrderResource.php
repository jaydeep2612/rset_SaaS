<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationLabel = 'Orders';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?int $navigationSort = 1;

    /* ---------------- ACCESS ---------------- */
    public static function canAccess(): bool
    {
        return auth()->check()
            && auth()->user()->restaurant_id
            && in_array(auth()->user()->role->name, [
                'restaurant_admin',
                'manager',
            ]);
    }

    /* ---------------- TENANCY ---------------- */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('restaurant_id', auth()->user()->restaurant_id);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('status')->disabled(),
            Forms\Components\TextInput::make('customer_name')->disabled(),
            Forms\Components\Textarea::make('notes')->disabled(),
            Forms\Components\TextInput::make('subtotal')->disabled(),
            Forms\Components\TextInput::make('tax')->disabled(),
            Forms\Components\TextInput::make('total_amount')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
         return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Order #')
                    ->sortable(),

                Tables\Columns\TextColumn::make('table.name')
                    ->label('Table'),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'placed',
                        'warning' => 'preparing',
                        'info' => 'ready',
                        'success' => 'served',
                        'danger' => 'cancelled',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->money('INR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('advance_status')
                    ->label('Advance Status')
                    ->visible(fn (Order $record) =>
                        auth()->user()->role->name === 'restaurant_admin'
                        && $record->status !== 'completed'
                    )
                    ->action(function (Order $record) {
                        $next = match ($record->status) {
                            'placed' => 'preparing',
                            'preparing' => 'ready',
                            'ready' => 'served',
                            'served' => 'completed',
                            default => null,
                        };

                        if ($next) {
                            OrderStatusService::transition(
                                $record,
                                $next,
                                auth()->user()->email
                            );
                        }
                    }),

                Tables\Actions\Action::make('cancel')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn () =>
                        auth()->user()->role->name === 'restaurant_admin'
                    )
                    ->action(fn (Order $record) =>
                        OrderStatusService::transition(
                            $record,
                            'cancelled',
                            auth()->user()->email
                        )
                    ),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            //'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
