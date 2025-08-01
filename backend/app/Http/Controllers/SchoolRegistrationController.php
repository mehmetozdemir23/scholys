<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CompleteAccountSetup;
use App\Actions\SendSchoolInvitation;
use App\Http\Requests\ResetPasswordAfterInvitationRequest;
use App\Http\Requests\SchoolInvitationRequest;
use App\Http\Requests\SelectPlanRequest;
use App\Models\School;
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

            /** @var User $user */
            $user = User::find($result['user_id']);
            $token = $user->createToken('School Registration Token')->plainTextToken;

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
            return response()->json(['message' => 'Failed to reset password: '.$e->getMessage()], 500);
        }
        // @codeCoverageIgnoreEnd

        return response()->json(['message' => 'Password reset successfully.']);
    }

    /**
     * Select a plan for the school during registration.
     */
    public function selectPlan(SelectPlanRequest $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $request->user();

            /** @var string $planId */
            $planId = $request->input('plan_id');

            /** @var School $school */
            $school = $user->school;

            $school->update([
                'plan_id' => $planId,
            ]);

        }
        // @codeCoverageIgnoreStart
        catch (Exception $e) {
            return response()->json(['message' => 'Failed to select plan: '.$e->getMessage()], 500);
        }
        // @codeCoverageIgnoreEnd

        return response()->json(['message' => 'Plan selected successfully.']);
    }
}
