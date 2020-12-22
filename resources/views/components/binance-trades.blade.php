<div class="card border-info">

    <div class="card-header">
        <button onClick="const symbol = document.querySelector('#symbol-data').textContent;
                        const current_price = document.querySelector('#current-price').textContent;
                        window.UIMethods.reloadTemplate('binance-trades', { symbol:symbol, current_price:current_price } );" 
                class='position-absolute' style="margin-left:-100px">
                        Reload
        </button>

        Trades : 

        {{ $symbol }} Current price: <b>{{ $current_price?? 'no current price' }}</b>
    </div>
    
    <div class="card-body text-info bg-info p-1">
        @foreach ($trades as $id => $trade) 

            <x-single-trade :trade="$trade" :currentPrice='$current_price' :tradeId="$id" />

        @endforeach
    </div>
</div>