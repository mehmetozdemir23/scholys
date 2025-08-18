<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateUser;
use App\Http\Requests\ImportUserRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

final class ImportUserController extends Controller
{
    public function __invoke(ImportUserRequest $request, CreateUser $createUser): JsonResponse
    {
        $file = $request->file('users');
        $csvData = file_get_contents($file->getPathname());
        $lines = array_map('str_getcsv', explode("\n", $csvData));

        $header = array_shift($lines);
        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($lines as $index => $line) {
            if (array_filter($line) === []) {
                continue;
            }

            $userData = array_combine($header, $line);

            try {
                $validator = Validator::make($userData, [
                    'firstname' => ['required', 'string', 'max:255'],
                    'lastname' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'email', 'unique:users,email'],
                ]);

                if ($validator->fails()) {
                    throw new Exception($validator->errors()->first());
                }

                $userData['password'] = Str::random(12);
                $userData['school_id'] = $request->user()->school_id;
                $createUser->handle($userData);
                $successCount++;
            } catch (Exception $e) {
                $errorCount++;
                $errors[] = [
                    'line' => $index + 2,
                    'data' => $userData,
                    'error' => $e->getMessage(),
                ];
                Log::error('Erreur lors de l\'import utilisateur', [
                    'line' => $index + 2,
                    'data' => $userData,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'message' => "Import terminé : {$successCount} utilisateurs créés, {$errorCount} erreurs.",
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'errors' => $errors,
        ]);
    }
}
