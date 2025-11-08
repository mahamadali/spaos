<?php

namespace Modules\Product\Trait;

use App\Models\Branch;
use App\Models\User;
use Modules\Product\Models\Cart;
use Modules\Product\Models\Order;
use Modules\Product\Models\OrderGroup;
use Modules\Product\Models\OrderItem;
use Modules\Tag\Models\Tag;
use Modules\Tax\Models\Tax;

trait ProductTrait
{
    /**
     * Request $data
     * - location_id
     * - shipping_address_id
     * - billing_address_id
     * - phone
     * - type
     * - tips
     * - payment_method
     * - user_id
     */
    protected function createCart($booking_product, $booking_details)
    {
        foreach ($booking_product as $product) {
            $cart = new Cart();
            $cart->user_id = $booking_details['user_id'];
            $cart->product_id = $product['product_id'];
            $cart->location_id = $booking_details['location_id'] ?? 1;
            $cart->product_variation_id = $product['product_variation_id'];
            $cart->qty = $product['product_qty'];
            $cart->save();
        }

        $branch_data = Branch::with('address')->where('id', $booking_details['branch_id'])->first();

        $address_id = $branch_data ? $branch_data->address->id : null;

        $user_data = User::where('id', $booking_details['user_id'])->first();

        $mobile = $user_data ? $user_data->mobile : null;

        $data = [
            'user_id' => $booking_details['user_id'],
            'location_id' => $booking_details['location_id'] ?? 1,
            'shipping_address_id' => $address_id,
            'billing_address_id' => $address_id,
            'type' => 'order',
            'tips' => $booking_details['payment']->tip_amount,
            'payment_method' => $booking_details['payment']->transaction_type,
            'alternative_phone' => $mobile,
            'phone' => $mobile,
            'delivery_status'=>$booking_details['delivery_status']  ?? 'order_placed',
            'payment_status'=>$booking_details['payment_status']  ?? 'unpaid',


        ];

        return $orderId = $this->createOrder($data);
    }

    protected function createOrder($data)
    {
        $userId = $data['user_id'];

        $location_id = $data['location_id'];

        $carts = Cart::where('user_id', $userId)->where('location_id', $location_id)->get();

        $tax = Tax::where('module_type', 'products')->where('status', 1)->get();



        if (count($carts) > 0) {
            // check carts available stock -- todo::[update version] -> run this check while storing OrderItems
            foreach ($carts as $cart) {
                $productVariationStock = $cart->product_variation->product_variation_stock ? $cart->product_variation->product_variation_stock->stock_qty : 0;
                if ($cart->qty > $productVariationStock) {
                    $message = $cart->product_variation->product->name . __('messages.is_out_of_stock');

                    return response()->json(['message' => $message, 'status' => false]);
                }
            }


            $sub_total_amount = getSubTotal($carts, false, '', false);

            $total_tax_amount = 0;
            if ($tax && count($tax) > 0) {
                foreach ($tax as $taxItem) {

                    if ($taxItem->type == 'percent') {
                        $total_tax_amount += $sub_total_amount * $taxItem->value / 100;
                    } elseif ($taxItem->type == 'fixed') {
                        $total_tax_amount += $taxItem->value;
                    }
                }
            }


            // create new order group
            $orderGroup = new OrderGroup;
            $orderGroup->user_id = $userId;
            $orderGroup->shipping_address_id = $data['shipping_address_id'];
            $orderGroup->billing_address_id = $data['billing_address_id'];
            $orderGroup->location_id = $location_id;
            $orderGroup->phone_no = $data['phone'];
            $orderGroup->alternative_phone_no = $data['alternative_phone'];
            $orderGroup->sub_total_amount = $sub_total_amount;
            $orderGroup->tax = json_encode($tax);
            $orderGroup->total_tax_amount = $total_tax_amount;
            $orderGroup->total_coupon_discount_amount = 0;
            $orderGroup->type = $data['type'] ?? 'order';
            $orderGroup->payment_status = 'paid';

            // todo::[for eCommerce] handle exceptions for standard & express
            $orderGroup->total_shipping_cost = 0;  //$logisticZone->standard_delivery_charge
            $orderGroup->total_tips_amount = $data['tips'];

            $orderGroup->grand_total_amount = $orderGroup->sub_total_amount + $orderGroup->total_tax_amount + $orderGroup->total_shipping_cost + $orderGroup->total_tips_amount - $orderGroup->total_coupon_discount_amount;
            $orderGroup->save();

            // order -> todo::[update version] make array for each vendor, create order in loop
            $order = new Order;
            $order->order_group_id = $orderGroup->id;
            $order->user_id = $userId;
            $order->location_id = $location_id;
            $order->total_admin_earnings = $orderGroup->grand_total_amount;
            $order->delivery_status = $data['delivery_status'];
            $order->payment_status = $data['payment_status'];



            $order->shipping_cost = $orderGroup->total_shipping_cost; // todo::[update version] calculate for each vendors
            $order->tips_amount = $orderGroup->total_tips_amount; // todo::[update version] calculate for each vendors

            $order->save();



            // order items
            $total_points = 0;
            foreach ($carts as $cart) {

                $unit_price = variationDiscountedPrice($cart->product_variation->product, $cart->product_variation);

                $total_tax = 0;
                // if ($tax && count($tax) > 0) {
                //     foreach ($tax as $taxItem) {
                //         if ($taxItem->type == 'percent') {
                //             $total_tax += $unit_price * $cart->qty * $taxItem->value / 100;
                //         } elseif ($taxItem->type == 'fixed') {
                //             $total_tax += $taxItem->value;
                //         }
                //     }
                // }

                $orderItem = new OrderItem;
                $orderItem->order_id = $order->id;
                $orderItem->product_variation_id = $cart->product_variation_id;
                $orderItem->qty = $cart->qty;
                $orderItem->location_id = $location_id;
                $orderItem->unit_price = $unit_price;
                $orderItem->total_tax = $total_tax;
                $orderItem->total_price = $orderItem->unit_price * $orderItem->qty + $orderItem->total_tax;
                $orderItem->save();

                $product = $cart->product_variation->product;
                $product->total_sale_count += $orderItem->qty;

                // minus stock qty
                try {
                    $productVariationStock = $cart->product_variation->product_variation_stock;
                    $productVariationStock->stock_qty -= $orderItem->qty;
                    $productVariationStock->save();
                } catch (\Throwable $th) {
                    throw $th;
                }

                $product->stock_qty -= $orderItem->qty;
                $product->save();

                // category sales count
                if ($product->categories()->count() > 0) {
                    foreach ($product->categories as $category) {
                        $category->total_sale_count += $orderItem->qty;
                        $category->save();
                    }
                }
                $cart->delete();
            }

            $order->save();
            // payment gateway integration & redirection
            $orderGroup->payment_method = $data['payment_method'];
            $orderGroup->save();

            return $order->id;
        }
    }
}
