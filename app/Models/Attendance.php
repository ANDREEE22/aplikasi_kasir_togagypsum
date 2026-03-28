<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendances';
    
    protected $fillable = [
        'user_id',
        'employee_id',
        'attendance_date',
        'is_present',
        'project_id',
        'notes',
    ];

    protected $casts = [
        'attendance_date' => 'date:Y-m-d',
        'is_present' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    // Relasi ke Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    // Relasi ke Project
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}