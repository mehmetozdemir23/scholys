<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Http\Requests\ImportUserRequest;
use App\Mail\ImportCompletedMail;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class ImportUsers implements ShouldQueue
{
    use Queueable;

    public function __construct(private ImportUserRequest $request) {}

    public function handle(): array
    {
        $roles = Role::pluck('id', 'name')->toArray();
        $schoolId = $this->request->user()->school_id;

        $parsedData = $this->parseCSV();
        $validatedData = $this->validateData($parsedData, $roles);

        $result = $this->batchInsert($validatedData, $schoolId, $roles);

        Mail::to($this->request->user()->email)->send(
            new ImportCompletedMail($result, $this->request->user())
        );

        return $result;
    }

    private function parseCSV(): array
    {
        $file = $this->request->file('users');
        $csvData = file_get_contents($file->getPathname());
        $lines = array_map('str_getcsv', explode("\n", $csvData));

        $header = array_shift($lines);
        $data = [];

        foreach ($lines as $index => $line) {
            if (array_filter($line) === []) {
                continue;
            }

            $data[] = [
                'index' => $index + 2,
                'data' => array_combine($header, $line),
            ];
        }

        return $data;
    }

    private function validateData(array $parsedData, array $roles): array
    {
        $valid = [];
        $errors = [];

        foreach ($parsedData as $item) {
            try {
                $validator = Validator::make($item['data'], [
                    'firstname' => ['required', 'string', 'max:255'],
                    'lastname' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'email', 'unique:users,email'],
                    'role' => ['required', 'string', Rule::in(array_keys($roles))],
                ]);

                if ($validator->fails()) {
                    throw new Exception($validator->errors()->first());
                }

                $item['data']['password'] = Str::random(12);
                $valid[] = $item;
            } catch (Exception $e) {
                $errors[] = [
                    'line' => $item['index'],
                    'data' => $item['data'],
                    'error' => $e->getMessage(),
                ];
                Log::error('Erreur lors de la validation utilisateur', [
                    'line' => $item['index'],
                    'data' => $item['data'],
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return ['valid' => $valid, 'errors' => $errors];
    }

    private function batchInsert(array $validatedData, string $schoolId, array $roles): array
    {
        $usersToInsert = [];
        $rolesAssignments = [];
        $emailsToSend = [];
        $now = now();

        foreach ($validatedData['valid'] as $item) {
            $userData = $item['data'];
            $userId = Str::uuid();

            $usersToInsert[] = [
                'id' => $userId,
                'school_id' => $schoolId,
                'firstname' => $userData['firstname'],
                'lastname' => $userData['lastname'],
                'email' => $userData['email'],
                'password' => bcrypt($userData['password']),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $rolesAssignments[] = [
                'user_id' => $userId,
                'role_id' => $roles[$userData['role']],
            ];

            $emailsToSend[] = [
                'email' => $userData['email'],
                'firstname' => $userData['firstname'],
                'lastname' => $userData['lastname'],
                'password' => $userData['password'],
            ];
        }

        DB::transaction(function () use ($usersToInsert, $rolesAssignments, $emailsToSend): void {
            collect($usersToInsert)->chunk(500)->each(function ($chunk): void {
                User::insert($chunk->toArray());
            });

            collect($rolesAssignments)->chunk(500)->each(function ($chunk): void {
                DB::table('role_user')->insert($chunk->toArray());
            });

            foreach ($emailsToSend as $emailData) {
                SendWelcomeEmail::dispatch($emailData);
            }
        });

        return [
            'successCount' => count($validatedData['valid']),
            'errorCount' => count($validatedData['errors']),
            'errors' => $validatedData['errors'],
        ];
    }
}
