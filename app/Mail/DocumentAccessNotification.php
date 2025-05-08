<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Document;

class DocumentAccessNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $document;
    public $permission;
    public $user;

    public function __construct(Document $document, $permission, $user)
    {
        $this->document = $document;
        $this->permission = $permission;
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('New document with access')
                    ->view('emails.document_access_notification')
                    ->with([
                        'user' => $this->user,
                        'document' => $this->document,
                        'permission' => $this->permission,
                    ]);
    }
}

