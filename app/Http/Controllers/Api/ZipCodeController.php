<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ZipCode;
use Illuminate\Http\Request;

class ZipCodeController extends Controller
{
    /**
     * @api {get} /api/zip-codes Get all zip codes
     * @apiName GetZipCodes
     * @apiGroup ZipCode
     * @apiVersion 1.0.0
     * @apiDescription Retrieve a list of all zip codes with settlement and county information
     *
     * @apiSuccess {Object[]} zipcodes Array of zip code objects
     * @apiSuccess {Number} zipcodes.id Zip code unique ID
     * @apiSuccess {String} zipcodes.code Zip code (max 10 characters)
     * @apiSuccess {Number} zipcodes.settlement_id Settlement ID
     * @apiSuccess {Number} zipcodes.county_id County ID
     * @apiSuccess {Object} zipcodes.settlement Settlement information
     * @apiSuccess {Number} zipcodes.settlement.id Settlement ID
     * @apiSuccess {String} zipcodes.settlement.name Settlement name
     * @apiSuccess {Object} zipcodes.county County information
     * @apiSuccess {Number} zipcodes.county.id County ID
     * @apiSuccess {String} zipcodes.county.name County name
     * @apiSuccess {String} zipcodes.created_at Creation timestamp
     * @apiSuccess {String} zipcodes.updated_at Last update timestamp
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     [
     *       {
     *         "id": 1,
     *         "code": "1011",
     *         "settlement_id": 1,
     *         "county_id": 1,
     *         "settlement": {
     *           "id": 1,
     *           "name": "Budapest",
     *           "county_id": 1
     *         },
     *         "county": {
     *           "id": 1,
     *           "name": "Pest"
     *         },
     *         "created_at": "2024-01-01T10:00:00.000000Z",
     *         "updated_at": "2024-01-01T10:00:00.000000Z"
     *       }
     *     ]
     *
     * @apiExample {curl} Example usage:
     *     curl -i http://localhost:8000/api/zip-codes
     */
    public function index()
    {
        return ZipCode::with(['settlement', 'county'])->get();
    }

    /**
     * @api {post} /api/zip-codes Create new zip code
     * @apiName CreateZipCode
     * @apiGroup ZipCode
     * @apiVersion 1.0.0
     * @apiDescription Create a new zip code record
     * @apiPermission authenticated
     *
     * @apiHeader {String} Authorization Bearer token for authentication
     * @apiHeaderExample {json} Header-Example:
     *     {
     *       "Authorization": "Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
     *     }
     *
     * @apiBody {String} code Zip code (required, max 10 characters)
     * @apiBody {Number} settlement_id Settlement ID (required, must exist in settlements table)
     * @apiBody {Number} county_id County ID (required, must exist in counties table)
     *
     * @apiSuccess {Number} id Created zip code ID
     * @apiSuccess {String} code Zip code
     * @apiSuccess {Number} settlement_id Settlement ID
     * @apiSuccess {Number} county_id County ID
     * @apiSuccess {Object} settlement Settlement information
     * @apiSuccess {Object} county County information
     * @apiSuccess {String} created_at Creation timestamp
     * @apiSuccess {String} updated_at Last update timestamp
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 201 Created
     *     {
     *       "id": 3,
     *       "code": "6720",
     *       "settlement_id": 3,
     *       "county_id": 3,
     *       "settlement": {
     *         "id": 3,
     *         "name": "Szeged",
     *         "county_id": 3
     *       },
     *       "county": {
     *         "id": 3,
     *         "name": "Csongrád-Csanád"
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
     *     curl -X POST http://localhost:8000/api/zip-codes \
     *       -H "Authorization: Bearer YOUR_TOKEN" \
     *       -H "Content-Type: application/json" \
     *       -d '{"code":"6720","settlement_id":3,"county_id":3}'
     */
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

    /**
     * @api {get} /api/zip-codes/:id Get zip code by ID
     * @apiName GetZipCode
     * @apiGroup ZipCode
     * @apiVersion 1.0.0
     * @apiDescription Retrieve a single zip code by its ID
     *
     * @apiParam {Number} id Zip code unique ID
     *
     * @apiSuccess {Number} id Zip code ID
     * @apiSuccess {String} code Zip code
     * @apiSuccess {Number} settlement_id Settlement ID
     * @apiSuccess {Number} county_id County ID
     * @apiSuccess {Object} settlement Settlement information
     * @apiSuccess {Object} county County information
     * @apiSuccess {String} created_at Creation timestamp
     * @apiSuccess {String} updated_at Last update timestamp
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "id": 1,
     *       "code": "1011",
     *       "settlement_id": 1,
     *       "county_id": 1,
     *       "settlement": {
     *         "id": 1,
     *         "name": "Budapest",
     *         "county_id": 1
     *       },
     *       "county": {
     *         "id": 1,
     *         "name": "Pest"
     *       },
     *       "created_at": "2024-01-01T10:00:00.000000Z",
     *       "updated_at": "2024-01-01T10:00:00.000000Z"
     *     }
     *
     * @apiError (404) {String} message Zip code not found
     * @apiErrorExample {json} Not Found:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "message": "No query results for model [App\\Models\\ZipCode]."
     *     }
     *
     * @apiExample {curl} Example usage:
     *     curl -i http://localhost:8000/api/zip-codes/1
     */
    public function show(ZipCode $zipCode)
    {
        return $zipCode->load(['settlement', 'county']);
    }

    /**
     * @api {put} /api/zip-codes/:id Update zip code
     * @apiName UpdateZipCode
     * @apiGroup ZipCode
     * @apiVersion 1.0.0
     * @apiDescription Update an existing zip code
     * @apiPermission authenticated
     *
     * @apiHeader {String} Authorization Bearer token for authentication
     * @apiHeaderExample {json} Header-Example:
     *     {
     *       "Authorization": "Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
     *     }
     *
     * @apiParam {Number} id Zip code unique ID
     * @apiBody {String} code Zip code (required, max 10 characters)
     * @apiBody {Number} settlement_id Settlement ID (required, must exist in settlements table)
     * @apiBody {Number} county_id County ID (required, must exist in counties table)
     *
     * @apiSuccess {Number} id Zip code ID
     * @apiSuccess {String} code Zip code
     * @apiSuccess {Number} settlement_id Settlement ID
     * @apiSuccess {Number} county_id County ID
     * @apiSuccess {Object} settlement Settlement information
     * @apiSuccess {Object} county County information
     * @apiSuccess {String} created_at Creation timestamp
     * @apiSuccess {String} updated_at Last update timestamp
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "id": 1,
     *       "code": "1012",
     *       "settlement_id": 1,
     *       "county_id": 1,
     *       "settlement": {
     *         "id": 1,
     *         "name": "Budapest",
     *         "county_id": 1
     *       },
     *       "county": {
     *         "id": 1,
     *         "name": "Pest"
     *       },
     *       "created_at": "2024-01-01T10:00:00.000000Z",
     *       "updated_at": "2024-01-15T16:45:00.000000Z"
     *     }
     *
     * @apiError (404) {String} message Zip code not found
     * @apiError (422) {Object} errors Validation errors
     * @apiError (401) {String} message Unauthorized
     *
     * @apiExample {curl} Example usage:
     *     curl -X PUT http://localhost:8000/api/zip-codes/1 \
     *       -H "Authorization: Bearer YOUR_TOKEN" \
     *       -H "Content-Type: application/json" \
     *       -d '{"code":"1012","settlement_id":1,"county_id":1}'
     */
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

    /**
     * @api {delete} /api/zip-codes/:id Delete zip code
     * @apiName DeleteZipCode
     * @apiGroup ZipCode
     * @apiVersion 1.0.0
     * @apiDescription Delete a zip code by its ID
     * @apiPermission authenticated
     *
     * @apiHeader {String} Authorization Bearer token for authentication
     * @apiHeaderExample {json} Header-Example:
     *     {
     *       "Authorization": "Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
     *     }
     *
     * @apiParam {Number} id Zip code unique ID
     *
     * @apiSuccess (204) NoContent Zip code successfully deleted (no content returned)
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 204 No Content
     *
     * @apiError (404) {String} message Zip code not found
     * @apiError (401) {String} message Unauthorized
     *
     * @apiExample {curl} Example usage:
     *     curl -X DELETE http://localhost:8000/api/zip-codes/1 \
     *       -H "Authorization: Bearer YOUR_TOKEN"
     */
    public function destroy(ZipCode $zipCode)
    {
        $zipCode->delete();
        return response()->json(null, 204);
    }
}