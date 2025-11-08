<?php

namespace App\Mail;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;

class MailMailableSend extends Mailable
{
    use Queueable, SerializesModels;

    public $mailable;

    public $data;

    public $templateData;

    public $type;

    /**
     * Create a new message instance.
     */
    public function __construct($mailable, $data, $type = '')
    {

        $this->mailable = $mailable ?? '';
        $this->data = $data;
        $this->type = $type;
    }

    /**
     * Build the message.
     *
     * @return $this
     */


    public function content()
    {

        \Log::info($this->mailable);

        $this->templateData = $this->mailable->template_detail;
        foreach ($this->data as $key => $value) {
            $replace = is_scalar($value) ? (string)$value : (is_null($value) ? '' : json_encode($value));
            $this->templateData = str_replace('[[ '.$key.' ]]', $replace, $this->templateData);
        }

        return new Content(
            markdown: 'mail.markdown',
        );
    }

    public function attachments()
    {
        $files = [];
        if ($this->type == 'complete_booking') {
            \Log::info($this->data);
            $pdf = Pdf::loadHTML(view("mail.invoice-templates.template1" , ['data' => $this->data])->render());
            $files[0] = Attachment::fromData(function () use ($pdf) {
                return $pdf->output();
            }, 'Invoice.pdf')
                ->withMime('application/pdf');
        }

        return $files;
    }
}
