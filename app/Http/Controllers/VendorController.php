<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index()
    {
        return view('vendor.index', [
            'canManage' => auth()->user()->canManage(),
            'canDelete' => auth()->user()->canDelete(),
        ]);
    }

    public function nextId()
    {
        return response()->json(['vendor_code' => $this->generateNextVendorCode()]);
    }

    private function generateNextVendorCode(): string
    {
        $latest = Vendor::where('vendor_code', 'like', 'VND%')
            ->orderByRaw('LENGTH(vendor_code) DESC, vendor_code DESC')
            ->value('vendor_code');
        if (!$latest) return 'VND001';
        $number = (int) substr($latest, 3);
        return 'VND' . str_pad($number + 1, 3, '0', STR_PAD_LEFT);
    }

    public function list()
    {
        $vendors = Vendor::orderBy('name')->get(['id', 'vendor_code', 'name']);
        return response()->json($vendors);
    }

    public function data()
    {
        $vendors = Vendor::latest()->get()->map(fn($v) => [
            'id'          => $v->id,
            'vendor_code' => $v->vendor_code,
            'name'        => $v->name,
            'pic_name'    => $v->pic_name ?? '-',
            'contact'     => $v->contact ?? '-',
            'address'     => $v->address ?? '-',
        ]);
        return response()->json(['data' => $vendors]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'pic_name' => 'nullable|string|max:255',
            'contact'  => 'nullable|string|max:50',
            'address'  => 'nullable|string',
        ]);

        Vendor::create([
            'vendor_code' => $this->generateNextVendorCode(),
            'name'        => $request->name,
            'pic_name'    => $request->pic_name,
            'contact'     => $request->contact,
            'address'     => $request->address,
        ]);

        return response()->json(['message' => 'Vendor berhasil ditambahkan.']);
    }

    public function update(Request $request, Vendor $vendor)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'pic_name' => 'nullable|string|max:255',
            'contact'  => 'nullable|string|max:50',
            'address'  => 'nullable|string',
        ]);

        $vendor->update($request->only(['name', 'pic_name', 'contact', 'address']));

        return response()->json(['message' => 'Vendor berhasil diupdate.']);
    }

    public function destroy(Vendor $vendor)
    {
        if (!auth()->user()->canDelete()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus data.'], 403);
        }

        $vendor->delete();
        return response()->json(['message' => 'Vendor berhasil dihapus.']);
    }
}
