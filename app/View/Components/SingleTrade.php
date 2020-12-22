<?php

namespace App\View\Components;

use Illuminate\View\Component;

class SingleTrade extends Component
{
    // params
    public $trade;
    public $tradeId;
    public $current_price;


    public $is_winning = false;
    public $is_losing = false;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(Array $trade, float $currentPrice = 0, $tradeId = 0)
    {
        $this->trade            = $trade;
        $this->tradeId          = $tradeId;
        $this->current_price    = $currentPrice;

        $this->is_winning = ($trade['benefitUSDT'] > 3);
        $this->is_losing  = ($trade['benefitUSDT'] < -3);
    }



    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('components.single-trade');
    }
}
