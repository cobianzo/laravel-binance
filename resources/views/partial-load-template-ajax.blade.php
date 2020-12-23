{{-- No need to initialize here the param symbol because it's sent directly 
within $_REQUEST, and we have taken it from app.js 
    We also receive $_REQUEST['template'] ie 'binance-trades'
--}}

{{-- <pre>
    {{ print_r($_REQUEST) }}
</pre> --}}

@if ($_REQUEST['template'] === 'binance-trades')
    <x-binance-trades></x-binance-trades>
@endif
@if ($_REQUEST['template'] === 'binance-balance')
    <x-binance-balance ></x-binance-balance>
@endif


