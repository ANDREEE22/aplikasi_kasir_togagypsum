<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // GET /api/attendances - Ambil daftar absen per bulan
    public function index(Request $request)
    {
        try {
            $month = $request->input('month');  // Format: 2026-03
            
            if (!$month) {
                return response()->json(['status' => 'error', 'message' => 'Month parameter required'], 422);
            }

            // Filter attendance berdasarkan bulan dan user
            $attendances = Attendance::where('user_id', $request->user()->id)
                                    ->whereRaw("DATE_FORMAT(attendance_date, '%Y-%m') = ?", [$month])
                                    ->orderBy('attendance_date', 'desc')
                                    ->with('employee', 'project')
                                    ->get();

            return response()->json([
                'status' => 'success',
                'data' => $attendances
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // POST /api/attendances - Simpan attendance baru
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'attendance_date' => 'required|date',
                'employees' => 'required|array',
                'employees.*.employee_id' => 'required|integer|exists:employees,id',
                'employees.*.is_present' => 'required|boolean',
                'project_id' => 'nullable|integer|exists:projects,id',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
            }

            // Hapus attendance lama untuk hari tersebut (jika ada)
            Attendance::where('user_id', $request->user()->id)
                     ->where('attendance_date', $request->attendance_date)
                     ->delete();

            // Simpan attendance baru untuk setiap karyawan
            $savedAttendances = [];
            foreach ($request->employees as $emp) {
                $attendance = Attendance::create([
                    'user_id' => $request->user()->id,
                    'employee_id' => $emp['employee_id'],
                    'attendance_date' => $request->attendance_date,
                    'is_present' => $emp['is_present'],
                    'project_id' => $request->project_id,
                    'notes' => $request->notes,
                ]);
                $savedAttendances[] = $attendance->load('employee', 'project');
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Absen berhasil disimpan',
                'data' => $savedAttendances
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // GET /api/employees - Ambil list karyawan
    public function getEmployees(Request $request)
    {
        try {
            $employees = Employee::where('user_id', $request->user()->id)
                                ->orderBy('name')
                                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $employees
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // POST /api/employees - Tambah karyawan baru
    public function storeEmployee(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:191',
                'position' => 'nullable|string|max:191',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
            }

            $employee = Employee::create([
                'user_id' => $request->user()->id,
                'name' => $request->name,
                'position' => $request->position,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Karyawan berhasil ditambahkan',
                'data' => $employee
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // DELETE /api/employees/{id} - Hapus karyawan
    public function deleteEmployee($id, Request $request)
    {
        try {
            $employee = Employee::where('id', $id)
                               ->where('user_id', $request->user()->id)
                               ->first();

            if (!$employee) {
                return response()->json(['status' => 'error', 'message' => 'Karyawan tidak ditemukan'], 404);
            }

            $employee->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Karyawan berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}