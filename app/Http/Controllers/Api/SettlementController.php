<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Settlement;
use Illuminate\Http\Request;

class SettlementController extends Controller
{
    /**
     * @api {get} /api/settlements Get all settlements
     * @apiName GetSettlements
     * @apiGroup Settlement
     * @apiVersion 1.0.0
     * @apiDescription Retrieve a list of all settlements (cities/villages) with their county information
     *
     * @apiSuccess {Object[]} settlements Array of settlement objects
     * @apiSuccess {Number} settlements.id Settlement unique ID
     * @apiSuccess {String} settlements.name Settlement name
     * @apiSuccess {Number} settlements.county_id County ID
     * @apiSuccess {Object} settlements.county County information
     * @apiSuccess {Number} settlements.county.id County ID
     * @apiSuccess {String} settlements.county.name County name
     * @apiSuccess {String} settlements.created_at Creation timestamp
     * @apiSuccess {String} settlements.updated_at Last update timestamp
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     [
     *       {
     *         "id": 1,
     *         "name": "Budapest",
     *         "county_id": 1,
     *         "county": {
     *           "id": 1,
     *           "name": "Pest"
     *         },
     *         "created_at": "2024-01-01T10:00:00.000000Z",
     *         "updated_at": "2024-01-01T10:00:00.000000Z"
     *       },
     *       {
     *         "id": 2,
     *         "name": "Debrecen",
     *         "county_id": 2,
     *         "county": {
     *           "id": 2,
     *           "name": "Hajdú-Bihar"
     *         },
     *         "created_at": "2024-01-01T10:00:00.000000Z",
     *         "updated_at": "2024-01-01T10:00:00.000000Z"
     *       }
     *     ]
     *
     * @apiExample {curl} Example usage:
     *     curl -i http://localhost:8000/api/settlements
     */
    public function index()
    {
        return Settlement::with('county')->get();
    }

    /**
     * @api {post} /api/settlements Create new settlement
     * @apiName CreateSettlement
     * @apiGroup Settlement
     * @apiVersion 1.0.0
     * @apiDescription Create a new settlement record
     * @apiPermission authenticated
     *
     * @apiHeader {String} Authorization Bearer token for authentication
     * @apiHeaderExample {json} Header-Example:
     *     {
     *       "Authorization": "Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
     *     }
     *
     * @apiBody {String} name Settlement name (required, max 255 characters)
     * @apiBody {Number} county_id County ID (required, must exist in counties table)
     *
     * @apiSuccess {Number} id Created settlement ID
     * @apiSuccess {String} name Settlement name
     * @apiSuccess {Number} county_id County ID
     * @apiSuccess {Object} county County information
     * @apiSuccess {Number} county.id County ID
     * @apiSuccess {String} county.name County name
     * @apiSuccess {String} created_at Creation timestamp
     * @apiSuccess {String} updated_at Last update timestamp
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 201 Created
     *     {
     *       "id": 3,
     *       "name": "Szeged",
     *       "county_id": 3,
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
     *       "message": "The name field is required.",
     *       "errors": {
     *         "name": ["The name field is required."],
     *         "county_id": ["The county id field is required."]
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
     *     curl -X POST http://localhost:8000/api/settlements \
     *       -H "Authorization: Bearer YOUR_TOKEN" \
     *       -H "Content-Type: application/json" \
     *       -d '{"name":"Szeged","county_id":3}'
     */
    public function store(Request $request)
    {
        // 1. Validálás (postal_code kötelező!)
        $validated = $request->validate([
            'name' => 'required|string',
            'county_id' => 'required|exists:counties,id',
            'postal_code' => 'required', // <--- Ez a sor kritikus!
        ]);

        // 2. Létrehozás a validált adatokból
        $settlement = Settlement::create($validated);

        return response()->json($settlement, 201);
    }

    /**
     * @api {get} /api/settlements/:id Get settlement by ID
     * @apiName GetSettlement
     * @apiGroup Settlement
     * @apiVersion 1.0.0
     * @apiDescription Retrieve a single settlement by its ID
     *
     * @apiParam {Number} id Settlement unique ID
     *
     * @apiSuccess {Number} id Settlement ID
     * @apiSuccess {String} name Settlement name
     * @apiSuccess {Number} county_id County ID
     * @apiSuccess {Object} county County information
     * @apiSuccess {Number} county.id County ID
     * @apiSuccess {String} county.name County name
     * @apiSuccess {String} created_at Creation timestamp
     * @apiSuccess {String} updated_at Last update timestamp
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "id": 1,
     *       "name": "Budapest",
     *       "county_id": 1,
     *       "county": {
     *         "id": 1,
     *         "name": "Pest"
     *       },
     *       "created_at": "2024-01-01T10:00:00.000000Z",
     *       "updated_at": "2024-01-01T10:00:00.000000Z"
     *     }
     *
     * @apiError (404) {String} message Settlement not found
     * @apiErrorExample {json} Not Found:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "message": "No query results for model [App\\Models\\Settlement]."
     *     }
     *
     * @apiExample {curl} Example usage:
     *     curl -i http://localhost:8000/api/settlements/1
     */
    public function show(Settlement $settlement)
    {
        return $settlement->load('county');
    }

    /**
     * @api {put} /api/settlements/:id Update settlement
     * @apiName UpdateSettlement
     * @apiGroup Settlement
     * @apiVersion 1.0.0
     * @apiDescription Update an existing settlement
     * @apiPermission authenticated
     *
     * @apiHeader {String} Authorization Bearer token for authentication
     * @apiHeaderExample {json} Header-Example:
     *     {
     *       "Authorization": "Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
     *     }
     *
     * @apiParam {Number} id Settlement unique ID
     * @apiBody {String} name Settlement name (required, max 255 characters)
     * @apiBody {Number} county_id County ID (required, must exist in counties table)
     *
     * @apiSuccess {Number} id Settlement ID
     * @apiSuccess {String} name Settlement name
     * @apiSuccess {Number} county_id County ID
     * @apiSuccess {Object} county County information
     * @apiSuccess {String} created_at Creation timestamp
     * @apiSuccess {String} updated_at Last update timestamp
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "id": 1,
     *       "name": "Budapest",
     *       "county_id": 1,
     *       "county": {
     *         "id": 1,
     *         "name": "Pest"
     *       },
     *       "created_at": "2024-01-01T10:00:00.000000Z",
     *       "updated_at": "2024-01-15T16:45:00.000000Z"
     *     }
     *
     * @apiError (404) {String} message Settlement not found
     * @apiError (422) {Object} errors Validation errors
     * @apiError (401) {String} message Unauthorized
     *
     * @apiExample {curl} Example usage:
     *     curl -X PUT http://localhost:8000/api/settlements/1 \
     *       -H "Authorization: Bearer YOUR_TOKEN" \
     *       -H "Content-Type: application/json" \
     *       -d '{"name":"Budapest","county_id":1}'
     */
    public function update(Request $request, Settlement $settlement)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'county_id' => 'required|exists:counties,id',
        ]);

        $settlement->update($validated);
        return response()->json($settlement->load('county'));
    }

    /**
     * @api {delete} /api/settlements/:id Delete settlement
     * @apiName DeleteSettlement
     * @apiGroup Settlement
     * @apiVersion 1.0.0
     * @apiDescription Delete a settlement by its ID
     * @apiPermission authenticated
     *
     * @apiHeader {String} Authorization Bearer token for authentication
     * @apiHeaderExample {json} Header-Example:
     *     {
     *       "Authorization": "Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
     *     }
     *
     * @apiParam {Number} id Settlement unique ID
     *
     * @apiSuccess (204) NoContent Settlement successfully deleted (no content returned)
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 204 No Content
     *
     * @apiError (404) {String} message Settlement not found
     * @apiError (401) {String} message Unauthorized
     *
     * @apiExample {curl} Example usage:
     *     curl -X DELETE http://localhost:8000/api/settlements/1 \
     *       -H "Authorization: Bearer YOUR_TOKEN"
     */
    public function destroy(Settlement $settlement)
    {
        $settlement->delete();
        return response()->json(null, 204);
    }
}