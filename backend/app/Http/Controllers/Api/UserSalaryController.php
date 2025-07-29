<?php

namespace App\Http\Controllers\Api;

use App\Models\UserSalary;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;


class UserSalaryController extends Controller
{
    //
    #[OA\Post(
        path: '/salaries',
        tags: ['Salaries'],
        summary: 'Submit new salary data',
        description: 'Allows any user to submit their salary information. No authentication required.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'salary'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john.doe@example.com'),
                    new OA\Property(property: 'salary_local_currency', type: 'number', format: 'float', example: 500.00),
                ]
            )
        ),
    )]
    #[OA\Response(
        response: 200,
        description: 'Salary info submitted successfully',
        content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')
    )]
    #[OA\Response(
        response: 403,
        description: 'Forbidden access.',
        content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
    )]
    #[OA\Response(
        response: 422,
        description: 'Validation error.',
        content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
    )]
    #[OA\Response(
        response: 500,
        description: 'Internal server error.',
        content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
    )]
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3',
            'email' => 'required|string|email',
            'salary_local_currency' => 'sometimes|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'status_code' => 422,
                'message' => 'Validation error',
                'data' => null,
                'errors' => $validator->errors()
            ], 422);
        }
        $email = $request->get('email');
        $name = $request->get('name');
        $salary = $request->get('salary_local_currency', '500');
        $user_salary = UserSalary::where('email', $email)->first();
        if (!$user_salary) {
            $user_salary = UserSalary::create([
                'email' => $email,
                'name' => $name,
            ]);
        }
        $user_salary->update(['name' => $name, 'salary_local_currency' => $salary]);
        return response()->json([
            'status' => true,
            'status_code' => 201,
            'message' => 'Salary info submitted successfully.',
            'data' => $user_salary,
            'errors' => null
        ], 201);

    }

    #[OA\Get(
        path: '/salaries',
        tags: ['Salaries'],
        summary: 'Get all salary records',
        description: 'Retrieves a list of all salary records.',
        security: [['bearerAuth' => []]], // Requires JWT bearer token
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful retrieval of salary records.',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')
            )
        ]
    )]
    #[OA\Parameter(
        name: 'page',
        in: 'query',
        description: 'Page number for pagination',
        required: false,
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Parameter(
        name: 'limit',
        in: 'query',
        description: 'Number of records per page',
        required: false,
        schema: new OA\Schema(type: 'integer', example: 10)
    )]
    #[OA\Parameter(
        name: 'search',
        in: 'query',
        description: 'Search term for filtering salary records',
        required: false,
        schema: new OA\Schema(type: 'string', example: 'developer')
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized access.',
        content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
    )]
    #[OA\Response(
        response: 403,
        description: 'Forbidden access.',
        content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
    )]
    #[OA\Response(
        response: 422,
        description: 'Validation error.',
        content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
    )]
    #[OA\Response(
        response: 500,
        description: 'Internal server error.',
        content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
    )]
    public function index()
    {
        $validator = Validator::make(request()->all(), [
            'page' => 'sometimes|integer|min:1',
            'limit' => 'sometimes|integer|min:1|max:100',
            'search' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'status_code' => 422,
                'message' => 'Validation error',
                'data' => null,
                'error' => $validator->errors()
            ], 422);
        }

        // Set pagination values
        $page = request('page', 1);
        $limit = request('limit', 10);
        $search = request('search', '');

        $salaries = UserSalary::orderBy('created_at', 'desc')
            ->when($search, fn($query) => $query->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
            ->paginate($limit, ['*'], 'page', $page);
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Salaries retrieved successfully.',
            'data' => [
                'total' => $salaries->total(),
                'per_page' => $salaries->perPage(),
                'current_page' => $salaries->currentPage(),
                'last_page' => $salaries->lastPage(),
                'items' => $salaries->items(),
            ],
            'errors' => null
        ], 200);
    }

    #[OA\Get(
        path: '/salaries/{id}',
        tags: ['Salaries'],
        summary: 'Get a specific salary record',
        description: 'Retrieves a specific salary record by ID.',
        security: [['bearerAuth' => []]], // Requires JWT bearer token
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful retrieval of salary records.',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')
            )
        ]
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'ID of the salary record',
        required: true,
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized access.',
        content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
    )]
    #[OA\Response(
        response: 403,
        description: 'Forbidden access.',
        content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
    )]
    #[OA\Response(
        response: 500,
        description: 'Internal server error.',
        content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
    )]
    public function show($id)
    {
        $salary = UserSalary::find($id);
        if (!$salary) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'Salary record not found.',
                'data' => null,
                'errors' => null
            ], 404);
        }
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Salary record retrieved successfully.',
            'data' => $salary,
            'errors' => null
        ], 200);
    }

    #[OA\Delete(
        path: '/salaries/{id}',
        tags: ['Salaries'],
        summary: 'Delete a specific salary record',
        description: 'Deletes a specific salary record by ID.',
        security: [['bearerAuth' => []]], // Requires JWT bearer token
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful deletion of salary record.',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')
            )
        ]
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'ID of the salary record',
        required: true,
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized access.',
        content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
    )]
    #[OA\Response(
        response: 403,
        description: 'Forbidden access.',
        content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
    )]
    #[OA\Response(
        response: 500,
        description: 'Internal server error.',
        content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
    )]
    public function destroy($id)
    {
        $salary = UserSalary::find($id);
        if (!$salary) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'Salary record not found.',
                'data' => null,
                'errors' => null
            ], 404);
        }
        $salary->delete();
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Salary record deleted successfully.',
            'data' => null,
            'errors' => null
        ], 200);
    }

    #[OA\Put(
        path: '/salaries/{id}',
        tags: ['Salaries'],
        summary: 'Update a specific salary record',
        description: 'Updates a specific salary record by ID.',
        security: [['bearerAuth' => []]], // Requires JWT bearer token
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful update of salary record.',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')
            )
        ]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'salary_local_currency', type: 'number', format: 'float', example: 5000.00),
                new OA\Property(property: 'salary_euros', type: 'number', format: 'float', example: 4500.00),
                new OA\Property(property: 'commission', type: 'number', format: 'float', example: 500.00),
            ]
        )
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'ID of the salary record',
        required: true,
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized access.',
        content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
    )]
    #[OA\Response(
        response: 403,
        description: 'Forbidden access.',
        content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
    )]
    #[OA\Response(
        response: 500,
        description: 'Internal server error.',
        content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
    )]
    public function update(Request $request, $id){
        $salary = UserSalary::find($id);
        if (!$salary) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'Salary record not found.',
                'data' => null,
                'errors' => null
            ], 404);
        }
        
        $validator = Validator::make(request()->all(), [
            'salary_local_currency' => 'sometimes|numeric|min:0',
            'salary_euros' => 'sometimes|numeric|min:0',
            'commission' => 'sometimes|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'status_code' => 422,
                'message' => 'Validation error',
                'data' => null,
                'error' => $validator->errors()
            ], 422);
        }

        $salary->update($request->only([
            'salary_local_currency',
            'salary_euros',
            'commission'
        ]));
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Salary record updated successfully.',
            'data' => $salary,
            'errors' => null
        ], 200);
    }
}
