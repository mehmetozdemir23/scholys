<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GetSchoolStats;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class DashboardController extends Controller
{
    public function schoolStats(Request $request, GetSchoolStats $getSchoolStats): JsonResponse
    {
        Gate::authorize('viewSchoolStats', $request->user());

        $stats = $getSchoolStats->handle($request->user());

        return response()->json($stats);
    }
}
