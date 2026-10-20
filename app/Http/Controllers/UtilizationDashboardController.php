<?php

namespace App\Http\Controllers;

use App\Http\Requests\UtilizationDashboardRequest;
use App\Services\UtilizationDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class UtilizationDashboardController extends Controller
{
    public function __construct(
        private readonly UtilizationDashboardService $dashboard
    ) {
    }

    /**
     * Live dashboard utilisasi (default: minggu berjalan).
     * Tambahkan header Accept: application/json untuk mendapat data JSON.
     */
    public function index(UtilizationDashboardRequest $request): View|JsonResponse
    {
        $data = $this->dashboard->build($request->period());

        if ($request->expectsJson()) {
            return response()->json($data);
        }

        return view('dashboard.utilization', $data);
    }
}
