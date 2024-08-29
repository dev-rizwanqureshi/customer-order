<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {


        $query = Customer::query()
            ->select('id', 'email', 'name');

        if ($request->filled('keyword')) {

            $keyword = $request->keyword;

            $query->where('email', 'like', '%' . $keyword . '%')
                ->orWhereHas('orders', function($sub_query) use ($keyword) {
                    $sub_query->select('id', 'order_number', 'customer_id')
                    ->where('order_number', 'like', '%' . $keyword . '%')
                        ->orWhereHas('orderItems', function($sub_query) use ($keyword) {
                            $sub_query->select('id', 'order_id', 'item_id')
                            ->whereHas('item', function($sub_query) use ($keyword) {
                                $sub_query->select('id', 'name')
                                ->where('name', 'like', '%' . $keyword . '%');
                            });
                        });
                });
        }

        $customers = $query->with(['orders' => function($query) {
            $query->select('id', 'order_number', 'customer_id');
        }, 'orders.orderItems' => function($query) {
            $query->select('id', 'order_id', 'item_id');
        }, 'orders.orderItems.item' => function($query) {
            $query->select('id', 'name');
        }])->get();



        return view('customers.index', compact('customers'));
    }
}
