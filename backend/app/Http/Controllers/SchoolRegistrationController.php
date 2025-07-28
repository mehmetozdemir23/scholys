<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CompleteAccountSetup;
use App\Actions\SendSchoolInvitation;
use App\Http\Requests\SchoolInvitationRequest;
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
            return response()->json(['message' => 'Failed to send invitation email: '.$e->getMessage()], 500);
        }

        return response()->json(['message' => 'Invitation sent successfully.']);
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

            auth()->loginUsingId($result['user_id']);

            $params = http_build_query(
                [
                    'status' => 'success',
                    'user_id' => $result['user_id'],
                    'user_email' => $result['user_email'],
                ]
            );

            return redirect("$frontendUrl/school/registration?$params");
        } catch (InvalidSignatureException) {
            $params = http_build_query([
                'status' => 'error',
                'message' => 'Invalid or expired invitation link.',
            ]);

            return redirect("$frontendUrl/school/registration?$params");
        } catch (Exception $e) {
            $params = http_build_query([
                'status' => 'error',
                'message' => 'Failed to confirm school registration: '.$e->getMessage(),
            ]);

            return redirect("$frontendUrl/school/registration?$params");
        }
    }
}
