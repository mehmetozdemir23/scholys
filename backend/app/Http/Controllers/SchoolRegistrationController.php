<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\InviteSchool;
use App\Http\Requests\SchoolInvitationRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SchoolRegistrationController extends Controller
{
    public function sendInvitation(SchoolInvitationRequest $request, InviteSchool $inviteSchool): JsonResponse
    {
        /** @var string $email */
        $email = $request->email;

        try {
            $inviteSchool->handle($email);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to send invitation email: ' . $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Invitation sent successfully.']);
    }

    public function confirmInvitation(Request $request): JsonResponse
    {
        if (!$request->hasValidSignature()) {
            return response()->json(['message' => 'Invalid or expired invitation link.'], 403);
        }

        return response()->json(['message' => 'Invitation confirmed successfully.']);
    }
}
