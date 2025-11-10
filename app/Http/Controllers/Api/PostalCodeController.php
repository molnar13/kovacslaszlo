<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PostalCode;
use App\Models\City;
use App\Models\County;
use Illuminate\Http\Request;

class PostalCodeController extends Controller
{
    /**
     * @api {get} /api/postal-codes Get all postal codes
     * @apiName GetPostalCodes
     * @apiGroup PostalCode
     * @apiVersion 1.0.0
     * @apiDescription Retrieve a paginated list of all Hungarian postal codes with optional filtering
     *
     * @apiParam {String} [county] Optional filter by county name (partial match, case-insensitive)
     * @apiParam {String} [city] Optional filter by city name (partial match, case-insensitive)
     * @apiParam {Number} [per_page=15] Number of results per page (default: 15)
     *
     * @apiSuccess {Number} current_page Current page number
     * @apiSuccess {Object[]} data Array of postal code objects
     * @apiSuccess {Number} data.id Postal code unique ID
     * @apiSuccess {String} data.code 4-digit postal code
     * @apiSuccess {Number} data.city_id City ID
     * @apiSuccess {Object} data.city City information
     * @apiSuccess {Number} data.city.id City ID
     * @apiSuccess {String} data.city.name City name
     * @apiSuccess {Number} data.city.county_id County ID
     * @apiSuccess {Object} data.city.county County information
     * @apiSuccess {Number} data.city.county.id County ID
     * @apiSuccess {String} data.city.county.name County name
     * @apiSuccess {String} data.created_at Creation timestamp
     * @apiSuccess {String} data.updated_at Last update timestamp
     * @apiSuccess {String} first_page_url First page URL
     * @apiSuccess {Number} from Starting record number
     * @apiSuccess {Number} last_page Last page number
     * @apiSuccess {String} last_page_url Last page URL
     * @apiSuccess {String} next_page_url Next page URL
     * @apiSuccess {String} path Base URL path
     * @apiSuccess {Number} per_page Records per page
     * @apiSuccess {String} prev_page_url Previous page URL
     * @apiSuccess {Number} to Ending record number
     * @apiSuccess {Number} total Total number of records
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "current_page": 1,
     *       "data": [
     *         {
     *           "id": 1,
     *           "code": "1011",
     *           "city_id": 1,
     *           "city": {
     *             "id": 1,
     *             "name": "Budapest",
     *             "county_id": 1,
     *             "county": {
     *               "id": 1,
     *               "name": "Pest"
     *             }
     *           },
     *           "created_at": "2024-01-01T10:00:00.000000Z",
     *           "updated_at": "2024-01-01T10:00:00.000000Z"
     *         }
     *       ],
     *       "first_page_url": "http://localhost:8000/api/postal-codes?page=1",
     *       "from": 1,
     *       "last_page": 10,
     *       "last_page_url": "http://localhost:8000/api/postal-codes?page=10",
     *       "next_page_url": "http://localhost:8000/api/postal-codes?page=2",
     *       "path": "http://localhost:8000/api/postal-codes",
     *       "per_page": 15,
     *       "prev_page_url": null,
     *       "to": 15,
     *       "total": 150
     *     }
     *
     * @apiExample {curl} Example usage:
     *     curl -i http://localhost:8000/api/postal-codes
     *
     * @apiExample {curl} Example with county filter:
     *     curl -i http://localhost:8000/api/postal-codes?county=pest
     *
     * @apiExample {curl} Example with city filter:
     *     curl -i http://localhost:8000/api/postal-codes?city=budapest
     *
     * @apiExample {curl} Example with pagination:
     *     curl -i http://localhost:8000/api/postal-codes?per_page=20
     */
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

    /**
     * @api {get} /api/postal-codes/:id Get postal code by ID
     * @apiName GetPostalCode
     * @apiGroup PostalCode
     * @apiVersion 1.0.0
     * @apiDescription Retrieve a single postal code by its ID
     *
     * @apiParam {Number} id Postal code unique ID
     *
     * @apiSuccess {Number} id Postal code ID
     * @apiSuccess {String} code 4-digit postal code
     * @apiSuccess {Number} city_id City ID
     * @apiSuccess {Object} city City information
     * @apiSuccess {Number} city.id City ID
     * @apiSuccess {String} city.name City name
     * @apiSuccess {Number} city.county_id County ID
     * @apiSuccess {Object} city.county County information
     * @apiSuccess {Number} city.county.id County ID
     * @apiSuccess {String} city.county.name County name
     * @apiSuccess {String} created_at Creation timestamp
     * @apiSuccess {String} updated_at Last update timestamp
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "id": 1,
     *       "code": "1011",
     *       "city_id": 1,
     *       "city": {
     *         "id": 1,
     *         "name": "Budapest",
     *         "county_id": 1,
     *         "county": {
     *           "id": 1,
     *           "name": "Pest"
     *         }
     *       },
     *       "created_at": "2024-01-01T10:00:00.000000Z",
     *       "updated_at": "2024-01-01T10:00:00.000000Z"
     *     }
     *
     * @apiError (404) {String} message Postal code not found
     * @apiErrorExample {json} Not Found:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "message": "No query results for model [App\\Models\\PostalCode]."
     *     }
     *
     * @apiExample {curl} Example usage:
     *     curl -i http://localhost:8000/api/postal-codes/1
     */
    public function show(PostalCode $postalCode)
    {
        $postalCode->load('city.county');
        
        return response()->json($postalCode);
    }

    /**
     * @api {get} /api/postal-codes/search/:code Search by postal code
     * @apiName SearchPostalCode
     * @apiGroup PostalCode
     * @apiVersion 1.0.0
     * @apiDescription Search for a postal code by its 4-digit code
     *
     * @apiParam {String} code 4-digit postal code to search for
     *
     * @apiSuccess {Number} id Postal code ID
     * @apiSuccess {String} code 4-digit postal code
     * @apiSuccess {Number} city_id City ID
     * @apiSuccess {Object} city City information
     * @apiSuccess {Number} city.id City ID
     * @apiSuccess {String} city.name City name
     * @apiSuccess {Number} city.county_id County ID
     * @apiSuccess {Object} city.county County information
     * @apiSuccess {String} created_at Creation timestamp
     * @apiSuccess {String} updated_at Last update timestamp
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "id": 1,
     *       "code": "1011",
     *       "city_id": 1,
     *       "city": {
     *         "id": 1,
     *         "name": "Budapest",
     *         "county_id": 1,
     *         "county": {
     *           "id": 1,
     *           "name": "Pest"
     *         }
     *       },
     *       "created_at": "2024-01-01T10:00:00.000000Z",
     *       "updated_at": "2024-01-01T10:00:00.000000Z"
     *     }
     *
     * @apiError (404) {String} message Postal code not found
     * @apiErrorExample {json} Not Found:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "message": "No query results for model [App\\Models\\PostalCode]."
     *     }
     *
     * @apiExample {curl} Example usage:
     *     curl -i http://localhost:8000/api/postal-codes/search/1011
     */
    public function searchByCode($code)
    {
        $postalCode = PostalCode::with('city.county')
            ->where('code', $code)
            ->firstOrFail();
        
        return response()->json($postalCode);
    }

    /**
     * @api {post} /api/postal-codes Create new postal code
     * @apiName CreatePostalCode
     * @apiGroup PostalCode
     * @apiVersion 1.0.0
     * @apiDescription Create a new postal code record (automatically creates city and county if needed)
     * @apiPermission authenticated
     *
     * @apiHeader {String} Authorization Bearer token for authentication
     * @apiHeaderExample {json} Header-Example:
     *     {
     *       "Authorization": "Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
     *     }
     *
     * @apiBody {String} code 4-digit postal code (required, unique, exactly 4 characters)
     * @apiBody {String} city_name City name (required)
     * @apiBody {String} county_name County name (required)
     *
     * @apiSuccess {Number} id Created postal code ID
     * @apiSuccess {String} code 4-digit postal code
     * @apiSuccess {Number} city_id City ID
     * @apiSuccess {Object} city City information
     * @apiSuccess {String} created_at Creation timestamp
     * @apiSuccess {String} updated_at Last update timestamp
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 201 Created
     *     {
     *       "id": 3,
     *       "code": "6720",
     *       "city_id": 3,
     *       "city": {
     *         "id": 3,
     *         "name": "Szeged",
     *         "county_id": 5,
     *         "county": {
     *           "id": 5,
     *           "name": "Csongrád-Csanád"
     *         }
     *       },
     *       "created_at": "2024-01-15T14:30:00.000000Z",
     *       "updated_at": "2024-01-15T14:30:00.000000Z"
     *     }
     *
     * @apiError (422) {Object} errors Validation errors
     * @apiErrorExample {json} Validation Error:
     *     HTTP/1.1 422 Unprocessable Entity
     *     {
     *       "message": "The code field is required.",
     *       "errors": {
     *         "code": ["The code field is required."]
     *       }
     *     }
     *
     * @apiError (401) {String} message Unauthorized
     * @apiErrorExample {json} Unauthorized:
     *     HTTP/1.1 401 Unauthorized
     *     {
     *       "message": "Unauthenticated."
     *     }
     *
     * @apiExample {curl} Example usage:
     *     curl -X POST http://localhost:8000/api/postal-codes \
     *       -H "Authorization: Bearer YOUR_TOKEN" \
     *       -H "Content-Type: application/json" \
     *       -d '{
     *         "code": "6720",
     *         "city_name": "Szeged",
     *         "county_name": "Csongrád-Csanád"
     *       }'
     */
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

    /**
     * @api {put} /api/postal-codes/:id Update postal code
     * @apiName UpdatePostalCode
     * @apiGroup PostalCode
     * @apiVersion 1.0.0
     * @apiDescription Update an existing postal code record
     * @apiPermission authenticated
     *
     * @apiHeader {String} Authorization Bearer token for authentication
     * @apiHeaderExample {json} Header-Example:
     *     {
     *       "Authorization": "Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
     *     }
     *
     * @apiParam {Number} id Postal code unique ID
     * @apiBody {String} [code] 4-digit postal code (optional, unique, exactly 4 characters)
     * @apiBody {String} [city_name] City name (optional)
     * @apiBody {String} [county_name] County name (optional)
     *
     * @apiSuccess {Number} id Postal code ID
     * @apiSuccess {String} code 4-digit postal code
     * @apiSuccess {Number} city_id City ID
     * @apiSuccess {Object} city City information
     * @apiSuccess {String} created_at Creation timestamp
     * @apiSuccess {String} updated_at Last update timestamp
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "id": 1,
     *       "code": "1012",
     *       "city_id": 1,
     *       "city": {
     *         "id": 1,
     *         "name": "Budapest",
     *         "county_id": 1,
     *         "county": {
     *           "id": 1,
     *           "name": "Pest"
     *         }
     *       },
     *       "created_at": "2024-01-01T10:00:00.000000Z",
     *       "updated_at": "2024-01-15T16:45:00.000000Z"
     *     }
     *
     * @apiError (404) {String} message Postal code not found
     * @apiError (422) {Object} errors Validation errors
     * @apiError (401) {String} message Unauthorized
     *
     * @apiExample {curl} Example usage:
     *     curl -X PUT http://localhost:8000/api/postal-codes/1 \
     *       -H "Authorization: Bearer YOUR_TOKEN" \
     *       -H "Content-Type: application/json" \
     *       -d '{"code":"1012"}'
     */
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

    /**
     * @api {delete} /api/postal-codes/:id Delete postal code
     * @apiName DeletePostalCode
     * @apiGroup PostalCode
     * @apiVersion 1.0.0
     * @apiDescription Delete a postal code by its ID
     * @apiPermission authenticated
     *
     * @apiHeader {String} Authorization Bearer token for authentication
     * @apiHeaderExample {json} Header-Example:
     *     {
     *       "Authorization": "Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
     *     }
     *
     * @apiParam {Number} id Postal code unique ID
     *
     * @apiSuccess {String} message Success message
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "message": "Postal code deleted successfully"
     *     }
     *
     * @apiError (404) {String} message Postal code not found
     * @apiError (401) {String} message Unauthorized
     *
     * @apiExample {curl} Example usage:
     *     curl -X DELETE http://localhost:8000/api/postal-codes/1 \
     *       -H "Authorization: Bearer YOUR_TOKEN"
     */
    public function destroy(PostalCode $postalCode)
    {
        $postalCode->delete();

        return response()->json([
            'message' => 'Postal code deleted successfully'
        ], 200);
    }
}