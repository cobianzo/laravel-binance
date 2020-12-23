<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Caca extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public $cosa;
    public function __construct()
    {
        $this->cosa = "mi mierda es mia";
    }

    function getCosa(){
        return $this->cosa;
    }
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('components.caca');
    }
}
