<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {


        $query = Customer::query();

        if ($request->filled('keyword')) {

            $keyword = $request->keyword;

            $query->where('email', 'like', '%' . $keyword . '%')
                ->orWhereHas('orders', function($sub_query) use ($keyword) {
                    $sub_query->where('order_number', 'like', '%' . $keyword . '%')
                        ->orWhereHas('orderItems.item', function($sub_query) use ($keyword) {
                            $sub_query->where('name', 'like', '%' . $keyword . '%');
                        });
                });
        }

        $customers = $query->with(['orders.orderItems.item'])->get();

        return view('customers.index', compact('customers'));
    }
}
