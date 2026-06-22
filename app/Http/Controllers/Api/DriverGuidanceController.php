<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DriverGuidanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverGuidanceController extends Controller
{
    public function __invoke(Request $request, DriverGuidanceService $guidanceService): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Panduan driver berhasil dimuat.',
            'data' => $guidanceService->buildFor($request->user()),
        ]);
    }
}
