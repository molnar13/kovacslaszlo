<?php

namespace App\Http\Controllers;

use App\Models\County;
use Illuminate\Http\Request;

/**
 * @apiDefine CountySuccess
 * @apiSuccess {Number} id County ID
 * @apiSuccess {String} name County name
 * @apiSuccess {String} created_at Creation timestamp
 * @apiSuccess {String} updated_at Last update timestamp
 */

/**
 * @apiDefine CountyNotFound
 * @apiError (404) {String} message Error message
 * @apiErrorExample {json} Not Found:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "message": "Not found!"
 *     }
 */

/**
 * @apiDefine BearerAuth
 * @apiHeader {String} Authorization Bearer token for authentication
 * @apiHeaderExample {json} Header-Example:
 *     {
 *       "Authorization": "Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
 *     }
 */

class CountyController extends Controller
{
    /**
     * @api {get} /api/counties Get all counties
     * @apiName GetCounties
     * @apiGroup County
     * @apiVersion 1.0.0
     * @apiDescription Retrieve a list of all Hungarian counties with optional filtering
     *
     * @apiParam {String} [needle] Optional search term to filter counties by name (case-insensitive)
     *
     * @apiSuccess {Object[]} counties Array of county objects
     * @apiSuccess {Number} counties.id County unique ID
     * @apiSuccess {String} counties.name County name
     * @apiSuccess {String} counties.created_at Creation timestamp
     * @apiSuccess {String} counties.updated_at Last update timestamp
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     [
     *       {
     *         "id": 1,
     *         "name": "Pest",
     *         "created_at": "2024-01-01T10:00:00.000000Z",
     *         "updated_at": "2024-01-01T10:00:00.000000Z"
     *       },
     *       {
     *         "id": 2,
     *         "name": "Baranya",
     *         "created_at": "2024-01-01T10:00:00.000000Z",
     *         "updated_at": "2024-01-01T10:00:00.000000Z"
     *       }
     *     ]
     *
     * @apiExample {curl} Example usage:
     *     curl -i http://localhost:8000/api/counties
     *
     * @apiExample {curl} Example with filter:
     *     curl -i http://localhost:8000/api/counties?needle=pest
     */
    public function index(Request $request)
    {
        $query = County::query();
        
        if ($request->has('needle')) {
            $query->where('name', 'like', '%' . $request->needle . '%');
        }
        
        return response()->json($query->get());
    }

    /**
     * @api {post} /api/counties Create new county
     * @apiName CreateCounty
     * @apiGroup County
     * @apiVersion 1.0.0
     * @apiDescription Create a new county record
     * @apiPermission authenticated
     *
     * @apiUse BearerAuth
     *
     * @apiBody {String} name County name (required, unique, max 255 characters)
     *
     * @apiSuccess {Number} id Created county ID
     * @apiSuccess {String} name County name
     * @apiSuccess {String} created_at Creation timestamp
     * @apiSuccess {String} updated_at Last update timestamp
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 201 Created
     *     {
     *       "id": 3,
     *       "name": "Somogy",
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
     *         "name": ["The name field is required."]
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
     *     curl -X POST http://localhost:8000/api/counties \
     *       -H "Authorization: Bearer YOUR_TOKEN" \
     *       -H "Content-Type: application/json" \
     *       -d '{"name":"Somogy"}'
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:counties,name'
        ]);
        
        $county = County::create($validated);
        return response()->json($county, 201);
    }

    /**
     * @api {get} /api/counties/:id Get county by ID
     * @apiName GetCounty
     * @apiGroup County
     * @apiVersion 1.0.0
     * @apiDescription Retrieve a single county by its ID
     *
     * @apiParam {Number} id County unique ID
     *
     * @apiUse CountySuccess
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "id": 1,
     *       "name": "Pest",
     *       "created_at": "2024-01-01T10:00:00.000000Z",
     *       "updated_at": "2024-01-01T10:00:00.000000Z"
     *     }
     *
     * @apiUse CountyNotFound
     *
     * @apiExample {curl} Example usage:
     *     curl -i http://localhost:8000/api/counties/1
     */
    public function show($id)
    {
        $county = County::find($id);
        
        if (!$county) {
            return response()->json(['message' => 'Not found!'], 404);
        }
        
        return response()->json($county);
    }

    /**
     * @api {put} /api/counties/:id Update county
     * @apiName UpdateCounty
     * @apiGroup County
     * @apiVersion 1.0.0
     * @apiDescription Update an existing county
     * @apiPermission authenticated
     *
     * @apiUse BearerAuth
     *
     * @apiParam {Number} id County unique ID
     * @apiBody {String} name New county name (required, unique, max 255 characters)
     *
     * @apiUse CountySuccess
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "id": 1,
     *       "name": "Nógrád",
     *       "created_at": "2024-01-01T10:00:00.000000Z",
     *       "updated_at": "2024-01-15T16:45:00.000000Z"
     *     }
     *
     * @apiUse CountyNotFound
     *
     * @apiError (422) {Object} errors Validation errors
     * @apiErrorExample {json} Validation Error:
     *     HTTP/1.1 422 Unprocessable Entity
     *     {
     *       "message": "The name has already been taken.",
     *       "errors": {
     *         "name": ["The name has already been taken."]
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
     *     curl -X PUT http://localhost:8000/api/counties/1 \
     *       -H "Authorization: Bearer YOUR_TOKEN" \
     *       -H "Content-Type: application/json" \
     *       -d '{"name":"Nógrád"}'
     */
    public function update(Request $request, $id)
    {
        $county = County::find($id);
        
        if (!$county) {
            return response()->json(['message' => 'Not found!'], 404);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:counties,name,' . $id
        ]);
        
        $county->update($validated);
        return response()->json($county);
    }

    /**
     * @api {delete} /api/counties/:id Delete county
     * @apiName DeleteCounty
     * @apiGroup County
     * @apiVersion 1.0.0
     * @apiDescription Delete a county by its ID
     * @apiPermission authenticated
     *
     * @apiUse BearerAuth
     *
     * @apiParam {Number} id County unique ID
     *
     * @apiSuccess {String} message Success message
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 410 Gone
     *     {
     *       "message": "Deleted"
     *     }
     *
     * @apiUse CountyNotFound
     *
     * @apiError (401) {String} message Unauthorized
     * @apiErrorExample {json} Unauthorized:
     *     HTTP/1.1 401 Unauthorized
     *     {
     *       "message": "Unauthenticated."
     *     }
     *
     * @apiExample {curl} Example usage:
     *     curl -X DELETE http://localhost:8000/api/counties/1 \
     *       -H "Authorization: Bearer YOUR_TOKEN"
     */
    public function destroy($id)
    {
        $county = County::find($id);
        
        if (!$county) {
            return response()->json(['message' => 'Not found!'], 404);
        }
        
        $county->delete();
        return response()->json(['message' => 'Deleted'], 410);
    }
}