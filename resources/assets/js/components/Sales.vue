<template>
    <div>
        <div class="col-md-8">
            <div class="panel panel-default">
                <div class="panel-heading">List Item</div>
                <div class="panel-body">
                    <div class="row">
                        <!--
                        <div class="col-md-2">
                            <div class="list-group">
                                <a href="#" v-for="category in categories" class="list-group-item list-group-item-info" v-on:click="setSearchItemKey(category.id)" track-by="$index" >{{ category.name }}</a>
                            </div>
                        </div>
                        -->
                        <div class="col-md-12">
                            <input type="text" class="form-control" placeholder="Search for..." v-model="searchProductQuery.q" v-on:keyup="searchProduct">
                            <br />
                            <div class="row">
                                <div v-for="product in products.data" key="id" class="col-md-4" :title="product.name">
                                    <div class="thumbnail" @click="storeItemToCart(product)">
                                        <div class="caption">
                                            <h4>{{ product.name | truncate(15) }}</h4>
                                            <p>Price : {{ product.price }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    &nbsp;<span class="pull-right">Products from {{ products.from }} to {{ products.to }} total {{ products.total }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">Cart</div>
                <ul class="list-group cart-item">
                    <li class="list-group-item list-group-item-warning" v-show="!cartCount">Cart item is empty</li>
                    <li class="list-group-item" v-for="cartItem in cart" track-by="id" v-on:click="decreaseCartQty(cartItem)">
                        {{ cartItem.name }}
                        <span class="pull-right">
                            ({{ cartItem.price }} x {{ cartItem.quantity }})
                            <i class="glyphicon glyphicon-remove cart-item-action" v-on:click="deleteItemFromCart(cartItem)"></i>
                        </span>
                        <!-- <button class="pull-right" v-on:click="deleteItemFromCart(cartItem)">-</button> -->
                    </li>
                </ul>
                <div class="panel-footer">
                    Total Item:
                    <span class="pull-right">
                        {{ cartCount }}
                        <i class="glyphicon glyphicon-refresh cart-item-action" v-on:click="clearCart()"></i>
                    </span>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-body">
                    <form>

                        <larapos-autocomplete label="Room Name" src="/pos/public/api/customers" placeholder="Search Room" :select="autocompleteSelect"></larapos-autocomplete>


                        <div class="form-group">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Enter Cash Ammount" v-model="form.totalPayment">
                                <span class="input-group-btn">
                                    <button class="btn btn-success" type="button" :disabled="(amountDue > 0 || cartCount == 0)" v-on:click="createSale">Finish Sale</button>
                                </span>
                            </div>
                        </div>


                        <div class="form-group">
                             <h3 class="form-control-static text-warning">Total : {{ totalPrice }}</h3>
                        </div>

                        <div class="form-group">
                            <h3 class="form-control-static text-warning"> Change : {{ change }}</h3>
                        </div>






                        <div class="form-group">
                            <input type="hidden" class="form-control" v-model="form.comments">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

export default {
    mounted() {
        this.fetchData()
    },

    data() {
        return {
            cart: [],
            categories: [],
            form: {
                customer: {},
                comments: null,
                totalPayment: null,
            },
            products: [],
            searchProductQuery: {
                limit : 2,
                q: null
            }
        }
    },

    computed: {
        cartCount: function(){
            return this.cart.length
        },
        totalPrice: function(){
            return this.cart.reduce(function(prev, item){
                return prev + (item.price * item.quantity)
            }, 0)
        },
        amountDue: function() {
            return this.totalPrice - this.form.totalPayment
        },
        change:function(){
             return this.form.totalPayment - this.totalPrice 
        },
    },

    methods: {
        autocompleteSelect(data) {
            this.form.customer = data
        },
        storeItemToCart: function(item) {
            var ids = _.map(this.cart, 'id')

            if (!_.includes(ids, item.id)) {
                item.quantity = 1
                this.cart.push(item)
            } else {
                var index = _.findIndex(this.cart, item)
                this.cart[index].quantity = this.cart[index].quantity + 1
            }
        },

        decreaseCartQty: function(item) {
            var index = _.findIndex(this.cart, item)

            if (this.cart[index].quantity - 1 == 0) {
                this.deleteItemFromCart(item)
            } else {
                this.cart[index].quantity = this.cart[index].quantity - 1
            }
        },

        deleteItemFromCart: function(item) {
            this.cart.splice(item)
        },

        clearCart: function() {
            if (confirm('are you sure?')) {
                $.notify('Cart items deleted', {
                    type: 'success',
                    placement: {
                        from: 'bottom'
                    }
                });

                this.cart = []
            }
        },

        fetchData: function() {
            this.$http.get('/pos/public/api/products').then(function(products) {
                this.products = products.body
            })
        },

        searchProduct: function(direct) {
            let qLength = this.searchProductQuery.q.length

            if (this.searchProductQuery.limit <= qLength || qLength == 0)
            {
                // set delay
                setTimeout(function() {
                    this.$http.get('/pos/public/api/products', {
                        params : {
                            q: this.searchProductQuery.q
                        }
                    }).then(function(products) {
                        this.products = products.body
                    });
                }.bind(this), 100)
            }
        },

        createSale: function() {
            if (confirm('Press ok button to proceed'))
            {
                this.$http.post('/pos/public/api/sales', {
                    customer_id: this.form.customer.id,
                    comments: this.form.totalPayment,
                    items: _.map(this.cart, function(cart){
                        return {
                            product_id: cart.id,
                            quantity: cart.quantity,
                            price: cart.price
                        }
                    })
                }).then(function(response) {
                    let responseBody = response.body

                    this.cart = []
                    this.form.totalPayment = null
                    this.form.comments = null
                    this.form.customer = {
                        id:this.form.customer.id}

                    $.notify('Sales created with <a href="/pos/public/sales/receipt/' + responseBody.id + '" target="_BLANK">INVOICE</a>', {
                        type: 'success',
                        placement: {
                            from: 'bottom'
                        }
                    })

                    window.open('/pos/public/sales/receipt/' + responseBody.id)
                })
            }
        }
    }
}
</script>

<style>
.cart-item {
    max-height: 160px;
    overflow-y: scroll;
}
</style>
