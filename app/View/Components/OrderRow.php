<?php

namespace App\View\Components;

use Illuminate\View\Component;

class OrderRow extends Component
{
    public $order;
    public $ratio_benefit = 0;

    public $ref_price = 0; // price of the trade,to take as reference to compare with order price (benefit/loss)
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($order, $ref_price = 0)
    {
        $this->order = $order;
        $this->ratio_benefit = $this->getRatioBenefit();
    }

    public function getRatioBenefit() {
        if ($this->ref_price)
            return round( (100 * $this->order['price'] / $this->$ref_price) - 1, 2);
        return 0;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('components.order-row');
    }
}
