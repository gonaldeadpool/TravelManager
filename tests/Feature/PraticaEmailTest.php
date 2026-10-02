<?php

namespace Tests\Feature;

use App\Mail\PraticaRiepilogoMail;
use App\Models\AppSetting;
use App\Models\Cliente;
use App\Models\Pratica;
use App\Models\User;
use App\Models\Viaggio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PraticaEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_encrypted_smtp_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put(route('amministrazione.mail.update'), [
            'smtp_host' => 'smtp.example.test',
            'smtp_port' => 587,
            'smtp_scheme' => 'smtp',
            'smtp_username' => 'agenzia',
            'smtp_password' => 'secret-password',
            'from_address' => 'agenzia@example.test',
            'from_name' => 'Agenzia Viaggi',
        ])->assertRedirect(route('amministrazione'));

        $storedPassword = AppSetting::where('key', 'mail.smtp.password')->value('value');

        $this->assertNotSame('secret-password', $storedPassword);
        $this->assertSame('secret-password', Crypt::decryptString($storedPassword));
    }

    public function test_operator_cannot_change_smtp_settings(): void
    {
        $operator = User::factory()->create(['role' => 'operatore']);

        $this->actingAs($operator)->put(route('amministrazione.mail.update'), [])->assertForbidden();
    }

    public function test_practice_pdf_is_sent_once_per_unique_selected_or_manual_recipient(): void
    {
        Mail::fake();
        $operator = User::factory()->create(['role' => 'operatore', 'menu_permissions' => ['pratiche']]);
        $viaggio = Viaggio::create([
            'nome' => 'Roma',
            'tipologia' => 'viaggio',
            'destinazione' => 'Roma',
            'data_partenza' => '2026-11-01',
            'data_rientro' => '2026-11-05',
        ]);
        $pratica = Pratica::create(['viaggio_id' => $viaggio->id]);
        $cliente = Cliente::create(['nome' => 'Mario', 'cognome' => 'Rossi', 'email' => 'mario@example.test']);
        $pratica->clienti()->attach($cliente->id);

        foreach ([
            'mail.smtp.host' => 'smtp.example.test',
            'mail.smtp.port' => '587',
            'mail.smtp.scheme' => 'smtp',
            'mail.from.address' => 'agenzia@example.test',
            'mail.from.name' => 'Agenzia',
        ] as $key => $value) {
            AppSetting::create(['key' => $key, 'value' => $value]);
        }

        $this->actingAs($operator)->post(route('pratiche.riepilogo.email', $pratica), [
            'client_recipients' => ['mario@example.test'],
            'manual_recipients' => "mario@example.test; altro@example.test\nTERZO@example.test",
            'subject' => 'Riepilogo pratica',
            'body' => 'Buongiorno',
        ])->assertRedirect();

        $this->assertSessionHas('emailSuccess', 'Riepilogo inviato a 3 destinatari.');
        Mail::assertSent(PraticaRiepilogoMail::class, 3);
        Mail::assertSent(PraticaRiepilogoMail::class, fn (PraticaRiepilogoMail $mail) => $mail->hasTo('mario@example.test'));
        Mail::assertSent(PraticaRiepilogoMail::class, fn (PraticaRiepilogoMail $mail) => $mail->hasTo('altro@example.test'));
        Mail::assertSent(PraticaRiepilogoMail::class, fn (PraticaRiepilogoMail $mail) => $mail->hasTo('TERZO@example.test'));
    }
}
