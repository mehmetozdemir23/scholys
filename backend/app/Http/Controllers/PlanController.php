<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\JsonResponse;

final class PlanController extends Controller
{
    /**
     * Get all available plans.
     */
    public function index(): JsonResponse
    {
        $plans = Plan::where('is_active', true)->get();

        return response()->json($plans);
    }
}
