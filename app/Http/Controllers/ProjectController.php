<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class ProjectController extends Controller
{
    /**
     * Constructor - memastikan semua method menggunakan auth
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // ✅ PERBAIKAN: Filter berdasarkan user_id dan status (jika ada query param)
            $query = Project::where('user_id', $request->user()->id)
                           ->orderBy('created_at', 'desc');
            
            // Filter by status jika ada query param
            if ($request->has('status') && $request->status != 'semua') {
                $query->where('status', $request->status);
            }
            
            $projects = $query->with('items')->get();
            
            return response()->json([
                'status' => 'success',
                'data' => $projects
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // ✅ PERBAIKAN: Status baru (proses, belum_bayar, dp, selesai)
            $validator = Validator::make($request->all(), [
                'customer_name' => 'required|string|max:191',
                'address' => 'required|string',
                'phone_number' => 'required|string|max:20',
                'status' => 'required|in:proses,belum_bayar,dp,selesai',
                'installation_date' => 'nullable|date',
                'notes' => 'nullable|string',
                'items' => 'nullable|array',
                'items.*.item_name' => 'required_with:items|string',
                'items.*.item_price' => 'required_with:items|integer|min:0',
                'items.*.quantity' => 'required_with:items|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
            }

            // Buat project
            $project = Project::create([
                'user_id' => $request->user()->id,
                'customer_name' => $request->customer_name,
                'address' => $request->address,
                'phone_number' => $request->phone_number,
                'status' => $request->status,
                'installation_date' => $request->installation_date,
                'notes' => $request->notes,
            ]);

            // Simpan items jika ada
            if ($request->has('items') && is_array($request->items)) {
                foreach ($request->items as $item) {
                    $subtotal = $item['item_price'] * ($item['quantity'] ?? 1);
                    
                    ProjectItem::create([
                        'project_id' => $project->id,
                        'item_name' => $item['item_name'],
                        'item_price' => $item['item_price'],
                        'quantity' => $item['quantity'] ?? 1,
                        'subtotal' => $subtotal
                    ]);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Proyek berhasil ditambahkan',
                'data' => $project->load('items')
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified project
     */
    public function show($id, Request $request)
    {
        try {
            $user = $request->user();
            $project = Project::where('id', $id)
                              ->where('user_id', $user->id)
                              ->with('items')
                              ->first();

            if (!$project) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Proyek tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $project
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified project
     */
    public function update(Request $request, $id)
    {
        try {
            $project = Project::where('id', $id)
                              ->where('user_id', $request->user()->id)
                              ->first();

            if (!$project) {
                return response()->json(['status' => 'error', 'message' => 'Proyek tidak ditemukan'], 404);
            }

            // ✅ PERBAIKAN: Status baru
            $validator = Validator::make($request->all(), [
                'customer_name' => 'required|string|max:191',
                'address' => 'required|string',
                'phone_number' => 'required|string|max:20',
                'status' => 'required|in:proses,belum_bayar,dp,selesai',
                'installation_date' => 'nullable|date',
                'notes' => 'nullable|string',
                'items' => 'nullable|array',
                'items.*.item_name' => 'required_with:items|string',
                'items.*.item_price' => 'required_with:items|integer|min:0',
                'items.*.quantity' => 'required_with:items|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
            }

            // Update project
            $project->update([
                'customer_name' => $request->customer_name,
                'address' => $request->address,
                'phone_number' => $request->phone_number,
                'status' => $request->status,
                'installation_date' => $request->installation_date,
                'notes' => $request->notes,
            ]);

            // Update items (delete lama, create baru)
            if ($request->has('items')) {
                $project->items()->delete();
                
                if (is_array($request->items)) {
                    foreach ($request->items as $item) {
                        $subtotal = $item['item_price'] * ($item['quantity'] ?? 1);
                        
                        ProjectItem::create([
                            'project_id' => $project->id,
                            'item_name' => $item['item_name'],
                            'item_price' => $item['item_price'],
                            'quantity' => $item['quantity'] ?? 1,
                            'subtotal' => $subtotal
                        ]);
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Proyek berhasil diperbarui',
                'data' => $project->load('items')
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete the specified project
     */
    public function destroy($id, Request $request)
    {
        try {
            $user = $request->user();
            $project = Project::where('id', $id)->where('user_id', $user->id)->first();

            if (!$project) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Proyek tidak ditemukan'
                ], 404);
            }

            $project->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Proyek berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}