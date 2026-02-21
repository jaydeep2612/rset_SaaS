<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TableBillingResource\Pages;
use App\Models\RestaurantTable;
use App\Models\Payment;
use App\Models\Order;
use App\Models\QrSession;
use App\Models\OrderStatusLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class TableBillingResource extends Resource
{
    protected static ?string $model = RestaurantTable::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Billing Checkout';
    protected static ?string $navigationGroup = 'Finance';

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
            ->with([
                'sessions' => fn($q) => $q->where('is_active', true),
                'sessions.orders' => fn($q) => $q->where('status', '!=', 'cancelled'),
                'sessions.orders.items.menuItem.category', 
                'sessions.guests' => fn($q) => $q->where('is_active', true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->contentGrid([
                'default' => 1,
                'md' => 2,
                'xl' => 3,
                '2xl' => 4,
            ])
            // Uses Tailwind to nicely support both light and dark mode for the cards
            ->recordClasses(fn (RestaurantTable $record) => 'bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-800 rounded-xl flex flex-col')
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    
                    // --- 1. TABLE HEADER ---
                    Tables\Columns\TextColumn::make('table_number')
                        ->formatStateUsing(fn ($state) => "Table {$state}")
                        ->weight(FontWeight::Black)
                        ->color('primary')
                        ->alignCenter()
                        ->extraAttributes(['class' => 'text-2xl py-4 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50 rounded-t-xl']),

                    // --- 2. SUMMARY NUMBERS ---
                    Tables\Columns\Layout\Grid::make(2)->schema([
                        Tables\Columns\TextColumn::make('total_bill')
                            ->label('Total Bill')
                            ->state(function (RestaurantTable $record) {
                                return $record->sessions->flatMap->orders->sum('total_amount');
                            })
                            ->money('INR')
                            ->weight(FontWeight::Bold)
                            ->extraAttributes(['class' => 'text-center p-3']),

                        Tables\Columns\TextColumn::make('active_customers')
                            ->label('Active Diners')
                            ->state(function (RestaurantTable $record) {
                                return $record->sessions->count() . ' People';
                            })
                            ->color('info')
                            ->weight(FontWeight::Bold)
                            ->extraAttributes(['class' => 'text-center p-3']),
                    ]),

                    // --- 3. BALANCE DUE ---
                    Tables\Columns\TextColumn::make('due_amount')
                        ->label('Balance Due')
                        ->state(function (RestaurantTable $record) {
                            $total = $record->sessions->flatMap->orders->sum('total_amount');
                            $orderIds = $record->sessions->flatMap->orders->pluck('id');
                            $paid = Payment::whereIn('order_id', $orderIds)->where('status', 'paid')->sum('amount');
                            
                            return max(0, $total - $paid);
                        })
                        ->money('INR')
                        ->weight(FontWeight::Black)
                        ->size(Tables\Columns\TextColumn\TextColumnSize::Large)
                        ->color(fn ($state) => $state > 0 ? 'danger' : 'gray')
                        ->alignCenter()
                        ->extraAttributes(['class' => 'py-3 mt-2 bg-gray-50 dark:bg-gray-800 border-y border-gray-100 dark:border-gray-700']),

                ])->space(0),
            ])
            ->actions([

                /* ================= CHECKOUT ACTION ================= */
                Tables\Actions\Action::make('checkout')
                    ->label('Checkout & Settle')
                    ->icon('heroicon-o-credit-card')
                    ->button()
                    ->color('success')
                    ->modalHeading(fn (RestaurantTable $record) => "Checkout - Table {$record->table_number}")
                    ->modalWidth('6xl')
                    ->modalSubmitActionLabel('Confirm Payment & Clear Table')
                    ->fillForm(function (RestaurantTable $record): array {
                        $total = $record->sessions->flatMap->orders->sum('total_amount');
                        $orderIds = $record->sessions->flatMap->orders->pluck('id');
                        $paid = Payment::whereIn('order_id', $orderIds)->where('status', 'paid')->sum('amount');
                        
                        return [
                            'subtotal' => max(0, $total - $paid),
                            'tip' => 0, 
                        ];
                    })
                    ->form([
                        Forms\Components\Grid::make(12)->schema([
                            
                            // 📜 LEFT COLUMN: DETAILED MASTER RECEIPT
                            Forms\Components\Section::make('Master Order History')
                                ->columnSpan(7)
                                ->schema([
                                    Forms\Components\Placeholder::make('receipt')
                                        ->hiddenLabel()
                                        ->content(function (RestaurantTable $record) {
                                            
                                            // 🔥 FIX: Added explicit `color: #000000;` and `color: #1f2937;` to override Dark Mode defaults
                                            $html = '<div style="max-height: 500px; overflow-y: auto; padding: 20px; background-color: #ffffff; color: #000000; border: 1px solid #e5e7eb; border-radius: 8px; font-family: monospace;">';
                                            $html .= '<h2 style="text-align: center; font-size: 20px; font-weight: 900; margin-bottom: 5px; color: #000000;">TABLE ' . $record->table_number . '</h2>';
                                            $html .= '<div style="text-align: center; font-size: 12px; color: #4b5563; border-bottom: 2px dashed #000000; padding-bottom: 15px; margin-bottom: 15px;">FINAL BILLING SUMMARY</div>';
                                            
                                            $hasOrders = false;
                                            $grandTotal = 0;
                                            $totalOrdersCount = 0;

                                            // 1. Identify the Primary Host Session
                                            $primarySession = $record->sessions->where('is_primary', true)->first();

                                            if ($primarySession) {
                                                // --- HOST ORDERS ---
                                                $hostOrdersCount = $primarySession->orders->count();
                                                $hasOrders = $hasOrders || $hostOrdersCount > 0;

                                                $html .= "<div style='margin-bottom: 20px;'>";
                                                $html .= "<div style='font-size: 16px; font-weight: bold; background-color: #f3f4f6; color: #111827; padding: 8px; border-radius: 5px;'>👑 HOST: {$primarySession->customer_name} <span style='font-weight: normal; font-size: 12px; float: right; color: #4b5563;'>({$hostOrdersCount} Orders)</span></div>";
                                                
                                                foreach ($primarySession->orders as $order) {
                                                    $totalOrdersCount++;
                                                    $html .= "<div style='margin-top: 10px; padding-left: 10px; border-left: 2px solid #e5e7eb;'>";
                                                    $html .= "<div style='font-size: 12px; color: #6b7280; margin-bottom: 4px;'>Order #{$order->id}</div>";
                                                    
                                                    foreach ($order->items as $item) {
                                                        $name = $item->menuItem ? $item->menuItem->name : $item->item_name;
                                                        $category = $item->menuItem?->category ? strtoupper($item->menuItem->category->name) : 'GENERAL';
                                                        
                                                        $html .= "
                                                        <div style='display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 4px; color: #111827;'>
                                                            <span><strong>{$item->quantity}x</strong> {$name} <br><span style='font-size: 10px; color: #6b7280;'>[{$category}]</span></span>
                                                            <span>₹{$item->total_price}</span>
                                                        </div>";
                                                    }
                                                    $html .= "</div>";
                                                    $grandTotal += $order->total_amount;
                                                }
                                                $html .= "</div>";

                                                // --- GUEST ORDERS ---
                                                $guests = $record->sessions->where('host_session_id', $primarySession->id);
                                                
                                                if ($guests->isNotEmpty()) {
                                                    $html .= "<div style='font-size: 14px; font-weight: bold; text-align: center; border-top: 1px dashed #9ca3af; border-bottom: 1px dashed #9ca3af; padding: 5px 0; margin: 15px 0; color: #374151;'>--- JOINED GUESTS ---</div>";

                                                    foreach ($guests as $guest) {
                                                        $guestOrdersCount = $guest->orders->count();
                                                        $hasOrders = $hasOrders || $guestOrdersCount > 0;

                                                        $html .= "<div style='margin-bottom: 15px;'>";
                                                        $html .= "<div style='font-size: 14px; font-weight: bold; background-color: #fdfaf5; color: #111827; border: 1px solid #fef3c7; padding: 6px; border-radius: 5px;'>👤 GUEST: {$guest->customer_name} <span style='font-weight: normal; font-size: 12px; float: right; color: #4b5563;'>({$guestOrdersCount} Orders)</span></div>";
                                                        
                                                        foreach ($guest->orders as $order) {
                                                            $totalOrdersCount++;
                                                            $html .= "<div style='margin-top: 8px; padding-left: 10px; border-left: 2px solid #fde68a;'>";
                                                            $html .= "<div style='font-size: 11px; color: #6b7280; margin-bottom: 4px;'>Order #{$order->id}</div>";
                                                            
                                                            foreach ($order->items as $item) {
                                                                $name = $item->menuItem ? $item->menuItem->name : $item->item_name;
                                                                $category = $item->menuItem?->category ? strtoupper($item->menuItem->category->name) : 'GENERAL';
                                                                
                                                                $html .= "
                                                                <div style='display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 4px; color: #111827;'>
                                                                    <span><strong>{$item->quantity}x</strong> {$name} <br><span style='font-size: 10px; color: #6b7280;'>[{$category}]</span></span>
                                                                    <span>₹{$item->total_price}</span>
                                                                </div>";
                                                            }
                                                            $html .= "</div>";
                                                            $grandTotal += $order->total_amount;
                                                        }
                                                        $html .= "</div>";
                                                    }
                                                }
                                            }

                                            if (!$hasOrders) {
                                                return new HtmlString("<div style='text-align: center; padding: 20px; color: #6b7280;'>No valid orders found to bill.</div>");
                                            }
                                            
                                            // Final Summary Footer
                                            $html .= "
                                                <div style='border-top: 2px solid #000000; margin-top: 20px; padding-top: 10px;'>
                                                    <div style='display: flex; justify-content: space-between; font-size: 14px; color: #4b5563;'>
                                                        <span>Total Orders Delivered:</span>
                                                        <span>{$totalOrdersCount}</span>
                                                    </div>
                                                    <div style='display: flex; justify-content: space-between; font-size: 20px; font-weight: 900; color: #000000; margin-top: 10px;'>
                                                        <span>GRAND TOTAL</span>
                                                        <span>₹" . number_format($grandTotal, 2) . "</span>
                                                    </div>
                                                </div>
                                            </div>";

                                            return new HtmlString($html);
                                        })
                                ]),

                            // 💰 RIGHT COLUMN: PAYMENT GATEWAY & CLOSURE
                            Forms\Components\Section::make('Payment Collection')
                                ->columnSpan(5)
                                ->schema([
                                    Forms\Components\TextInput::make('subtotal')
                                        ->label('Remaining Bill Balance')
                                        ->numeric()
                                        ->prefix('₹')
                                        ->readOnly()
                                        // 🔥 FIX: Removed forced background colors so it adapts naturally to Filament's Dark Mode input fields
                                        ->extraInputAttributes(['style' => 'font-weight: bold; font-size: 1.2rem;']),

                                    Forms\Components\TextInput::make('tip')
                                        ->label('Add Tip Amount (Optional)')
                                        ->numeric()
                                        ->prefix('₹')
                                        ->default(0)
                                        ->live(onBlur: true),

                                    Forms\Components\Placeholder::make('grand_total')
                                        ->label('Total to Collect')
                                        ->content(function (Forms\Get $get) {
                                            $total = (float) $get('subtotal') + (float) $get('tip');
                                            // Keeping explicit dark green text and light green background because they look great on both themes
                                            return new HtmlString("
                                                <div style='font-size: 32px; font-weight: 900; color: #047857; background-color: #ecfdf5; padding: 15px; border-radius: 8px; border: 2px solid #10b981; text-align: center;'>
                                                    ₹" . number_format($total, 2) . "
                                                </div>
                                            ");
                                        }),

                                    Forms\Components\ToggleButtons::make('payment_method')
                                        ->label('Select Payment Method')
                                        ->options([
                                            'cash' => 'Cash',
                                            'upi' => 'UPI (QR)',
                                            'card' => 'Credit/Debit Card',
                                        ])
                                        ->icons([
                                            'cash' => 'heroicon-m-banknotes',
                                            'upi' => 'heroicon-m-qr-code',
                                            'card' => 'heroicon-m-credit-card',
                                        ])
                                        ->colors([
                                            'cash' => 'success',
                                            'upi' => 'info',
                                            'card' => 'warning',
                                        ])
                                        ->inline()
                                        ->required()
                                        ->default('cash'),

                                    Forms\Components\TextInput::make('transaction_reference')
                                        ->label('Transaction ID / UTR')
                                        ->placeholder('Required for Online Payments')
                                        ->required(fn (Forms\Get $get) => in_array($get('payment_method'), ['upi', 'card']))
                                        ->visible(fn (Forms\Get $get) => $get('payment_method') !== 'cash'),
                                        
                                ]),
                        ]),
                    ])
                    ->action(function (RestaurantTable $record, array $data) {
                        
                        $activeSessions = $record->sessions()->where('is_active', true)->get();
                        $sessionIds = $activeSessions->pluck('id')->toArray();

                        $validOrders = Order::whereIn('qr_session_id', $sessionIds)
                            ->where('status', '!=', 'cancelled')
                            ->get();
                        
                        $orderIds = $validOrders->pluck('id')->toArray();
                        $latestOrderId = collect($orderIds)->last();
                        $totalAmountToRecord = (float) $data['subtotal'] + (float) $data['tip'];

                        if ($latestOrderId && $totalAmountToRecord > 0) {
                            Payment::create([
                                'order_id' => $latestOrderId,
                                'amount' => $totalAmountToRecord,
                                'payment_method' => $data['payment_method'],
                                'status' => 'paid',
                                'transaction_reference' => $data['transaction_reference'] ?? null,
                                'paid_at' => now(),
                            ]);
                        }

                        if (!empty($orderIds)) {
                            Order::whereIn('id', $orderIds)->update(['status' => 'completed']);
                            
                            foreach ($orderIds as $oId) {
                                OrderStatusLog::create([
                                    'order_id' => $oId,
                                    'from_status' => 'served', 
                                    'to_status' => 'completed',
                                    'changed_by' => auth()->id(),
                                ]);
                            }
                        }

                        if (!empty($sessionIds)) {
                            QrSession::whereIn('id', $sessionIds)->update([
                                'is_active' => false,
                                //'join_status' => 'completed' 
                            ]);
                        }
                    }),
            ])
            //->actionsAlignment(Tables\Enums\ActionsAlignment::Center)
            ->recordAction(null) 
            ->recordUrl(null);
    }

    public static function form(Form $form): Form { return $form->schema([]); }
    public static function getPages(): array { return ['index' => Pages\ListTableBillings::route('/')]; }
}