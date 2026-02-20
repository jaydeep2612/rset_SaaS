<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\KitchenQueue;
use App\Models\OrderStatusLog;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';
    protected static ?string $navigationLabel = 'Incoming Orders';
    protected static ?string $navigationGroup = 'Operations';

    public static function canAccess(): bool
    {
        return auth()->check()
            && auth()->user()->restaurant_id
            && in_array(auth()->user()->role->name, ['restaurant_admin', 'manager']);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('restaurant_id', auth()->user()->restaurant_id)
            ->with(['items.menuItem', 'table'])
            // Custom ordering to keep 'placed' orders (Pending) at the top
            ->orderByRaw("FIELD(status, 'placed', 'preparing', 'ready', 'served', 'cancelled')")
            ->orderBy('created_at', 'desc');
    }

   public static function table(Table $table): Table
{
    return $table
        ->poll('10s')
        ->contentGrid([
            'default' => 1,
            'md' => 2,
            'xl' => 3,
            '2xl' => 4,
        ])
        ->recordClasses(fn (Order $record) => match ($record->status) {
            'placed' => 'bg-white dark:bg-gray-800 border-l-4 border-danger-500 shadow-md rounded-xl p-4 h-full flex flex-col justify-between',
            default => 'bg-white dark:bg-gray-800 border-l-4 border-gray-200 dark:border-gray-700 shadow rounded-xl p-4 h-full flex flex-col justify-between opacity-75',
        })
        ->columns([
            Tables\Columns\Layout\Stack::make([
                
                // --- 1. HEADER: Table Number & Time (Fixed Visibility) ---
                Tables\Columns\Layout\Split::make([
                    // Table Number (with fallback if null)
                    Tables\Columns\TextColumn::make('restaurantTable.table_number')
                        ->formatStateUsing(fn ($state) => $state ? "Table {$state}" : "Takeaway") 
                        ->weight(FontWeight::Black)
                        ->color('gray')
                        ->size(Tables\Columns\TextColumn\TextColumnSize::Large)
                        ->extraAttributes(['class' => 'text-xl font-black uppercase tracking-tight text-gray-900 dark:text-white']), // Forced styling

                    // Time Ago
                    Tables\Columns\TextColumn::make('created_at')
                        ->since()
                        ->badge()
                        ->color('gray')
                        ->alignEnd(),
                ])->extraAttributes(['class' => 'items-center mb-2']),

                // --- 2. SUB-INFO: Customer & Priority ---
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\TextColumn::make('priority_label')
                        ->default('HIGH PRIORITY')
                        ->weight(FontWeight::Bold)
                        ->color('danger')
                        ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall),

                    Tables\Columns\TextColumn::make('customer_name')
                        ->icon('heroicon-m-user')
                        ->limit(12) // Shorten the hash/name
                        ->color('gray')
                        ->alignEnd(),
                ])->extraAttributes(['class' => 'mb-4 border-b border-gray-100 dark:border-gray-700 pb-2']),

                // --- 3. ITEMS LIST (Fixed Spacing) ---
                Tables\Columns\TextColumn::make('order_items')
                    ->state(fn (Order $record) => $record->items)
                    ->formatStateUsing(function ($state, Order $record) {
                        $html = '<div class="w-full space-y-3 mb-4">'; // Increased spacing between rows
                        
                        foreach ($record->items as $item) {
                            $totalItemPrice = (float)$item->unit_price * $item->quantity;
                            $priceFormatted = '₹' . number_format($totalItemPrice, 2);
                            $itemName = $item->menuItem ? $item->menuItem->name : $item->item_name;
                            
                            $html .= "
                                <div class='flex justify-between items-start text-sm w-full'>
                                    <div class='flex items-start gap-2 pr-4'> 
                                        <span class='font-bold text-gray-900 dark:text-white whitespace-nowrap'>{$item->quantity}x</span>
                                        <span class='text-gray-700 dark:text-gray-300 leading-tight'>{$itemName}</span>
                                    </div>

                                    <span class='font-bold text-gray-900 dark:text-white whitespace-nowrap pl-2'>
                                        {$priceFormatted}
                                    </span>
                                </div>
                            ";
                            
                            if ($item->notes) {
                                $html .= "<div class='text-xs text-red-500 ml-6 italic mt-1'>📝 {$item->notes}</div>";
                            }
                        }
                        $html .= '</div>';
                        return new HtmlString($html);
                    })
                    ->html(),

                // --- 4. TOTAL ---
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\TextColumn::make('total_label')
                        ->default('Total')
                        ->weight(FontWeight::Bold)
                        ->color('gray'),

                    Tables\Columns\TextColumn::make('total_amount')
                        ->prefix('₹') 
                        ->numeric(decimalPlaces: 2)
                        ->weight(FontWeight::Black)
                        ->size(Tables\Columns\TextColumn\TextColumnSize::Large)
                        ->alignEnd(),
                ])->extraAttributes(['class' => 'mt-auto pt-3 border-t border-dashed border-gray-300 dark:border-gray-600']),
            ])->space(2),
        ])
        ->actions([
            Tables\Actions\ActionGroup::make([
                Tables\Actions\Action::make('cancel')
                    ->label('Reject')
                    ->button()
                    ->color('danger')
                    ->icon('heroicon-m-x-mark')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record) => $record->status === 'placed')
                    ->action(fn (Order $record) => static::processOrder($record, 'cancelled'))
                    ->extraAttributes(['class' => 'w-full flex-1']),

                Tables\Actions\Action::make('confirm')
                    ->label('Accept')
                    ->button()
                    ->color('success')
                    ->icon('heroicon-m-check')
                    ->visible(fn (Order $record) => $record->status === 'placed')
                    ->action(fn (Order $record) => static::processOrder($record, 'preparing'))
                    ->extraAttributes(['class' => 'w-full flex-1']),
                    
                 Tables\Actions\Action::make('served')
                    ->label('Mark Served')
                    ->button()
                    ->color('success')
                    ->visible(fn (Order $record) => $record->status === 'ready')
                    ->action(fn (Order $record) => static::processOrder($record, 'served'))
                    ->extraAttributes(['class' => 'w-full']),
            ])
            ->dropdown(false)
            ->extraAttributes(['class' => 'flex gap-3 w-full mt-2'])
        ]);
}
    public static function processOrder(Order $record, $status)
    {
        $record->update(['status' => $status]);

        if ($status === 'preparing') {
            KitchenQueue::create([
                'order_id' => $record->id,
                'current_status' => 'placed',
                'priority' => 0,
            ]);
        }

        OrderStatusLog::create([
            'order_id' => $record->id,
            'from_status' => 'placed',
            'to_status' => $status,
            'changed_by' => auth()->id(),
        ]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
        ];
    }
}