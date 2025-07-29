<?php

namespace App\Http\Controllers;
use OpenApi\Attributes as OA;


#[OA\OpenApi(
    info: new OA\Info(
        version: '1.0.0',
        title: 'Salary Backend API',
        description: 'API Endpoints for Custom Salary View project',
        contact: new OA\Contact(email: 'support@salarybackend.com'),
        license: new OA\License(name: 'MIT', url: 'https://opensource.org/license/mit/')
    ),
    servers: [
        new OA\Server(url: 'http://localhost:8000/api/', description: 'Local server'),
    ],
)]

#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
#[OA\Schema(
    schema: 'User',
    title: 'User Resource',
    description: 'Represents an admin user in the system',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'user_id', type: 'integer', example: 123),
        new OA\Property(property: 'action', type: 'string', example: 'login'),
        new OA\Property(property: 'description', type: 'string', example: 'User logged in successfully'),
        new OA\Property(property: 'properties', type: 'object', example: ['key' => 'value']),
        new OA\Property(property: 'logged_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'ip_address', type: 'string', format: 'ipv4'),
        new OA\Property(property: 'user_agent', type: 'string'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time')
    ]
)]

#[OA\Schema(
    schema: 'ApiResponse',
    properties: [
        new OA\Property(property: 'status', type: 'boolean', example: true),
        new OA\Property(property: 'status_code', type: 'integer', example: 200),
        new OA\Property(property: 'message', type: 'string', example: 'Operation successful'),
        new OA\Property(
            property: 'data',
            type: 'object',
            example: ['key' => 'value']
        ),
        new OA\Property(
            property: 'errors',
            type: 'object',
            nullable: true,
            example: null
        )
    ]
)]
#[OA\Schema(
    schema: 'ApiErrorResponse',
    properties: [
        new OA\Property(property: 'status', type: 'boolean', example: false),
        new OA\Property(property: 'status_code', type: 'integer', example: 400),
        new OA\Property(property: 'message', type: 'string', example: 'Operation failed'),
        new OA\Property(
            property: 'errors',
            type: 'object',
            example: ['key' => ['error message']]
        )
    ]
)]
abstract class Controller
{
    //
}
