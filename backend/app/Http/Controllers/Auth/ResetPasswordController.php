<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class ResetPasswordController extends Controller
{
    public function __invoke(ResetPasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $reset = DB::table('password_resets')
            ->where('email', $validated['email'])
            ->first();

        if (! $reset) {
            return response()->json(['message' => 'Token invalide ou expiré.'], 422);
        }

        if (! Hash::check($validated['token'], $reset->token)) {
            return response()->json(['message' => 'Token invalide ou expiré.'], 422);
        }

        if (Carbon::parse($reset->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_resets')->where('email', $validated['email'])->delete();

            return response()->json(['message' => 'Token invalide ou expiré.'], 422);
        }

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            return response()->json(['message' => 'Utilisateur introuvable.'], 404);
        }

        $user->update([
            'password' => bcrypt($validated['password']),
        ]);

        DB::table('password_resets')->where('email', $validated['email'])->delete();

        return response()->json(['message' => 'Mot de passe réinitialisé avec succès.']);
    }
}
