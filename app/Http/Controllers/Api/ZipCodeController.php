<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ZipCode;
use Illuminate\Http\Request;

class ZipCodeController extends Controller
{
    public function index()
    {
        return ZipCode::with(['settlement', 'county'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10',
            'settlement_id' => 'required|exists:settlements,id',
            'county_id' => 'required|exists:counties,id',
        ]);

        $zipCode = ZipCode::create($validated);
        return response()->json($zipCode->load(['settlement', 'county']), 201);
    }

    public function show(ZipCode $zipCode)
    {
        return $zipCode->load(['settlement', 'county']);
    }

    public function update(Request $request, ZipCode $zipCode)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10',
            'settlement_id' => 'required|exists:settlements,id',
            'county_id' => 'required|exists:counties,id',
        ]);

        $zipCode->update($validated);
        return response()->json($zipCode->load(['settlement', 'county']));
    }

    public function destroy(ZipCode $zipCode)
    {
        $zipCode->delete();
        return response()->json(null, 204);
    }
}