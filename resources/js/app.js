/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
import qs from 'qs';

// const api = require('@marcius-capital/binance-api')

window.Vue  = require('vue');

// for future ajax requests with axios, I ensure that the calls are tokenized
window.myToken = document.querySelector('[name="csrf-token"]').getAttribute('content');
// axios.defaults.headers.common['X-CSRF-TOKEN'] = window.myToken; // can't use it or binance api doesnt work

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

/**
 * VueJS bus: we use it to trigger events inside the Vue components.
 * USAGE: window.bus.$emit('event-name', 'DATA send used inside component');
 * The VueJS component must have inside mounted the definition window.bus.$on('event-name', (data) => { ...
 *  */ 
window.bus = new Vue();
/* All JS important fns about this app. */
window.binanceMethods = {
    selectSymbol(symbol) {

        // 1) Update the Vue component!
        window.bus.$emit('update-symbol', symbol);
        localStorage.setItem('current-symbol', symbol);

        // 2) Update the PHP component for orders.
        window.UIMethods.reloadTemplate('binance-trades', { symbol });

        // show the button in the list highlighted.
        Array.from(document.querySelectorAll('.coin-selector')).forEach( el => 
            el.classList.remove('active')   
        );
        document.querySelector('.coin-'+symbol.toLowerCase()).classList.add('active');
    },
    cancelOrder(symbol, orderId) {
        alert(`a ver ${symbol}, ${orderId} `);
        axios.post('cancel-order', 
                    { symbol, orderId },
                    { headers: { 'X-CSRF-TOKEN': window.myToken } })
                        .then( result => {
                           // alert(result.data.msg);
                            console.log(result);
                        }).catch( err => console.error(err));
    }
}

window.UIMethods = {
    reloadTemplate: function(templateName, args = {}) { // ie. binance-balance
        // console.log('reloading' + templateName, args);
        const container = document.querySelector("[data-templatename='"+templateName+"']");
        container.classList.add('loading');
        // call the template to laravel, via get, and passing the params template and others if needed
        axios.get('load-partial-ajax?template='+ templateName + "&" + qs.stringify(args))
                    .then(pageHtml => {
                        // console.log('the return is',pageHtml.data);
                        container.innerHTML = pageHtml.data;
                        container.classList.remove('loading');
        });

    }
}

Vue.component('price-and-amount', require('./components/PriceAndAmount.vue').default);


document.addEventListener('DOMContentLoaded', function() {
    // when loading the page, initializr to the last selected symbol
    let currentSymbol = localStorage.getItem('current-symbol') || 'BTCUSDT';
    window.binanceMethods.selectSymbol(currentSymbol); // this inits the values for price-and-amount vuejs component

    if (document.querySelector('[data-templatename="binance-trades"]')) {
        window.tradesInterval = setInterval( () => {
            // const symbol        = document.querySelector('#symbol-data').textContent;
            // const current_price = document.querySelector('#current-price').textContent;
            // window.UIMethods.reloadTemplate('binance-trades', { symbol:symbol, current_price:current_price } );
        }, 6000);
    }
    
});




// window.updateVueTemplate = function(compName, vueFile) {
//     window.Vue.component(compName, require('./components/'+vueFile+'.vue').default);
// }
// window.teest = function() {
//     // const elem = document.createElement('price-and-amount');
//     const elem = document.querySelector('#jander');
//     // elem.setAttribute('symbol', 'TFUELUSDT')
//     // const target = document.querySelector('#price-and-amount');
//     // target.parentNode.insertBefore(elem, target);
//     window.updateVueTemplate('jander','PriceAndAmount');
//     // window.updateVueTemplate('price-and-amount','PriceAndAmount');
// }
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
});
