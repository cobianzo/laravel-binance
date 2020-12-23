<?php
// TODELETE
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CacaController extends Controller
{
       /**
     * Create a new controller instance.
     *
     * @return void
     */
    public $cosa; 
    const COSITA = "MIERDA";
    public function __construct()
    {
        // $this->middleware('auth');
        $cosa = 'mierda';
    }

    public static function getCosa(){
        return "la cosa es una " . self::COSITA;
    }

}
