<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PostalCode;
use App\Models\City;
use App\Models\County;
use Illuminate\Http\Request;

class PostalCodeController extends Controller
{
    public function index(Request $request)
    {
        $query = PostalCode::with('city.county');
        
        // Szűrés megye szerint
        if ($request->has('county')) {
            $query->whereHas('city.county', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->county . '%');
            });
        }
        
        // Szűrés város szerint
        if ($request->has('city')) {
            $query->whereHas('city', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->city . '%');
            });
        }
        
        // Lapozás
        $perPage = $request->get('per_page', 15);
        $postalCodes = $query->paginate($perPage);
        
        return response()->json($postalCodes);
    }

    public function show(PostalCode $postalCode)
    {
        $postalCode->load('city.county');
        
        return response()->json($postalCode);
    }

    public function searchByCode($code)
    {
        $postalCode = PostalCode::with('city.county')
            ->where('code', $code)
            ->firstOrFail();
        
        return response()->json($postalCode);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:4|unique:postal_codes,code',
            'city_name' => 'required|string',
            'county_name' => 'required|string',
        ]);

        $county = County::firstOrCreate(['name' => $request->county_name]);
        
        $city = City::firstOrCreate([
            'name' => $request->city_name,
            'county_id' => $county->id,
        ]);

        $postalCode = PostalCode::create([
            'code' => $request->code,
            'city_id' => $city->id,
        ]);

        $postalCode->load('city.county');

        return response()->json($postalCode, 201);
    }

    public function update(Request $request, PostalCode $postalCode)
    {
        $request->validate([
            'code' => 'sometimes|string|size:4|unique:postal_codes,code,' . $postalCode->id,
            'city_name' => 'sometimes|string',
            'county_name' => 'sometimes|string',
        ]);

        if ($request->has('county_name') || $request->has('city_name')) {
            $countyName = $request->get('county_name', $postalCode->city->county->name);
            $cityName = $request->get('city_name', $postalCode->city->name);
            
            $county = County::firstOrCreate(['name' => $countyName]);
            $city = City::firstOrCreate([
                'name' => $cityName,
                'county_id' => $county->id,
            ]);
            
            $postalCode->city_id = $city->id;
        }

        if ($request->has('code')) {
            $postalCode->code = $request->code;
        }

        $postalCode->save();
        $postalCode->load('city.county');

        return response()->json($postalCode);
    }

    public function destroy(PostalCode $postalCode)
    {
        $postalCode->delete();

        return response()->json([
            'message' => 'Postal code deleted successfully'
        ], 200);
    }
}