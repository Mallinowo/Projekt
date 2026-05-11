<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MatchMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public User $matched) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('mail.match_subject', ['name' => $this->matched->name]));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.match');
    }
}
