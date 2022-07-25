<?php

namespace Pagzi\NovaWebhooks\Tests\Feature;

use Pagzi\NovaWebhooks\Enums\ModelEvents;
use Pagzi\NovaWebhooks\Listeners\WebhookFailed;
use Pagzi\NovaWebhooks\Listeners\WebhookSucceeded;
use Pagzi\NovaWebhooks\Models\Webhook;
use Pagzi\NovaWebhooks\Tests\Models\Api\PageLike;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Pagzi\NovaWebhooks\Tests\TestCase;
use Spatie\WebhookServer\Events\FinalWebhookCallFailedEvent;
use Spatie\WebhookServer\Events\WebhookCallSucceededEvent;

class EventLoggingTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        Webhook::factory()
            ->create([
                'url' => 'https://webhook.site/022b21d3-fce9-43e8-bc89-ba122fcc124d',
                'settings' => [
                    PageLike::class.':'.ModelEvents::Created,
                    PageLike::class.':'.ModelEvents::Deleted,
                ],
                'secret' => Webhook::newSecret(),
            ]);
    }

    /**
     * @test
     * @covers \Pagzi\NovaWebhooks\Listeners\WebhookSucceeded
     */
    public function custom_event_listener_is_listening_to_the_call_succeeded_event()
    {
        \Event::fake();

        $like = PageLike::factory()
            ->create();

        $like->delete();

        \Event::assertListening(WebhookCallSucceededEvent::class, WebhookSucceeded::class);
    }

    /**
     * @test
     * @covers \Pagzi\NovaWebhooks\Listeners\WebhookSucceeded
     */
    public function custom_event_listener_is_listening_to_the_call_failed_event()
    {
        \Event::fake();

        $like = PageLike::factory()
            ->create();

        $like->delete();

        \Event::assertListening(FinalWebhookCallFailedEvent::class, WebhookFailed::class);
    }
}
