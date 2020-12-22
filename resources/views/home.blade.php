@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row no-gutters justify-content-center">

        <div class='template-container' data-templatename='binance-balance'>
            <x-binance-balance></x-binance-balance>
        </div>
        

        <div class='template-container col-md-5' 
             data-templatename='binance-trades'>
            
             <x-binance-trades symbol="ETHUSDT" current_price=""/>
             
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


    </div>
</div>
@endsection
