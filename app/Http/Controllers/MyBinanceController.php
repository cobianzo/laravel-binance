<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Http\Controllers;

class MyBinanceController extends Controller
{
    // TODO: add middelware to be logged in to use all these functions.

    private $api                = null;
    public  $ticker             = null;
    public  $exchangeinfo       = []; // info about filters of symbols.
    private $balances           = null;
    private $bookprices         = null;


    const BASE_COIN             = 'USDT';


    // VIEWS
    public function load_partial() {
        if (empty($_REQUEST['template'])) {
            return "error. template name required";
        }
        return view('partial-'.$_REQUEST['template'], $_REQUEST); // in _REQUEST we find other params that the partial can grab
    }

    // create a route /test for this for testing.
    public function test(Request $request) {
        return response()->json(\Auth::user());
    }

    /**
     * Get Binance API using key and secret associated to the current user.
     */
    public static function getApi() {
        try {
            require  base_path() . '/vendor/autoload.php';
            $user   = \Auth::user();
            if (empty($user->b_key) || empty($user->b_private)) return false;
            $api    = new \Binance\API($user->b_key,UserController::dc($user->b_private));
        } catch (\Exception $e) {
            // report($e);
            return false;
        }
        return $api;
    }
    public static function get_base() {
        return self::BASE_COIN;
    }


    /**
     * given 'TFUEL' return 'TFUELUSDT'
     */
    public static function getSymbol($coin = 'BTC', $base_coin = null) {
        $base_coin = $base_coin?? self::BASE_COIN;
        if (strpos($coin, $base_coin) > 0) 
            return $coin;
        return $coin . $base_coin;
    }
    /**
     * given 'TFUELUSDT' return 'TFUEL'
     */
    public static function getCoin($symbol = 'BTCUSDT', $base_coin = null) {
        $base_coin = $base_coin?? self::BASE_COIN;
        if (strpos($symbol, $base_coin) === FALSE) 
            return $symbol;
        return str_replace($base_coin, '', $symbol);
    }


    // Not usre if I m using it...
    // Returns number of coins
    public function getBalances( $coin = 'BTC' , $opt = ['forceUpdate' => false]) {
        
        if (empty($this->api))
            $this->api  = self::getApi();

        // $coin = $this->getSymbolFromCoin($coin, 'PARSED')['coin'];
        if (empty($this->balances) || $opt['forceUpdate']) {
            $ticker = $this->api->prices();
            $balances = $this->api->balances($ticker); // ensure to get last values.
            $this->balances = $balances;
        }
        if (!empty($coin)) {
            return $this->balances[$coin]; // ['available', 'onOrder']
        }
        return $this->balances;
    }


    // helpers
    public static function getTotal($available=0,$in_order=0, $decimals=0) {
        return round($available + $in_order, $decimals);
    }

    
    // helper . gets filter information to know the decimals on price and quantity
    static public function getExchangeInfo($coin) {
        if (empty($coin)) return null;
        $symbol = self::getSymbol($coin); //converts to ETHUSDT from ETH
        // TODO, check if file exists.
        $path = public_path() . "/exchangeinfo/$symbol.json";
        $exchangeinfo = json_decode(file_get_contents( $path ));
        return $exchangeinfo;
    }

    // helper - change the decimals of the price to make it fit with the rules of the exchange
    static public function parsePrice($symbol, $price) {
        $exchange_info = self::getExchangeInfo($symbol);
        foreach($exchange_info->filters as $filter) {
            if ($filter->filterType === 'PRICE_FILTER') {
                $tick_size = $filter->tickSize; // ie.  0.00001 
                $price = intval(floatval($price)/$tick_size)*$tick_size;
                return $price;
            }
        }
    }
    // helper - change the decimals of the quantity to make it fit with the rules of the exchange
    // ie. for COTI, converts the quantity 3000.4356 in 3000.4, because the lot step is 0.1
    static public function parseQuantity($symbol, $quantity) {
        
        $exchange_info = self::getExchangeInfo($symbol);

        if (!$exchange_info) return $quantity;
        foreach($exchange_info->filters as $filter) {
            if ($filter->filterType === 'LOT_SIZE') {
                $step_size = $filter->stepSize; // ie.  0.1 
                $quantity = intval(floatval($quantity)/$step_size)*$step_size;
                return $quantity;
            }
        }
        return "some error parsing quantty";
    }

    // helper. A certain amount of a coin, how much does it value in USDT?
    public static function valueInUSDT($coin, $quantity, $price = null, $ops = ['decimals' => 0]) {
        
        $symbol = self::getSymbol($coin);
        $api    = self::getApi();
        if (!$price)
            $price  = $api->price($symbol);

        
        return round($quantity * $price, $ops['decimals']);
    }







    

    /**
     * API action! BUY LIMIT!
     * @param float $params['price']
     * @param int $params['amountUSDT']
     * @param string $params['symbol']
     * 
     * @return Array status, msg 
     */
    public function place_buy(Request $request) {
        
        
        $price      = floatval($request['price']); // price per coin, in USDT.
        $amountUSDT = intval($request['amountUSDT']); // money to spend in the purchase (USDT)
        $symbol     = $request['symbol']; // coin, ie TFUELUSDT
        $quantity   = self::parseQuantity($symbol, $amountUSDT / $price); // amount of coins to buy. needs to refine the number of decimals. SUSHI, for example, doest accept more decimals
        $type       = isset($request['type'])? $request['type'] : 'MARKET';
        // init return for json
        $return_data = [ "status" => "success", "msg" => "", "order" => null];
        
        
        
        // create market BUY ORDER
        
        $coinspair   = self::getSymbol($symbol); // just in case
        
        $return_data['msg'] .= "<p class='text-warning'> " .
                                 " '$type' BUY $coinspair <br/> " .
                                 " <b class='small'>$quantity</b><i class='small'>$symbol</i><br/>" .
                                 " (<i class='small'>$amountUSDT</i><span class='small'>".$this::BASE_COIN."</small>) " .
                               " </p>";
        
        try {
            if (empty($this->api))
                $this->api  = self::getApi();

            if (0 || (defined('TEST') && TEST)) {
                $return_data['data'] = [$coinspair, $quantity];
                $return_data['order'] = $this->api->marketBuyTest($coinspair, $quantity);
                $return_data['msg'] .= '<br/> TESTING marketBuyTest';
            }
            else {
                $return_data['data'] = [$coinspair, $quantity, $price, $type];
                
                // BUY ORDER VIA API
                $return_data['order'] = $this->api->buy($coinspair, 
                                                        self::parseQuantity($symbol,$quantity),
                                                        self::parsePrice($symbol,$price),
                                                        $type, []);
                // $return_data['order'] = $this->api->marketBuy($coinspair, $quantity);
            }
            if (!is_array($return_data['order'])) {
                $return_data['status'] = 'error';
            }
        } catch (Exception $e) {
            $return_data['status'] = 'error';
            $return_data['msg'] .= "<p class='text-danger'> Error ".$e->getMessage()."</p>";
        }

        // from here VueJS takes the reponse and shows a message of success.
        return response()->json($return_data);
    }


    public function place_oco(Request $request) {

        $return_data = [ "status" => "success", "msg" => "", "order" => null];

        // inits. this fn is made to use ajax, but it should work using params instead.
        if (empty($this->api))
                $this->api  = self::getApi();

        $symbol     = $request['symbol']; // coin.

        // calculations.
        $balanceCoin = $this->getBalances($symbol);
        $balanceCoin = floatval($balanceCoin['available'] + $balanceCoin['onOrder']);

        $price      = floatval($request['price']); // price per coin, in USDT.
        $amountUSDT = intval($request['amountUSDT']); // money to spend in the purchase (USDT)
        
        $quantity   = $this->parseQuantity( $symbol, ($amountUSDT / $price) );      // amount of coins to buy

        // we can't sell more coins of those that we have!
        if ($quantity > $balanceCoin) {
            $diff = 1 - $balanceCoin/$quantity;
            $return_data['msg'][] = "Adjusting quantity $quantity ".$request['symbol']." to full balance of $balanceCoin";
            if (abs($diff) > 0.05) {
                return( [   'status' => 'error', 
                            'msg' => "Too much difference between current quantity in account and selling coins",
                            'order' => [ 'balanceCoin' => $balanceCoin, 'quantity' => $quantity ]] );
            } 
            $quantity = $this->parseQuantity( $symbol, $balanceCoin );
        }

        $T1         = floatval($request['price_t1']); // Sell with benefit at this price (LIMIT).
        $stop_loss  = floatval($request['price_stop_loss']); // Sell with loss if under this price.

        $price      = $this->parsePrice($symbol, $price);
        $T1         = $this->parsePrice($symbol, $T1);
        $stop_loss  = $this->parsePrice($symbol, $stop_loss);
        

        /*
        An OCO Trade has 2 orders:
            STOP_LOSS or STOP_LOSS_LIMIT leg (we prefer STOP LOSS -market- to ensure filling)
            LIMIT_MAKER leg
        Restriction from Binance: 
            SELL Orders : Limit Price > Last Price > Stop Price
        */
        $test = false;
        $flags = ['stopLimitPrice' => $stop_loss]; // if used, it places a STOP_LOSS_LIMIT. I want a STOP_LOSS (market)
        
        // TESTING
        // $symbol  = 'CHZUSDT';
        // $quantity   = 17419;
        // $T1         = 0.0147;
        // $stop_loss  = 0.0138;
        // $flags      = ['stopLimitPrice' => $stop_loss];
        $return_data['msg'][]     = "Info sent to binance: $symbol,$quantity,$T1,$stop_loss,".print_r($flags,1);
        
        try {

            $return_data['order'] = $wp_binance->api->OCOorder('SELL',
                                                        $symbol, // TFUELUSDT
                                                        $quantity,  // 322
                                                        $T1, // 0.01691 (profit, 5 decimals max)
                                                        $stop_loss, // 0.01600 (marker stop loss)
                                                        $flags,
                                                        $test ); 
        } catch (Exception $e) {
            $return_data['status'] = 'error';
            $return_data['msg'][] = "exception thrown. Sent data: symbol $symbol,quantity $quantity,T1 $T1,stop_loss $stop_loss ";
            $return_data['msg'][] = $e->getMessage();
            return response()->json($return_data);
        }
        
        if (!is_array($oco_order) || empty($oco_order)) {
            $return_data['status'] = 'error';
            $return_data['msg'][] = "error placing the oco order";
            return response()->json($return_data);
        }

        // all OK
        return response()->json($return_data);

    }
    // end place oco


    // POST ajax fn
    public function cancel_open_order(Request $request) {

        $symbol     = $request['symbol'];
        $orderId    = $request['orderId'];

        $return     = [ 'status' => 'error', 'data' => []];
        // API call
        if (!$this->api)
            $this->api   = MyBinanceController::getApi();

        // CANCEL THE ORDER and return the result to js success fn.
        try {
            $response = $this->api->cancel($symbol, $orderId);
        } catch (Exception $e ){
            $return['msg'] = $e->getMessage();
            return response()->json($return);
        }

        $return['status']   = 'success';
        $return['data']     = $response;
        $return['msg']      = "Order cancelled succesfully";
        return response()->json($return);
    }
    // end cancel


}
