<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ImportCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private array $importResult,
        private User $user
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Import d\'utilisateurs terminé - Scholys',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.import-completed',
            with: [
                'user' => $this->user,
                'successCount' => $this->importResult['successCount'],
                'errorCount' => $this->importResult['errorCount'],
                'errors' => $this->importResult['errors'],
                'hasErrors' => $this->importResult['errorCount'] > 0,
            ],
        );
    }
}
