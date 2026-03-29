<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        return view('customer.index', [
            'canManage' => auth()->user()->canManage(),
            'canDelete' => auth()->user()->canDelete(),
        ]);
    }

    public function nextId()
    {
        return response()->json(['customer_id' => $this->generateNextCustomerId()]);
    }

    private function generateNextCustomerId(): string
    {
        $latest = Customer::where('customer_id', 'like', 'CST%')
            ->orderByRaw('LENGTH(customer_id) DESC, customer_id DESC')
            ->value('customer_id');
        if (!$latest) return 'CST001';
        $number = (int) substr($latest, 3);
        return 'CST' . str_pad($number + 1, 3, '0', STR_PAD_LEFT);
    }

    public function data()
    {
        $customers = Customer::latest()->get()->map(fn($c) => [
            'id'          => $c->id,
            'customer_id' => $c->customer_id,
            'name'        => $c->name,
            'address'     => $c->address ?? '-',
            'pic_name'    => $c->pic_name ?? '-',
            'contact'     => $c->contact ?? '-',
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

        $customer = Customer::create([
            'customer_id' => $this->generateNextCustomerId(),
            'name'        => $request->name,
            'address'     => $request->address,
            'pic_name'    => $request->pic_name,
            'contact'     => $request->contact,
        ]);

        return response()->json(['message' => 'Customer berhasil ditambahkan.', 'id' => $customer->id]);
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
        if (!auth()->user()->canDelete()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus data.'], 403);
        }

        $customer->delete();
        return response()->json(['message' => 'Customer berhasil dihapus.']);
    }
}
