<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserRegistrationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $roleInfo;

    /**
     * Create a new message instance.
     */
    public function __construct($user)
    {
        $this->user = $user;
        
        // Role-specific information
        $this->roleInfo = [
            'client' => [
                'title' => 'Client Account',
                'next_steps' => 'To start purchasing paints and tracking orders, please complete your profile and await account activation.',
                'requirements' => 'Complete profile information and verify your email address.'
            ],
            'distributor' => [
                'title' => 'Distributor Account',
                'next_steps' => 'To sell and distribute paint products, please submit your business documents for verification.',
                'requirements' => 'Submit business registration documents and valid ID for verification.'
            ],
            'service_provider' => [
                'title' => 'Service Provider Account',
                'next_steps' => 'To offer painting and maintenance services, please submit your professional credentials.',
                'requirements' => 'Submit professional license, portfolio, and valid ID for verification.'
            ],
        ];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to CaviteGo Paint - Your Account is Successfully Created!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.user-registration',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}