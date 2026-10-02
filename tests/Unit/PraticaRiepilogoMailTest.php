<?php

namespace Tests\Unit;

use App\Mail\PraticaRiepilogoMail;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PraticaRiepilogoMailTest extends TestCase
{
    public function test_mail_contains_custom_subject_text_and_one_pdf_attachment(): void
    {
        $mail = new PraticaRiepilogoMail(
            'Oggetto prova',
            'Testo prova',
            '%PDF-contenuto',
            'riepilogo-pratica-12.pdf'
        );

        $this->assertSame('Oggetto prova', $mail->envelope()->subject);
        $this->assertSame('emails.pratiche.riepilogo', $mail->content()->text);
        $this->assertSame(['bodyText' => 'Testo prova'], $mail->content()->with);
        $this->assertCount(1, $mail->attachments());

        Mail::fake();
        Mail::mailer('smtp')->to('destinatario@example.test')->send($mail);

        Mail::assertSent(PraticaRiepilogoMail::class, fn (PraticaRiepilogoMail $sentMail) => $sentMail->hasTo('destinatario@example.test'));
    }
}
