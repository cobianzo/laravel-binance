<div class="card border-info">

    <div class="card-header">
        <button onClick="const symbol = document.querySelector('#symbol-data').textContent;
                        const currentPrice = document.querySelector('#current-price').textContent;
                        window.UIMethods.reloadTemplate('binance-trades', { symbol:symbol, currentPrice:currentPrice } );" 
                class='position-absolute' style="margin-left:-100px">
                        Reload
        </button>

        Trades : 

        {{ $symbol }} Current price: <b>{{ $currentPrice?? 'no current price' }}</b>
    </div>
    
    <div class="card-body text-info bg-info p-1">
        @foreach ($trades as $id => $trade) 

            <x-single-trade :trade="$trade" :currentPrice='$currentPrice' :tradeId="$id" />

        @endforeach
    </div>
</div>