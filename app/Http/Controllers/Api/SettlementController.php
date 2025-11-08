<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Settlement;
use Illuminate\Http\Request;

class SettlementController extends Controller
{
    public function index()
    {
        return Settlement::with('county')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'county_id' => 'required|exists:counties,id',
        ]);

        $settlement = Settlement::create($validated);
        return response()->json($settlement->load('county'), 201);
    }

    public function show(Settlement $settlement)
    {
        return $settlement->load('county');
    }

    public function update(Request $request, Settlement $settlement)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'county_id' => 'required|exists:counties,id',
        ]);

        $settlement->update($validated);
        return response()->json($settlement->load('county'));
    }

    public function destroy(Settlement $settlement)
    {
        $settlement->delete();
        return response()->json(null, 204);
    }
}