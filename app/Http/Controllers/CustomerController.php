<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        return view('dashboard.customer.index');
    }

    public function data()
    {
        $customers = Customer::latest()->get()->map(fn($c) => [
            'id'       => $c->id,
            'name'     => $c->name,
            'address'  => $c->address ?? '-',
            'pic_name' => $c->pic_name ?? '-',
            'contact'  => $c->contact ?? '-',
        ]);
        return response()->json(['data' => $customers]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'address'  => 'nullable|string',
            'pic_name' => 'nullable|string|max:255',
            'contact'  => 'nullable|string|max:50',
        ]);

        Customer::create($request->only(['name', 'address', 'pic_name', 'contact']));

        return response()->json(['message' => 'Customer berhasil ditambahkan.']);
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'address'  => 'nullable|string',
            'pic_name' => 'nullable|string|max:255',
            'contact'  => 'nullable|string|max:50',
        ]);

        $customer->update($request->only(['name', 'address', 'pic_name', 'contact']));

        return response()->json(['message' => 'Customer berhasil diupdate.']);
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return response()->json(['message' => 'Customer berhasil dihapus.']);
    }
}
