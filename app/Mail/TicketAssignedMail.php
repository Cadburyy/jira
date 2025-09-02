<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Dandory;
use App\Models\User;

class TicketAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $dandory;
    public $assignedToUser;

    /**
     * Create a new message instance.
     *
     * @param Dandory $dandory
     * @param User $assignedToUser
     */
    public function __construct(Dandory $dandory, User $assignedToUser)
    {
        $this->dandory = $dandory;
        $this->assignedToUser = $assignedToUser;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: '[Do Not Reply] New Dandory Ticket Assigned',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            markdown: 'emails.tickets.assigned',
            with: [
                'dandory' => $this->dandory,
                'user' => $this->assignedToUser,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}