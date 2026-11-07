<?php

namespace Tests\Feature\Api;

use App\Mail\WelcomePromo;
use App\Models\PromoCode;
use App\Models\Subscriber;
use App\Services\WelcomePromoIssuer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SubscribeApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_new_subscriber_gets_personal_promo_code(): void
    {
        $response = $this->postJson('/api/subscribe', [
            'email' => 'buyer@example.com',
            'name' => 'Оля',
        ])->assertCreated();

        $code = $response->json('promo_code');

        $this->assertNotEmpty($code);
        $this->assertStringStartsWith('WELCOME-', $code);
        $this->assertFalse($response->json('already_subscribed'));

        $this->assertDatabaseHas('subscribers', [
            'email' => 'buyer@example.com',
            'welcome_promo_code' => $code,
            'is_active' => true,
        ]);

        $promo = PromoCode::resolve($code);
        $this->assertNotNull($promo);
        $this->assertSame('percent', $promo->type);
        $this->assertSame(1, $promo->usage_limit);
        $this->assertSame(0, $promo->used_count);
        $this->assertTrue($promo->is_active);
        $this->assertNotNull($promo->expires_at);
    }

    /**
     * Шаблон листа колись не потрапив у коміт, і на проді лист мовчки падав
     * у try/catch: підписка працювала, а пошта — ні. Mail::fake() такого не
     * ловить, бо не рендерить вміст, тож рендеримо явно.
     */
    public function test_welcome_mail_renders(): void
    {
        $promo = (new WelcomePromoIssuer)->issue();

        $html = (new WelcomePromo($promo, 'Оля'))->render();

        $this->assertStringContainsString($promo->code, $html);
        $this->assertStringContainsString('Оля', $html);
    }

    public function test_welcome_mail_is_sent_to_subscriber(): void
    {
        $this->postJson('/api/subscribe', ['email' => 'buyer@example.com'])->assertCreated();

        Mail::assertSent(WelcomePromo::class, fn ($mail) => $mail->hasTo('buyer@example.com'));
    }

    /** Повторна підписка не має штампувати новий код. */
    public function test_repeat_subscribe_returns_the_same_code_and_issues_nothing(): void
    {
        $first = $this->postJson('/api/subscribe', ['email' => 'buyer@example.com'])->assertCreated();
        $code = $first->json('promo_code');

        $second = $this->postJson('/api/subscribe', ['email' => 'buyer@example.com'])->assertOk();

        $this->assertTrue($second->json('already_subscribed'));
        $this->assertSame($code, $second->json('promo_code'));
        $this->assertSame(1, PromoCode::count());
        $this->assertSame(1, Subscriber::count());
    }

    public function test_unsubscribed_email_is_reactivated_without_new_code(): void
    {
        $subscriber = Subscriber::create([
            'email' => 'back@example.com',
            'is_active' => false,
            'welcome_promo_code' => 'WELCOME-OLDONE',
        ]);

        $response = $this->postJson('/api/subscribe', [
            'email' => 'back@example.com',
            'name' => 'Ніна',
        ])->assertOk();

        $this->assertSame('WELCOME-OLDONE', $response->json('promo_code'));
        $this->assertSame(0, PromoCode::count());

        $subscriber->refresh();
        $this->assertTrue($subscriber->is_active);
        $this->assertSame('Ніна', $subscriber->name);
    }

    public function test_promo_code_is_usable_at_checkout(): void
    {
        $code = $this->postJson('/api/subscribe', ['email' => 'buyer@example.com'])
            ->json('promo_code');

        $this->postJson('/api/promo/validate', ['code' => $code, 'subtotal' => 1000])
            ->assertOk()
            ->assertJson(['valid' => true]);
    }

    public function test_codes_are_unique_across_subscribers(): void
    {
        $codes = [];

        foreach (['a@example.com', 'b@example.com', 'c@example.com'] as $email) {
            $codes[] = $this->postJson('/api/subscribe', ['email' => $email])->json('promo_code');
        }

        $this->assertCount(3, array_unique($codes));
    }

    public function test_email_is_required_and_validated(): void
    {
        $this->postJson('/api/subscribe', [])->assertUnprocessable();
        $this->postJson('/api/subscribe', ['email' => 'not-an-email'])->assertUnprocessable();
        $this->assertSame(0, Subscriber::count());
        $this->assertSame(0, PromoCode::count());
    }

    /** Збій пошти не має втрачати підписку. */
    public function test_subscription_survives_mail_failure(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('smtp down'));

        $this->postJson('/api/subscribe', ['email' => 'buyer@example.com'])->assertCreated();

        $this->assertDatabaseHas('subscribers', ['email' => 'buyer@example.com']);
        $this->assertSame(1, PromoCode::count());
    }
}
