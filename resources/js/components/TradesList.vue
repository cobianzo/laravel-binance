<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Orders and Trades for {{ this.symbol }} </div>

                    <div class="card-body">
                        I'm an example component.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'TradesList',
        mounted() {
            console.log('Component mounted.')
        },
        props: {
            symbol: null
        }
    }
</script>
