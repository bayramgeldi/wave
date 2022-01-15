<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Back\VoyagerBreadController;
use App\Order;
use App\OrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderController extends VoyagerBreadController
{

    public function getOrders()
    {
        //get All Orders as paginatable
        $orders = Order::paginate(10);

        return view('orders.index', compact('orders'));
    }

    /**
     * @throws ValidationException
     */
    protected function AddOrdersWithJSON(Request $request): \Illuminate\Http\JsonResponse
    {
        //validate incoming request
        $this->validate($request, [
            'orders' => 'required|array',
            'orders.*.summary' => 'required|array',
            'orders.*.summary.id' => 'required|integer',
        ]);
        $orders = $request['orders'];
        try{
            foreach ($orders as $rawOrder) {
                $order = new Order();
                $order->src_id = $rawOrder['summary']['id'];
                $order->number = $rawOrder['summary']['orderNumber'];
                $order->date = Carbon::parse(($rawOrder['summary']['orderDate'])/1000)->toDateTimeString();
                $order->full_name = $rawOrder['summary']['fullName'];
                $order->delivery_address_type = $rawOrder['summary']['deliveryAddressType'];
                $order->order_url = "https://www.trendyol.com/hesabim/siparislerim/".$rawOrder['summary']['orderNumber'];
                $order->save();
            }
        }catch (\Exception $e){
            throw ValidationException::withMessages(['message' => $e->getMessage()]);
        }
        return response()->json(["success"=>true, 'message' => 'Orders added successfully']);
    }

    protected function AddOrderItemsWithJSON(Request $request, $order){
        //validate incoming request
        $this->validate($request, [
            'summary' => 'required|array',
            'summary.id' => 'required|string',
            'summary.orderNumber' => 'required|string',
            'shipments.*.number' => 'required',
            'shipments.*.supplier' => 'required|array',
            'shipments.*.items' => 'required|array',
            'shipments.*.items.*.status' => 'required|string',
            'shipments.*.items.*.products' => 'required|array',
            'shipments.*.items.*.cargoInfo' => 'required|array',
        ]);
        $order = Order::where('src_id', $request["summary"]["id"])
            ->where('number', $request["summary"]["orderNumber"])
            ->where('source', "TRENDYOL")
            ->first();
        $order->cupoun = ($request["summary"]["coupons"] && $request["summary"]["coupons"][0])?$request["summary"]["coupons"][0]["amount"]:0;
        $items=[];
        foreach ($request["shipments"] as $shipment){
           foreach ( $shipment["items"] as $item){
               foreach ($item["products"] as $product){
                   $orderItem = new OrderItem();
                   $orderItem->order_id = $order->id;
                   $orderItem->name = $product["name"];
                   $orderItem->variant = $product["variant"]["name"];
                   $orderItem->count = $product["quantity"];
                   $orderItem->price = $product["finalPrice"];
                   $orderItem->actual_price = $product["originalPrice"];
                   $orderItem->shipment_number = $shipment["number"];
                   $orderItem->tracking_number =$item["cargoInfo"]["trackingNumber"];
                   $orderItem->cargo_provider = $item["cargoInfo"]["providerName"];
                   $orderItem->tracking_link = $item["cargoInfo"]["trackingLink"];
                   $orderItem->image_url = $product["imageUrl"];
                   $orderItem->item_number = $product["variant"]["itemNumber"];
                   $orderItem->url = $product["url"];
                   $orderItem->supplier_url = $shipment["supplier"]["url"];
                   $orderItem->supplier_name = $shipment["supplier"]["name"];
                   $orderItem->status = $item["status"];
                   $orderItem->save();
               }
           }
        }
        $order->save();
        return response()->json(["success"=>true, 'message' => 'Order items added successfully']);
    }
}
