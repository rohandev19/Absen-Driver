<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TransportCostService;
use App\Models\TransportCost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;

class TransportCostController extends Controller
{
    public function __construct(
        private TransportCostService $transportCostService
    ) {}

    /**
     * Check if driver can create trip entry for today
     * GET /api/transport-costs/can-create
     */
    public function canCreate(Request $request)
    {
        $driver = Auth::user();
        $date = $request->input('date', now()->toDateString());

        $result = $this->transportCostService->canCreateTripEntry($driver, $date);

        return response()->json($result);
    }

    /**
     * Create new trip entry
     * POST /api/transport-costs
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'do_number' => 'required|string|max:500',
            'drop_point_count' => 'required|integer|min:1',
            'delivery_location' => 'required|string',
            'gasoline_cost' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
            'toll_cost' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
            'parking_cost' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
            'gasoline_price_per_liter' => 'nullable|numeric|min:0',
            'delivery_start_time' => 'required|date_format:Y-m-d H:i:s',
            'delivery_end_time' => 'required|date_format:Y-m-d H:i:s|after:delivery_start_time',
            'gasoline_receipt' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
            'toll_receipt' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
            'parking_receipt' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
        ], [
            'do_number.required' => 'Nomor DO wajib diisi',
            'drop_point_count.min' => 'Jumlah drop point minimal 1',
            'gasoline_cost.regex' => 'Biaya bensin maksimal 2 angka desimal',
            'toll_cost.regex' => 'Biaya tol maksimal 2 angka desimal',
            'parking_cost.regex' => 'Biaya parkir maksimal 2 angka desimal',
            'delivery_end_time.after' => 'Waktu selesai harus setelah waktu mulai',
            'gasoline_receipt.image' => 'Bukti bensin harus berupa gambar',
            'gasoline_receipt.max' => 'Bukti bensin maksimal 5MB',
            'toll_receipt.image' => 'Bukti tol harus berupa gambar',
            'toll_receipt.max' => 'Bukti tol maksimal 5MB',
            'parking_receipt.image' => 'Bukti parkir harus berupa gambar',
            'parking_receipt.max' => 'Bukti parkir maksimal 5MB',
        ]);

        try {
            $driver = Auth::user();
            
            // Process images
            $data = $validated;
            if ($request->hasFile('gasoline_receipt')) {
                $data['gasoline_receipt_path'] = $this->optimizedImageProcessing($request->file('gasoline_receipt'));
            }
            if ($request->hasFile('toll_receipt')) {
                $data['toll_receipt_path'] = $this->optimizedImageProcessing($request->file('toll_receipt'));
            }
            if ($request->hasFile('parking_receipt')) {
                $data['parking_receipt_path'] = $this->optimizedImageProcessing($request->file('parking_receipt'));
            }

            $tripEntry = $this->transportCostService->createTripEntry($driver, $data);

            return response()->json([
                'status' => 'success',
                'message' => 'Trip entry berhasil disimpan',
                'data' => [
                    'id' => $tripEntry->id,
                    'trip_date' => $tripEntry->trip_date->format('Y-m-d'),
                    'total_cost' => $tripEntry->total_cost,
                    'odometer_difference' => $tripEntry->odometer_difference,
                    'fuel_efficiency_ratio' => $tripEntry->fuel_efficiency_ratio,
                    'overtime_hours' => $tripEntry->overtime_hours,
                    'overtime_payment' => $tripEntry->overtime_payment,
                    'bonus_driver' => $tripEntry->bonus_driver,
                    'approval_status' => $tripEntry->approval_status,
                ],
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'error_code' => 'NO_CHECKOUT',
                'message' => 'Anda belum melakukan check-out untuk hari ini',
            ], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == '23000') {
                return response()->json([
                    'status' => 'error',
                    'error_code' => 'DUPLICATE_TRIP',
                    'message' => 'Anda sudah membuat laporan uang jalan untuk hari ini',
                ], 409);
            }
            throw $e;
        }
    }

    /**
     * Get driver's trip history
     * GET /api/transport-costs
     */
    public function index(Request $request)
    {
        $driver = Auth::user();
        $perPage = $request->input('per_page', 20);

        $trips = TransportCost::where('driver_id', $driver->id)
            ->with('vehicle:id,plate_number')
            ->orderBy('trip_date', 'desc')
            ->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $trips->items(),
            'pagination' => [
                'current_page' => $trips->currentPage(),
                'total_pages' => $trips->lastPage(),
                'total_records' => $trips->total(),
            ],
        ]);
    }

    /**
     * Get single trip entry detail
     * GET /api/transport-costs/{id}
     */
    public function show($id)
    {
        $driver = Auth::user();

        $trip = TransportCost::where('id', $id)
            ->where('driver_id', $driver->id)
            ->with(['vehicle', 'project', 'attendance'])
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => $trip,
        ]);
    }

    /**
     * Optimized image processing using Intervention Image.
     */
    private function optimizedImageProcessing($file): string
    {
        $fileName = 'receipts/' . Str::uuid() . '.jpg';

        try {
            $manager = new ImageManager(new GdDriver());
            $image = $manager->read($file);
            $image->scaleDown(width: 1200);
            Storage::disk('public')->put($fileName, $image->encodeByMediaType('image/jpeg', 70));
        } catch (\Throwable $e) {
            \Log::error('TransportCost Image Processing Failed: ' . $e->getMessage());
            // Fallback: simpan file asli jika Intervention Image gagal
            Storage::disk('public')->put($fileName, file_get_contents($file->getRealPath()));
        }

        return $fileName;
    }
}
