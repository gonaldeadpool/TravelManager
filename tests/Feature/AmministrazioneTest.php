<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AmministrazioneTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_payment_details(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put(route('amministrazione.update'), [
            'locandine_path' => storage_path('app/public/locandine'),
            'documenti_path' => storage_path('app/private/documenti'),
            'documenti_pratiche_path' => storage_path('app/private/documenti-pratiche'),
            'scadenza_carta_identita' => 30,
            'scadenza_passaporto' => 30,
            'scadenza_patente' => 30,
            'scadenza_altro' => 30,
            'scadenza_acconto' => 30,
            'scadenza_saldo' => 30,
            'pagamento_intestatario' => 'Agenzia Viaggi S.r.l.',
            'pagamento_banca' => 'Banca di prova',
            'pagamento_iban' => 'IT60X0542811101000000123456',
        ])->assertRedirect(route('amministrazione'));

        $this->assertDatabaseHas('app_settings', [
            'key' => 'pagamenti.intestatario',
            'value' => 'Agenzia Viaggi S.r.l.',
        ]);
        $this->assertDatabaseHas('app_settings', [
            'key' => 'pagamenti.banca',
            'value' => 'Banca di prova',
        ]);
        $this->assertDatabaseHas('app_settings', [
            'key' => 'pagamenti.iban',
            'value' => 'IT60X0542811101000000123456',
        ]);
    }
}
