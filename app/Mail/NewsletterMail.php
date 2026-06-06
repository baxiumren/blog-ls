<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectLine,
        public string $bodyHtml,
        public string $unsubscribeUrl
    ) {}

    public function build()
    {
        return $this->subject($this->subjectLine)
            ->view('emails.newsletter');
    }
}