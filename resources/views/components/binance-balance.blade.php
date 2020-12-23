
    <div class="row alert alert-primary m-0">
        <div class="col-2">
            
            <div class="text-center" id="usdt-balance-total"><span class="value h1">{{ 
                $balance_total
            }}</span> {{ $base_coin }}</div>
            
            @if ($balance_base_on_order) 
                <div class="text-center text-danger" id="usdt-balance-in-order"><span class="value h4">{{ 
                    $parseInt($balance_base_on_order)
                }}</span> {{ $base_coin }}</div>
            @endif

            <button onClick="
                        const activeCoin = document.querySelector('.coin-selector.active .coin-value').textContent;
                        window.UIMethods.reloadTemplate('binance-balance', {activeCoin: activeCoin}); return false;"
                    class="position-absolute"
                    style="margin-top:-20px">
                <i>↺</i>
            </button>

        </div>
        <div class="col-10">
            @foreach ($coins_and_balances as $coin => $balance)                
                <a  class="badge badge-{{ ($balance['valueUSDT'] > 10)? 'info' : 'primary' }} 
                            coin-selector coin-{{ strtolower($coin . $base_coin) }}
                            {{ $activeCoin === $coin? 'active' : '' }}" 
                    href="#"
                    onClick="window.binanceMethods.selectSymbol('{{ $coin . $base_coin }}'); return false;">
                    <span class='coin-value'>{{ $coin }}</span>
                    <small>{{ $balance['totalCoins'] }}</small><br>
                    <small>{{ $parseInt($balance['valueUSDT']) }} {{$base_coin}}</small>
                </a>
            @endforeach
        </div>
    </div>
