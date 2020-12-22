<div class="trade-card gradient-border {{ $trade['is_open']? 'is-open' : 'is-closed' }}
    card card-{{ ( $is_winning? 'success is-winning ' : ( $is_losing? 'danger is-losing ' : 'secondary is-neutral ' ) ) }}"
    data-lastupdate='{{ $trade['last_update_trade'] }}' 
    data-orderids='{{ implode('|',$trade['all_orderIds']) }}' >

    <div class='card-header row no-gutters p-1'>
        <p class='col-3 mb-0 pb-0 small text-mute'>#{{$tradeId}}</p>

        <div class='col-2 badge'>
                $<span>{{ round($trade['benefitUSDT'],2) }}</span>
        </div>

        <div class='col-2 badge badge-{{ $trade['benefitUSDT'] > 0? 'success' : 'danger' }}'>
            {{ $trade['benefitUSDT'] > 0? '+' : '' }}
            {{ round(100*$trade['benefitUSDT']/$trade['amountUSDT'],2) }}
            %
            </div>

        @if ($trade['is_open'])
            <a class='col-3' href="#" 
                onClick="exitOpenTradeAtCurrentPrice('{{ $tradeId }}', '{{ $trade['symbol']}}') return false;">
                sell trade
            </a>
        @elseif ($trade['non_sold_coins']) 
            <div class='col-3'>{{$trade['non_sold_coins']}}</div>
        @endif


    </div>
    




    <div class='card-body mb-2 p-1'>

        <x-order-row :order="$trade['entry_order']" />

        @foreach($trade['open_orders'] as $o)
            <x-order-row :order="$o" />
        @endforeach

        @foreach($trade['exit_orders'] as $o)
            <x-order-row :order="$o" />
        @endforeach

        {{-- We use the current price passed initially from js. 
            Otherwise, calling the API takes too long --}}
        @if ($trade['uncovered_bottom'])
            @if ($current_price) 
                <p class='text-danger small'>Uncovered coins stop loss:  
                    {{$trade['uncovered_bottom']}}  
                    ({{ App\Http\Controllers\MyBinanceController::valueInUSDT(
                                                                $trade['symbol'],
                                                                $trade['uncovered_bottom'],
                                                                $current_price?? false) }}
                        USDT)
                </p>
            @endif
        @endif

    </div>
</div>