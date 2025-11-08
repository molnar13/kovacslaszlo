<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\County;
use Illuminate\Http\Request;

class CountyController extends Controller
{
    public function index()
    {
        return County::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:counties',
        ]);

        $county = County::create($validated);
        return response()->json($county, 201);
    }

    public function show(County $county)
    {
        return $county;
    }

    public function update(Request $request, County $county)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:counties,name,' . $county->id,
        ]);

        $county->update($validated);
        return response()->json($county);
    }

    public function destroy(County $county)
    {
        $county->delete();
        return response()->json(null, 204);
    }
}