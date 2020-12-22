<?php

namespace App\View\Components;

use Illuminate\View\Component;
use \App\Http\Controllers as C;

/**
 * Left column: shows current balance, and a list of all coins with some balance.
 */
class BinanceBalance extends Component
{

    public $balances = null;                // API response for ticker balances for all coins.
    public $balance_base_available = null;  // USDT available.
    public $balance_base_on_order  = null;  // USDT invested in orders.
    public $balance_total  = null;          // USDT in total
    public $exchangeBTCUSDT = null;
    public $coins_and_balances = [];
    public $more_coins = [ 'BTC', 'COTI', 'EUR', 'PERL', 'AXS', 'TFUEL', 'EGLD', 'INJ', 'ALPHA', 'CHZ', 'LTC',
    'KSM', 'OCEAN', 'DOGE', 'WING' ];       // Currencies that we want to display.

    const MIN_USDT_VALUE = 0.1;             // min value to show the coin.

    private $api;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        
        
        
        // API call
        if (!$this->api)
            $this->api          = C\MyBinanceController::getApi();
        if (!$this->api) {
            return false;
        }
        $ticker             = $this->api->prices();
        
        $this->balances = $this->api->balances($ticker); // the API list of prices in BTC value for all coins.
        
        // needed vars
        $base_coin              = C\MyBinanceController::get_base();
        $this->exchangeBTCUSDT  = $ticker[ C\MyBinanceController::getSymbol('BTC') ];


        // init propierties
        $this->balance_base_available   = $this->balances[$base_coin]['available'];
        $this->balance_base_on_order    = $this->balances[$base_coin]['onOrder'];
        $this->balance_total            = $this->getBalanceTotal();
        

        // set up $this->coins_and_balances with values to display in view.
        foreach ($this->balances as $coin => $balance) {            
            $valueUSDT = $balance['btcValue'] * $this->exchangeBTCUSDT;
            if ( $valueUSDT >= self::MIN_USDT_VALUE || 
                    in_array($coin, $this->more_coins) ) 
                {
                    // adding this coin to the list of shown coins.
                    $this->coins_and_balances[$coin] = $balance;
                    $this->coins_and_balances[$coin]['valueUSDT'] = $valueUSDT;
                    $this->coins_and_balances[$coin]['totalCoins'] = self::getTotal($balance['available'],$balance['onOrder']);
                }
        }

    }

    // get total of a coin, passing parameters.
    public static function getTotal($available, $in_order, $decimals = 0) {
        return C\MyBinanceController::getTotal($available,$in_order, $decimals);
    }

    // Total of USDT,available + onOrder
    public function getBalanceTotal(){
        return self::getTotal($this->balance_base_available, $this->balance_base_on_order);
    }
    public static function parseInt($int) {
        return intval($int);
    }
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('components.binance-balance', ([
            'base_coin'             => C\MyBinanceController::get_base(),
        ]));
    }
}
