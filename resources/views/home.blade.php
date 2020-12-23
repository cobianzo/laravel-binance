@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row no-gutters justify-content-center">

        <div class='template-container' data-templatename='binance-balance'>
            <x-binance-balance></x-binance-balance>
        </div>
        

        <div class='template-container col-md-5' 
             data-templatename='binance-trades'>
            
             <x-binance-trades symbol="" current_price=""/>
             
        </div>

        <div class="col-md-7">

            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <!-- 
                        {{ __('You are logged in!') }}


                        <form method="post" action="/test">
                            @csrf
                            <button id='ajax-btn' class='btn btn-primary'>
                                Ajax btn
                            </button>
                        </form>
                    -->



                    <!-- VUEJS component for setting the price!  -->
                        <price-and-amount symbol=""></price-and-amount>
                    <!-- **************************************  -->



                </div>
            </div>
        </div>

        {{-- This should go in a different page. It might 
        take a long time of processing --}}
        
        <div class="card-body text-info bg-info p-1" id="all trades">
            {{-- {{ App\View\Components\BinanceTrades::getAllTrades() }} --}}
            
            
            {{-- THIS WORKS but obvoipusly is very consuming
                @foreach (App\View\Components\BinanceTrades::getAllTrades() as $tradeId => $trade )
                <x-single-trade :trade="$trade" :currentPrice='false' :tradeId="$tradeId" :showSymbol="true"/>
            @endforeach --}}
            
            
            {{-- @inject('BT', 'App\View\Components\BinanceTrades')
            @foreach ( $BT::getAllTrades() as $tradeId => $trade)
            <x-single-trade :trade="$trade" :currentPrice='false' :tradeId="$id" />
            @endforeach
            Monthly Revenue: {{ $cc::getCosa() }}. --}}
            
        </div>

    </div>
</div>
@endsection
