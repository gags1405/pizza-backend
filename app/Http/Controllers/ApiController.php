<?php

namespace App\Http\Controllers;
use App\Products;
use App\ProductOption;
use App\Customer;
use App\Address;
use App\Order;
use App\OrderProduct;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function products(){
    	$data = array();
    	
    	$products = Products::get();
    	foreach($products as $product){
    		$arr['id'] = $product->id;
    		$arr['name'] = $product->name;
    		$arr['description'] = $product->description;
    		$arr['image'] = "https://pizza.intellemind.com/pizza/public/storage/".$product->image;
    		$arr['price'] = $product->price;
    		$arr['sizes'] = $product->options;
    		$data[] = $arr; 
    	}

    	if($data){
          return response()->json([
              'success' => true,
              'code'    => 200, 
              'message' => 'success',
              'conversionRate' => 0.92,
              'result' => $data            
            ], 201);
        }else{
          return response()->json([
                  'success' => false,
                  'code'    => 404, 
                  'message' => 'No record found.'
               ], 404);
        }
    }

    public function checkout(Request $request){
        $customer = Customer::where('phone', $request->mobile)->count();
        
        if($customer > 0){
          $customer = Customer::where('phone', $request->mobile)->first();
        }else{
          $customer = new Customer;
        }
        
        $customer->name = $request->name;
        $customer->phone = $request->mobile;
        $customer->email = $request->email;
        $customer->save();

        $address = Address::where(['customer_id'=> $customer->id, 'address'=>$request->address])->count();
        if($address == 0){
          $address = new Address;
          $address->address = $request->address;
          $address->customer_id = $customer->id;
          $address->save();

          $customer->address_id = $address->id;
          $customer->save();
        }

        $order = new Order;
        $order->customer_id = $customer->id;
        $order->currency = $request->currency;
        $order->total = $request->totalPrice;
        $order->deliveryCharge = $request->deliveryCharge;
        $order->save();

        foreach($request->order as $product){
          $op = new OrderProduct;
          $op->order_id = $order->id;
          $op->product_id = $product['id'];
          $op->quantity = $product['quantity'];
          $op->size_id   = $product['size'];
          $op->price   = $product['price'];
          $op->save();
        }

        return response()->json([
              'success' => true,
              'code'    => 200, 
              'message' => 'successfully placed order',
                       
            ], 201);
    }

    public function orders(Request $request){
      $data = array();
      $customer = Customer::where('phone', $request->mobile)->first();

      $orders = Order::where('customer_id', $customer->id)->orderBy('id', 'desc')->get();

      foreach($orders as $order){
        $arr = array();
        $orderproducts = array();
        $arr['order_id'] = $order->id;
        $arr['totalPrice'] = $order->total;
        $arr['currency'] = $order->currency;
        $arr['deliveryCharge'] = $order->deliveryCharge;
        $arr['order_at'] = $order->added_at;
        $arr['products'] = $this->getProductDetails($order->id);
        
        $data[] = $arr;
      }

      if($data){
        return response()->json([
            'success' => true,
            'code'    => 200, 
            'message' => 'success',
            'result' => $data            
          ], 201);
      }else{
        return response()->json([
                'success' => false,
                'code'    => 404, 
                'message' => 'No record found.'
             ], 404);
      }

    }

    public function getProductDetails($order_id){
      $order_products = array();
      $products = OrderProduct::where('order_id', $order_id)->get();
      foreach ($products as $product) {
          $p['product_id'] = $product->detail->id;
          $p['product_name'] = $product->detail->name;
          $p['product_order_price'] = $product->price;
          $p['quantity'] = $product->quantity;
          $p['ordered_size'] = $product->size_id;
          $p['image'] =  "https://pizza.intellemind.com/pizza/public/storage/".$product->detail->image;
          $order_products[] = $p; 
      }
      return $order_products;
    }
}
