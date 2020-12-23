<?php
// TODO: I think this should be a Controller. With its view.
// The call of this view and the ajax call will be done through the a controller fn, using params sent through route.
namespace App\View\Components;

use Illuminate\View\Component;
use App\Http\Controllers\MyBinanceController;
use App\Http\Controllers\UserController;

class BinanceTrades extends Component
{

    // passed as parametes in component
    public $symbol;
    public $currentPrice; // the price at the time of this component being rendered.


    
    public $trades    = [];

    public $all_orders    = [];
    public $open_orders   = [];
    public $buy_orders    = [];
    public $sell_orders   = [];

    public  $history;
    private $api;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($symbol = null, $currentPrice = 0)
    {
        // INIT PROPERTIES. If this component is loaded 
        // from js -> partial-load-template-ajax.blade.php, and here
        // we take the value from REQUEST
        $this->symbol       = $symbol??         (!empty($_REQUEST['symbol'])? $_REQUEST['symbol'] : null);
        $this->currentPrice = $currentPrice??   (!empty($_REQUEST['currentPrice'])? $_REQUEST['currentPrice'] : 0);
        
        $orders_set = $this->getAllOrdersForSymbol($this->symbol);
        if (!empty($orders_set))
            $this->trades = $this->groupOrdersInTrade($orders_set['buy_orders'], $orders_set['open_orders'], $orders_set['sell_orders']);
        
    }

    public static function getAllOrdersForSymbol(string $symbol = '') {

        if (empty($symbol)) return [];
        // API call
        
        $api   = MyBinanceController::getApi();
        
        // orders(string $symbol, int $limit = 500, int $fromOrderId = -1, array $params = []) {
        $all_orders_unsorted = $api->orders($symbol, 500, 0, []);
        if (!empty($all_orders_unsorted['code'])) // when returns an error.
            return false;
        $all_orders = [];
        $buy_orders = [];
        $sell_orders = [];
        $open_orders = [];
        if (is_array($all_orders_unsorted) && count($all_orders_unsorted))
        for ( $i = (count($all_orders_unsorted) - 1 ); $i >= 0; $i--) {
            $order = $all_orders_unsorted[$i];
            if ( !in_array($order['status'], ['CANCELED', 'EXPIRED']) ) {
                $all_orders[] = $order;
                if ($order['side'] === 'BUY') {
                    $buy_orders[] = $order; 
                } else { // SELL
                    if ($order['status'] === 'FILLED') {
                        $sell_orders[ ] = $order;
                    } else {
                        $open_orders[] = $order;
                    } 
                }
            }
        }
        return ['buy_orders' => $buy_orders,
                'open_orders' => $open_orders,
                'sell_orders' => $sell_orders,
                'all_orders' => $all_orders, ];
    }


    /**
     * trades = [ 6757653 => [ 'entry_order' => [], 
     *                               ...
     *                       ],
     *            7645775 => [
     *                       ],
     *          ]
     */
    public static function groupOrdersInTrade($buy_orders, $open_orders, $sell_orders) {

        
        $trades = [];
        foreach ($buy_orders as $buy_order) {

            /**
             * extended option: retrieve info saved in WP about the last update of this trade.
             * - retrieve last update timestamp, and all inner ordersid
             */


            
            $price      = floatval($buy_order['price']);

            $quantity   = floatval($buy_order['origQty']);
            $amountUSDT = round(floatval($buy_order['cummulativeQuoteQty']),2);            
            $price      = MyBinanceController::parsePrice($buy_order['symbol'], $amountUSDT / $quantity);
            
            // init info about the trade
            $current_trade = [ 
                'entry_order' => $buy_order,            // *** GROUP BUY ORDER INSIDE THE TRADE.        
                'open_orders' => [],                    // if the trade is happening, normally the OCO order with take profit and stop loss.
                'exit_orders' => [],                    // the sell filled order. Normally only one but in PF there were more.
                'all_orderIds'=> [$buy_order['orderId']],          // redundant: the ids of the entry order, all open orders and all exit orders.
                'symbol'      => $buy_order['symbol'],  // ie TFUELUSDT
                'amountUSDT'  => $amountUSDT,           // USDT payed for the buy order.
                'benefitUSDT' => -1 * $buy_order['cummulativeQuoteQty'], // calculated as we check the sell orders. If open, calculated if we sold the coins at current price.
                'non_sold_coins' => 0,                  // we calculate this value in the end.
                'entry_price' => $price,                // the price at which the coin was bought by the buy order.
                'is_open' => false,                     //
                'uncovered_top' => $quantity,           // calclate how much of the quantity of coins of the trade is corresponded with orders.
                'uncovered_bottom' => $quantity,
                'entry_time'    => $buy_order['updateTime'], // in milisecs timestamp
                'human_entry_time' => date('H:i dMY', $buy_order['updateTime']/1000),
                'last_update_trade' => $buy_order['updateTime'], // last update of the newest order of the trade.
            ];

            // $trades[] = $current_trade;
            // continue;
            // check the open orders associated to that buy order
            if (!empty($open_orders))
                foreach($open_orders as $oo => $open_order) {
                    if ( $open_order['side'] === 'SELL' &&
                        ($open_order['updateTime'] > $current_trade['entry_time'])) {
                        
                        $open_order_quantity = $open_order['origQty']; // number of coins
                        
                        if ($open_order['price'] > $current_trade['entry_price']) {
                        // check take profit order
                            $open_order['profit-or-loss'] = 'take-profit';
                            if ($open_order_quantity > $current_trade['uncovered_top']) 
                                break; 
                            $current_trade['uncovered_top'] -= $open_order_quantity;
                        } else {
                        // check stop loss
                            $open_order['profit-or-loss'] = 'stop-loss';
                            if ($open_order_quantity > $current_trade['uncovered_bottom']) 
                                break; 
                            $current_trade['uncovered_bottom'] -= $open_order_quantity;
                        }

                        // *** GROUP    O P E N    O R D E R    INSIDE THE TRADE.*
                        $current_trade['open_orders'][]     = $open_order;
                        $current_trade['all_orderIds'][]    = $open_order['orderId'];
                        $current_trade['last_update_trade'] = max($open_order['updateTime'], $current_trade['last_update_trade']);
                        unset($open_orders[$oo]); // remove it from the list so it can't be attached to other trade.

                        // *******************************************************

                        if ( $buy_order['status'] === 'FILLED')
                            $current_trade['is_open']       = true;

                        // // if what's left to cover is less than 5 USDT, it's insignificant, we can consider the trade covered.
                        if ( MyBinanceController::valueInUSDT($current_trade['symbol'], $current_trade['uncovered_bottom'], $current_trade['entry_price']) < 5 )
                            $current_trade['uncovered_bottom'] = 0;
                        if ( MyBinanceController::valueInUSDT($current_trade['symbol'], $current_trade['uncovered_top'], $current_trade['entry_price']) < 5 )
                            $current_trade['uncovered_top'] = 0;

                    }
                } // end chek open orders.
                // TODO: still can be checked if this trade is open if the user has balance of the coin

                if (!empty($sell_orders) && 
                            $current_trade['uncovered_top'] && 
                            $current_trade['uncovered_bottom']) {
                    foreach( $sell_orders as $so => $sell_order ) :
                        if (($sell_order['updateTime'] > $current_trade['entry_time']) &&
                           ($sell_order['origQty'] <= max(  $current_trade['uncovered_top'], 
                                                            $current_trade['uncovered_bottom'])))
                        {

                                // *** GROUP    E X I T ( SELL )  O R D E R    INSIDE THE TRADE.*//
                                $current_trade['exit_orders'][]     = $sell_order;
                                $current_trade['all_orderIds'][]    = $sell_order['orderId'];
                                unset($sell_orders[$so]);
                                $current_trade['uncovered_top']     -= $sell_order['origQty'];
                                $current_trade['uncovered_bottom']  -= $sell_order['origQty'];
                                $current_trade['benefitUSDT']       += $sell_order['cummulativeQuoteQty'];
                                $current_trade['last_update_trade'] = max($sell_order['updateTime'], $current_trade['last_update_trade']);
                                // *********************// *********************// **************//

                                // // if what's left to cover is less than 5 USDT, it's insignificant, we can consider the trade covered.
                                if ( MyBinanceController::valueInUSDT($current_trade['symbol'], $current_trade['uncovered_bottom'], $current_trade['entry_price']) < 5 )
                                    $current_trade['uncovered_bottom'] = 0;
                                if ( MyBinanceController::valueInUSDT($current_trade['symbol'], $current_trade['uncovered_top'], $current_trade['entry_price']) < 5 )
                                    $current_trade['uncovered_top'] = 0;
                        }
                    endforeach;
                }
            // end sell orders

            // TODO: I should check if the user has coins. If he has more coins that this trade then it is open.
            if ($buy_order['status'] === 'FILLED' && empty($current_trade['exit_orders']))
                $current_trade['is_open'] = true;

            // last calculation, if trade open, benefitUSDT is the value of the non sold coins of the trade.
            if ($current_trade['is_open']) {
                $current_trade['non_sold_coins'] = $quantity - array_sum(array_map(function($sell_order){ return floatval($sell_order['origQty']); }, $current_trade['exit_orders']));
                $current_trade['benefitUSDT'] += MyBinanceController::valueInUSDT($current_trade['symbol'], $current_trade['non_sold_coins']);
            }

            // saving, creation of the trade in the list of trades.
            $trades[$buy_order['orderId']] = $current_trade;
        }

        return $trades;
    }

    public static function justArray() {
        return [ '100', '200'];
    }

    public static function getAllTrades() {
        $fav_coins = UserController::get_favourite_coins('array');
        
        $all_all_trades = [];
        foreach ($fav_coins as $coin) 
            if ($coin !== 'USDT') {            
                $all_orders = self::getAllOrdersForSymbol(MyBinanceController::getSymbol($coin));
                if (!empty($all_orders)) {
                    $all_trades = self::groupOrdersInTrade($all_orders['buy_orders'], $all_orders['open_orders'], $all_orders['sell_orders']);
                    $all_all_trades = array_merge($all_all_trades, $all_trades);
                }
        }
        usort($all_all_trades, function($tr1, $tr2) {
            if ($tr1['last_update_trade'] == $tr2['last_update_trade']) return 0;
            return ($tr1['last_update_trade'] < $tr2['last_update_trade']) ? -1 : 1;
        });
        return $all_all_trades;
    }


    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('components.binance-trades', []);
    }
}
