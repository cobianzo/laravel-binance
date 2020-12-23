<template>
    <div id="price-and-amount" class='row'>
        <!-- column left: price and amount -->
        <div class='col-6 mb-2'>
            <div class="card card-vuejs">

                <div class="card-header">
                    <div class='row no-gutters'>
                        <a class="col-3 text-center text-danger text-mute" href='#'
                            v-on:click.prevent="()=> price = bid">
                            bid <br/>
                            <small>{{ parseFloat(this.bid) }}</small>
                        </a>
                        <a class="col-6 text-center h4" href="#"
                             v-on:click.prevent="()=> price = current_price">
                            <span id="symbol-data" class="h5">{{ this.symbolData }}</span><br/>
                            <span id="current-price">{{ parseFloat(this.current_price) }}</span>
                        </a>
                        <a class="col-3 text-center text-black" href='#'
                            v-on:click.prevent="()=> price = ask">
                            ask <br/>
                            <small>{{ parseFloat(this.ask.toString()) }}</small>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    
                    <div class="input-group">
                        <input  type="number" class="form-control" name="price" id="price" 
                                min='0' placeholder="price" required 
                                :step="this.step? this.step : '0.01'"
                                v-model="price" />
                    </div>

                    <div class="input-group">
                        <label for="amount text-center">
                            Amount
                            <input  type="radio" v-model="amount_or_risk" 
                                    name="amount-or-risk" id="amount" value="amount"/>
                        </label>
                        <label for="risk text-right">
                            Risk
                            <input  type="radio" v-model="amount_or_risk"
                                    name="amount-or-risk" id="risk" value="risk" />
                        </label>
                    </div>

                    <div class="input-group">
                        <input  v-if="( this.amount_or_risk !== 'risk' )"
                                type="number" class="form-control w-75" name="amount" id="amount" 
                                min='0' placeholder="amount" required 
                                step='1'
                                v-model="amountUSDT" />
                        <span v-if="( this.amount_or_risk !== 'risk' )"
                            class='w-25'>
                                {{ this.risk }}
                        </span>
                        
                        <input  v-if="( this.amount_or_risk === 'risk' )"
                                type="number" class="form-control text-danger" name="risk" id="risk" 
                                min='0' placeholder="risk" required 
                                step='1'
                                v-on:input="(e) => this.updateAmountRisk(0,this.risk)"
                                v-model="risk" />
                        
                        <ul id="amount-preset" class='d-flex w-100 list-unstyled'>
                            <li v-for="(num, index) in [100,250,500,1000]" v-bind:key="index"
                                class="col">
                                <a href="#" v-on:click.prevent="amountUSDT = num;">
                                    {{ num }}
                                </a>
                            </li>
                        </ul>
                        
                    </div>

                    <div class="text-center mt-3">
                        <button class="btn btn-primary m-auto" v-on:click.prevent="placeBuyOrder">
                            Buy <b>{{ this.amountUSDT }}</b> <small>USDT</small> <br/>at <b class="text-mute">{{ parseFloat(this.price) }}</b>
                        </button>
                    </div>
                    
                </div>
            </div>
        </div>
        <!-- column right: % -->
        <div class='col-6 mb-2'>
            
            <div class="card-header mb-3">
                    <b>{{ this.symbolData }}</b>
                    <span v-if="this.symbol_filters"
                        :class="('text-' + (this.symbol_filters.status !== 'TRADING'? 'danger' : 'success'))"
                    >{{ this.symbol_filters.status }}</span>
                    
            </div>

            <span class='d-block'>
                % benefit 
                <small class='text-success text-mute'>﹩{{ (this.amountUSDT * (this.price_t1/this.current_price - 1)).toFixed() }}</small>
            </span>
            <label for="percent_t1" class="xl">
                <input  type="number" class="form-control w-25 float-left"
                        name="percent_t1" id="percent_t1" 
                        min='0' placeholder="percent profit" required 
                        step='0.1'
                        v-model="percent_t1" />
                <div class="input-group-append w-25 float-left">
                    <span class="input-group-text">%</span>
                </div>
                <input  type="number" class="form-control w-50"
                        name="price_t1" id="price_t1" 
                        min='0' placeholder="take profit price" required 
                        :step="this.step? this.step : '0.01'"
                        v-model="price_t1" />
            </label>
        

            
            <span class='text-danger d-block'>
                % stop loss
                <small class='text-danger text-mute'>﹩{{ (this.amountUSDT * (this.price_stop_loss/this.current_price - 1)).toFixed() }}</small>
            </span>
            <label for="percent_t1 input-group">
                <input  type="number" class="form-control text-danger w-25 float-left"
                        name="percent_stop_loss" id="percent_stop_loss" 
                        min='0' placeholder="percent stop loss" required 
                        step="0.1"
                        v-model="percent_stop_loss" />
                <div class="input-group-append w-25 float-left">
                    <span class="input-group-text">%</span>
                </div>                        
                <input  type="number" class="form-control text-danger w-50"
                        name="price_stop_loss" id="price_stop_loss" 
                        min='0' placeholder="stop loss price" required 
                        :step="this.step? this.step : '0.01'"
                        v-model="price_stop_loss" />
            </label>




            <div class='text-center w-100 mt-3'>
                <button class="btn btn-secondary m-auto" v-on:click.prevent="placeOCOOrder">
                    <small>winning <b>﹩{{ (this.amountUSDT * (this.price_t1/this.current_price - 1)).toFixed() }}</b></small>
                    <br/>
                    Place OCO Order
                    <br/>
                    <small>losing <b>﹩{{ (this.amountUSDT * (this.price_stop_loss/this.current_price - 1)).toFixed() }}</b></small>
                </button>
            </div>
        </div>
        <!-- <trades-list :symbol="this.symbolData" /> -->
    </div>
    
</template>

<script>
    import qs from 'qs';
    //import TradesList from './TradesList.vue';
    export default {
        name: 'PriceAndAmount',
        props: {
            symbol: null
        },
        mounted() {
                // init on Load.

                this.init();
                
                /**
                 * This allows us to change the symbol from the outside, by calling 
                 *  window.bus.$emit('update-symbol', 'TFUELUSDT') 
                 * */ 
                var self = this;
                window.bus.$on('update-symbol', (sym) => {
                    // after changing this, automatically the 'watch' symbolData calls init() to reload all values in the component.
                    self.symbolData = sym;
                })

                // reload prices every 5 secs.
                window.paaInterval = setInterval( ()=> {
                    this.getBidAndAsk();
                    this.getCurrentPrice();
                    
                }, 5000);
        },
        data() {
            return {
                symbolData: this.symbol,
                step: 0.0001,
                symbol_filters: null,
                price: 0,
                current_price: 0,
                bid: 0,
                ask: 0,
                amount_or_risk: 'amount',
                amountUSDT: 100,
                risk: null,
                percent_t1: 3,
                price_t1: null,
                percent_stop_loss: 3,
                price_stop_loss:null,
                busy: false 
            }
        },
        methods: {
            // init
            init: function() {
                
                if (!this.symbolData) return;

                // Get info filters for the symbol
                const json_file_path = `/exchangeinfo/${this.symbolData}.json`;
                if (! this.doesFileExist(json_file_path)) {
                    // alert(`symbol ${this.symbolData}.json not found`);
                }
                else {
                    axios.get(json_file_path).then( (data) => {
                        
                        this.symbol_filters = data.data;
                        
                        const priceFilter = this.symbol_filters.filters.find( filt => filt.filterType === 'PRICE_FILTER')
                        if (priceFilter) {
                            this.step = parseFloat(priceFilter.tickSize);
                        }
                        return this.symbol_filters;
                    });
                }

                // init fields price, with current price.
                this.getCurrentPrice().then( price => { 
                                                this.price = price;
                                                this.updateExitPrices(price);
                                            } );
                this.getBidAndAsk();
            },
            // action methods
            placeBuyOrder: function(e) {
                
                if (this.busy) {
                    alert('can\'t place the order, it\'s busy '+this.busy);
                    return;
                }
                
                var r = confirm(`creating buy order LIMIT for ${this.symbolData} at ${this.price} paying ${this.amountUSDT} `);
                if (r !== true) return;

                this.busy   = 'placing buy order';

                axios.post('/place-buy', { 
                                        symbol: this.symbolData,
                                        price: this.price,
                                        amountUSDT: this.amountUSDT,
                                        type: 'LIMIT'
                                        },
                                        { headers: { 'X-CSRF-TOKEN': window.myToken } }) // I don't need this. Actually I am not sure it's sending anything
                            .then(response => {
                                alert('acabo');
                                if (response.data["status"] === 'success') {
                                    // show message of success.
                                } else alert('There was an error placing the order, check console.');
                                
                                console.log(response.data);
                                this.busy = false;

                            });
            },

            placeOCOOrder: function(e) {
                
                if (this.busy) {
                    alert('can\'t place the order, it\'s busy '+this.busy);
                    return;
                }

                this.busy   = 'placing oco order';

                var r = confirm(`creating buy order LIMIT for ${this.symbolData} at ${this.price} paying ${this.amountUSDT} `);
                if (r !== true) return;

                axios.post('/place-oco', { 
                                        symbol: this.symbolData,
                                        price: this.price,
                                        amountUSDT: this.amountUSDT,
                                        price_t1: this.price_t1,
                                        price_stop_loss: this.price_stop_loss,
                                        },
                                        { headers: { 'X-CSRF-TOKEN': window.myToken } }) // I don't need this. Actually I am not sure it's sending anything
                            .then(response => {
                                alert('acabo');
                                if (response.data["status"] === 'success') {
                                    // show message of success.
                                } else alert('There was an error placing the order, check console.');
                                
                                console.log(response.data);
                                this.busy = false;

                            });
            },

            // UI change method: only one of the 2 parameters will have a value (the one updated)
            updateAmountRisk: function(newAmount, newRisk) {
                if (newAmount) {
                    this.risk = parseInt(newAmount * this.percent_stop_loss/100);
                    console.log('setting risk to '+this.risk);
                } else if (newRisk) {
                    this.amountUSDT = parseInt(newRisk * 100 / this.percent_stop_loss);
                    console.log('setting AMOUT to '+this.amountUSDT);
                }
            },
            // UI change method: only one of the 3 parameters will have a value (the one updated)
            updateExitPrices: function(newPrice = 0 , newPercentTop = 0, newPercentBottom = 0) {
                const newPriceFloat      = parseFloat(newPrice || this.price);
                const newPercentTopFloat = parseFloat(newPercentTop || this.percent_t1);
                if (newPercentTopFloat) {
                    this.price_t1           = this.parsePrice(newPriceFloat * (1 + newPercentTopFloat/100));
                }
                const newPercentBottomFloat = parseFloat(newPercentBottom || this.percent_stop_loss);
                if (newPercentBottomFloat) {
                    this.price_stop_loss    = this.parsePrice(newPriceFloat * (1 - newPercentBottomFloat/100));
                    this.risk               = this.updateAmountRisk(this.amountUSDT, 0);
                }
            },
            // helpers
            // API Helper with Binance API Connection
            getCurrentPrice: async function() {   
                const params    = { symbol : this.symbolData };

                delete axios.defaults.headers.common['X-Requested-With']; // needed
                const r = await axios.get('https://api.binance.com/api/v3/ticker/price?' + qs.stringify(params));
                // console.log("Price got ", r.data);
                if (r.data.price) {
                    this.current_price = r.data.price;
                    return r.data.price;
                }
                return false;
            },
            getBidAndAsk:  async function() {   
                const params    = { symbol : this.symbolData };

                delete axios.defaults.headers.common['X-Requested-With']; // needed
                const r = await axios.get('https://api.binance.com/api/v3/ticker/bookTicker?' + qs.stringify(params));
                // console.log("ticker got ", r.data);
                this.bid = r.data.bidPrice;
                this.ask = r.data.askPrice;
            },
            // helper: checking existence of file synchronously
            doesFileExist: function(urlToFile) {
                var xhr = new XMLHttpRequest();
                xhr.open('HEAD', urlToFile, false);
                xhr.send();
                
                return xhr.status !== 404;
            },
            parsePrice: function(floatPrice) {
                const tick_size = this.step;                
                return floatPrice.toFixed(this.getPrecision(tick_size));
            },
            // from 0.001 returns 3, from 0.1 returns 1
            getPrecision: function(floatValue) {
                if (floatValue.toString().includes('.') === false) return 0;
                else return floatValue.toString().indexOf('1')  - 1
            }
            
        },
        watch: {
            symbolData: function(newVal, oldVal) {
                // updates all values using the symbol
                console.log('value changed from ' + oldVal + ' to ' + newVal);
                this.init();
            },
            // when te price change, the stop prices adapt to it
            price: function(newPrice, oldPrice) {
                console.log('price changed from ' + oldPrice + ' to ' + newPrice);
                this.updateExitPrices(newPrice, 0, 0);
                console.log(`recalculate benefit ${this.price_t1} and loss ${this.price_stop_loss}`);
            },
            percent_t1: function(newV, oldV) { 
                this.updateExitPrices(0, newV, 0); 
            },
            percent_stop_loss: function(newV, oldV) { 
                this.updateExitPrices(0, 0, newV); 
            },
            amountUSDT: function(newV, oldV) {
                console.log('changed Amount', this.amount_or_risk);
                if (this.amount_or_risk === 'amount') {
                    console.log('changed updating risk');
                    this.updateAmountRisk(newV, 0);
                }
            }
            // amount: function(newV, oldV) {
            //     console.log('updte amount USDT ' + oldV + ' to ' + newV);
            //     this.updateAmountRisk(newV, 0);
            // },
            // risk: function(newV, oldV) {
            //     this.updateAmountRisk(0, newV);
            // }
        },
        components: {
            // 'trades-list' : TradesList
        }
    }

    /**
     * - call from Laravel with 
     *  symbol=ETHUSDT
     * - on create component, load Binance filters stored in a json locally, named ETHUSDT.json
     * 
     */


</script>
