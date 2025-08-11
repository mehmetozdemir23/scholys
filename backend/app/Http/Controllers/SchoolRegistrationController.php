<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CompleteAccountSetup;
use App\Actions\SendSchoolInvitation;
use App\Http\Requests\ResetPasswordAfterInvitationRequest;
use App\Http\Requests\SchoolInvitationRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;

final class SchoolRegistrationController extends Controller
{
    /**
     * Send an invitation to a school.
     */
    public function sendInvitation(SchoolInvitationRequest $request, SendSchoolInvitation $sendSchoolInvitation): JsonResponse
    {
        /** @var string $email */
        $email = $request->email;

        try {
            $sendSchoolInvitation->handle($email);
        } catch (Exception $e) {
            return response()->json(['message' => 'Échec de l\'envoi de l\'invitation : '.$e->getMessage()], 500);
        }

        return response()->json(['message' => 'Invitation envoyée avec succès.']);
    }

    /**
     * Complete the account setup after the school invitation is accepted.
     */
    public function completeAccountSetup(Request $request, CompleteAccountSetup $completeAccountSetup): RedirectResponse
    {
        /** @var string $frontendUrl */
        $frontendUrl = config('app.frontend_url');

        try {
            $result = $completeAccountSetup->handle($request);

            /** @var User $user */
            $user = User::find($result['user_id']);
            $token = $user->createToken('Token d\'inscription scolaire')->plainTextToken;

            $params = http_build_query(
                [
                    'status' => 'success',
                    'token' => $token,
                    'user_email' => $result['user_email'],
                ]
            );

            return redirect("$frontendUrl/school/registration?$params");
        } catch (InvalidSignatureException) {
            $params = http_build_query([
                'status' => 'error',
                'message' => 'Lien d\'invitation invalide ou expiré.',
            ]);

            return redirect("$frontendUrl/school/registration?$params");
        } catch (Exception $e) {
            $params = http_build_query([
                'status' => 'error',
                'message' => 'Échec de la confirmation d\'inscription : '.$e->getMessage(),
            ]);

            return redirect("$frontendUrl/school/registration?$params");
        }
    }

    /**
     * Reset the password after the school invitation is accepted.
     */
    public function resetPasswordAfterInvitation(ResetPasswordAfterInvitationRequest $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $request->user();

            /** @var string $password */
            $password = $request->input('password');

            $user->update([
                'password' => bcrypt($password),
            ]);

        }
        // @codeCoverageIgnoreStart
        catch (Exception $e) {
            return response()->json(['message' => 'Échec de la réinitialisation du mot de passe : '.$e->getMessage()], 500);
        }
        // @codeCoverageIgnoreEnd

        return response()->json(['message' => 'Mot de passe réinitialisé avec succès.']);
    }
}
