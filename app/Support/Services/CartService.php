<?php

namespace App\Support\Services;
use App\Models\Shopcart;
use Carbon\Carbon;
use App\Models\Coupon;

class CartService
{
    protected $user;
    public $cartItems;

    public function __construct()
    {
        $this->user = auth('user')->user();
        $this->cartItems = $this->cartContent();
    }


    public function cartContent()
    {
        if (!empty($this->user->cart)) {
            return collect(json_decode($this->user->cart->content, true));
        }
        return collect();
    }


    public function find($rowid)
    {

        $item = count($this->cartItems) > 0 ? $this->cartItems->where('rowid', $rowid) : null;
//        return ( !empty($item) && count($item) > 0 ) ? true : false;
        return !empty($item) && count($item) > 0;
    }

    public function add($product, $attributes = [])
    {
        // if user have a cart
        if ( $this->user->cart != null ) {
            $cartItems = $this->cartAddProduct($product, $attributes);
            $this->cartStore($cartItems);
            return true;
        } else {
            // / if user have no cart
            $cartItems = $this->cartAddProduct($product, $attributes);
            $cart = new Shopcart;
            $cart->content = $cartItems->toJson();
            $cart->expire_at = Carbon::now()->addMonths(1);
            // dd($this->total());
            $cart->total = $this->subTotal(); // must
            return $this->user->cart()->save($cart);
        }
        return false;
    }



    public function cartAddProduct($product, $attributes = [], $quantity = 0)
    {
        $cartItems = collect();
        // add old items to cartItems obj
        if ( count($this->cartItems) > 0 ) {
            $cartItems = $this->cartItems->map(function($item, $key) {
                return $item;
            });
        }
        // add new item to cartItems obj
        $cartItem = [
            'rowid' => strtoupper(uniqid()),
            'id' => $product->id,
            'name' => $product->name,
            'quantity' => (int) $quantity != 0 ? (int) $quantity : 1,
            'price' => $product->price,
            'attributes' => $attributes
        ];

        $cartItems->push($cartItem);
        $this->cartItems = $cartItems;
        return $cartItems;
    }



    public function update($rowid, $quantity, $attributes = [])
    {
        // if user have a cart
        if ( $this->cartItems->count() > 0 ) {
            $cartItems = $this->updateProduct($rowid, $quantity, $attributes);
            $this->cartStore($cartItems);
            return true;
        }
        return false;
    }



    public function updateProduct($rowid, $quantity, $attributes = [])
    {
        return $this->cartItems->map(function($item, $key) use ($rowid, $quantity, $attributes) {
            if ($item['rowid'] == $rowid) {
                $item['quantity'] = !empty($quantity) ? (int) $quantity : $item['quantity'];
                // if request have attributes update it
                if ( !empty($attributes['color']) || !empty($attributes['size']) ) {
                    $item['attributes']['color'] = !empty($attributes['color']) ? $attributes['color'] : null ;
                    $item['attributes']['size'] = !empty($attributes['size']) ? $attributes['size'] : null ;
                }
            }
            return $item;
        });
    }


    public function cartIncrementProduct($product, $attributes = [])
    {
        return $this->cartItems->map(function($item, $key) use ($product, $attributes) {
            if ($item['id'] == $product->id) {
                $item['quantity'] = (int) $item['quantity'] + 1;
                // if request have attributes update it
                if ( !empty($attributes['color']) || !empty($attributes['size']) ) {
                    $item['attributes']['color'] = !empty($attributes['color']) ? $attributes['color'] : null ;
                    $item['attributes']['size'] = !empty($attributes['size']) ? $attributes['size'] : null ;
                }
            }
            return $item;
        });
    }


    public function remove($rowid)
    {
        $res = false;
        foreach ($this->cartItems as $key => $cartItem) {
            if ( $cartItem['rowid'] == $rowid ) {
                $res = true;
                unset($this->cartItems[$key]);
                $this->cartStore($this->cartItems);
                break;
            }
        }
        return $res;
    }


    public function applyCoupon($coupon)
    {
        if ( !empty($this->user->cart) ) {
            $this->user->cart->coupon_id = $coupon->id;
            $this->user->cart->total = $this->total();
            return $this->user->cart->save();
        }
        return false;
    }

    public function redeemCoupon()
    {
        if ( !empty($this->user->cart) ) {
            $this->user->cart->coupon_id = null;
            $this->user->cart->save();
            $this->user->cart->total = $this->total();
            return $this->user->cart->save();
        }
        return false;
    }


    public function subTotal()
    {
        return (int) $this->cartItems->sum(function ($item) {
            return (int) $item['quantity'] * (int) $item['price'];
        });
    }



    public function total()
    {
        $cart = auth('user')->user()->cart;

        if ($cart != null ) {

            $coupon = Coupon::find($cart->coupon_id);

            if ( !$coupon ) {
                return (int) $this->subTotal();
            }

            $total = ($cart != null && !$cart->coupon->isEmpty()) ? $this->subTotal() - $coupon->discount($cart) : 0;
            return $total >= 0 ? $total : 0;
        }

        return 0;
    }


    public function quantity()
    {
        return $this->cartItems->sum('quantity');
    }


    public function cartStore($cartItems)
    {
        $this->cartItems = $cartItems;
        $this->user->cart->content = $cartItems->toJson();
        $this->user->cart->expire_at = Carbon::now()->addMonths(1);
        $this->user->cart->total = $this->total();
        $this->user->cart->save();
    }

    public function clear()
    {
        $this->cartStore(collect([]));
    }

}
