<div class="row no-gutters line-height-1" 
        data-orderid="{{$order['orderId']}}">
    
    <!-- show profit/loss -->
    @if ($ratio_benefit) 
        <div class='col-2 small text-center text-{{ ($ratio_benefit>0? 'success' : 'danger') }}'>
            {{ $ratio_benefit }}
        </div>
    @endif


    <!-- SELL/BUY LIMIT/MARKET column -->
    <span class='col col-3 alert alert-{{ ($order['side']==='BUY'? 'info' : 
                                                ($ratio_benefit > 0? 'success' : 'danger')) }}
                 p-0 text-center small overflow-hidden'>
        
            <b class='w-100'>{{ $order['side'] }}</b>
            <b>{{ $order['type'] }}</b>
        
    </span>

    <!-- Amount USDT column -->
    <div class='col col-3 text-center align-items-center'>
        <span class='d-block'>$<b>{{ round($order['cummulativeQuoteQty'],2) }}</b></span>
        <span class="quantity small">({{ 
             App\Http\Controllers\MyBinanceController::parseQuantity(
                                                        $order['symbol'], 
                                                        $order['origQty'])
        }}<i>💮</i>)</span>
    </div>

    
    <!-- price USDT/coin column -->
    <span class='order-price col col-2 text-center align-items-center'>
        <b class='small'>{{
            App\Http\Controllers\MyBinanceController::parsePrice(
                                                            $order['symbol'],
                                                            $order['price'])
        }}</b>            
    </span>
    
    <!-- time -->
    <span class='order-time col col-2 text-center align-items-center'>
        <i class='small d-block'>{{
            date('H:i', $order['updateTime']/1000)
        }}</i>
        <b class='xs'>{{
            date('d M', $order['updateTime']/1000)
        }}</b>
    </span>


    {{-- Cancel --}}
    @if ($order['status'] === 'NEW')
        <button class='col col-1 btn btn-danger btn-sm text-center align-items-center' 
            onclick="window.binanceMethods.cancelOrder('{{$order['symbol']}}', '{{$order['orderId']}}');">
            <span>🅧</span>
        </button>
    @else
        <span class='col col-2 {{ ($order['status'] === 'FILLED')? ' alert alert-success' : '' }}
                text-center px-0 pb-0 pt-2 small'>
            {{ $order['status'] }}
        </span>
    @endif
</div>