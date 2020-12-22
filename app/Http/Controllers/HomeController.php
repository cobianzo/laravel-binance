<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * cpould be called Binance controller.
 */
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the binance operation Dashboard for current user.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }
}
