<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

class UserSalary extends Model
{
    //
    use HasFactory;
    #[OA\Schema(
        schema: 'UserSalary',
        title: 'User Salary Submission',
        description: 'Schema for publicly submitted salary data',
        properties: [
            new OA\Property(property: 'id', type: 'integer', format: 'int64', example: 1),
            new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
            new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john.doe@example.com'),
            new OA\Property(property: 'salary_local_currency', type: 'number', format: 'float', example: 75000.00),
            new OA\Property(property: 'salary_euros', type: 'number', format: 'float', example: 18000.00),
            new OA\Property(property: 'commission', type: 'number', format: 'float', example: 500.00),
            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
            new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        ]
    )]

    protected $fillable = [
        'name',
        'email',
        'salary_local_currency',
        'salary_euros',
        'commission',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_salaries';

    // You can add accessor for displayed_salary here if you want it computed by the model
    public function getDisplayedSalaryAttribute()
    {
        // Ensure commission has a default if not set
        $commission = $this->commission ?? 500.00; // Default commission for calculation
        return ($this->salary_euros ?? 0) + $commission;
    }

}
