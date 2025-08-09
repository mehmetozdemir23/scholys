<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Mail\PasswordResetMail;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

final class ForgotPasswordController extends Controller
{
    public function __invoke(ForgotPasswordRequest $request): JsonResponse
    {
        $token = Str::random(64);

        $validated = $request->validated();

        DB::table('password_resets')->updateOrInsert(
            ['email' => $validated['email']],
            [
                'token' => bcrypt($token),
                'created_at' => Carbon::now(),
            ]
        );

        Mail::to($validated['email'])->send(new PasswordResetMail($token));

        return response()->json([
            'message' => 'Si votre adresse e-mail est enregistrée, vous recevrez bientôt un lien pour réinitialiser votre mot de passe.',
        ]);
    }
}
