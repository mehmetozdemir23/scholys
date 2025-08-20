<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ImportUserRequest;
use App\Jobs\ImportUsers;
use Illuminate\Http\JsonResponse;

final class ImportUserController extends Controller
{
    public function __invoke(ImportUserRequest $request): JsonResponse
    {
        ImportUsers::dispatch($request);

        return response()->json([
            'message' => 'Import des utilisateurs en cours. Vous recevrez une notification une fois terminé.',
            'status' => 'processing',
        ]);
    }
}
