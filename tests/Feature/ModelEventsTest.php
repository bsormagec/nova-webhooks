<?php

namespace Pagzi\NovaWebhooks\Tests\Feature;

use Pagzi\NovaWebhooks\Enums\ModelEvents;
use Pagzi\NovaWebhooks\Models\Webhook;
use Pagzi\NovaWebhooks\Tests\Models\Api\PageLike;
use Pagzi\NovaWebhooks\Tests\Models\PageView;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use Pagzi\NovaWebhooks\Tests\TestCase;
use Spatie\WebhookServer\CallWebhookJob;

class ModelEventsTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    /**
     * @test
     * @covers \Pagzi\NovaWebhooks\Traits\CreatedWebhook::bootCreatedWebhook
     * @covers \Pagzi\NovaWebhooks\Library\WebhookUtility::executeWebhook
     */
    public function correctly_configured_webhook_fires_upon_model_creation()
    {
        Queue::fake();

        Webhook::factory()
            ->create([
                'settings' => [
                    PageView::class.':'.ModelEvents::Created
                ]
            ]);

        PageView::factory()
            ->create();

        $this->assertDatabaseCount('page_views', 1);
        Queue::assertPushed(PageView::$job);
    }

    /**
     * @test
     * @covers \Pagzi\NovaWebhooks\Traits\CreatedWebhook::bootCreatedWebhook
     * @covers \Pagzi\NovaWebhooks\Library\WebhookUtility::executeWebhook
     */
    public function incorrectly_configured_webhook_does_not_fire_upon_model_creation()
    {
        Queue::fake();

        Webhook::factory()
            ->create([
                'settings' => [
                    PageView::class.':'.ModelEvents::Updated
                ]
            ]);

        PageView::factory()
            ->create();

        $this->assertDatabaseCount('page_views', 1);
        Queue::assertNotPushed(CallWebhookJob::class);
    }

    /**
     * @test
     */
    public function webhook_with_invalid_model_is_skipped()
    {
        Queue::fake();

        Webhook::factory()
            ->create([
                'settings' => [
                    'Pagzi\NovaWebhooks\Tests\Models\User'.':'.ModelEvents::Created
                ]
            ]);

        PageView::factory()
            ->create();

        Queue::assertPushed(PageView::$job);
        Queue::assertNotPushed(CallWebhookJob::class);
    }

    /**
     * @test
     * @covers \Pagzi\NovaWebhooks\Traits\UpdatedWebhook::bootUpdatedWebhook
     */
    public function nested_webhook_fires_upon_model_update()
    {
        Queue::fake();

        $like = PageLike::factory()
            ->create();

        Webhook::factory()
            ->create([
                'settings' => [
                    PageView::class.':updated',
                    PageLike::class.':updated',
                ]
            ]);

        Webhook::factory()
            ->create([
                'settings' => [
                    PageLike::class.':'.ModelEvents::Updated,
                ]
            ]);

        $this->assertDatabaseCount('page_likes', 1);

        $like->page = $this->faker->firstName();
        $like->save();

        Queue::assertPushed(CallWebhookJob::class, 2);
    }

    /**
     * @test
     * @covers \Pagzi\NovaWebhooks\Traits\DeletedWebhook::bootDeletedWebhook
     */
    public function webhook_fires_upon_model_deletion()
    {
        Queue::fake();

        $like = PageLike::factory()
            ->create();

        Webhook::factory()
            ->create([
                'settings' => [
                    PageLike::class.':'.ModelEvents::Deleted,
                ]
            ]);

        $like->delete();

        Queue::assertPushed(CallWebhookJob::class, 1);
    }

    /**
     * @test
     * @covers \Pagzi\NovaWebhooks\Traits\DeletedWebhook::bootDeletedWebhook
     * @covers \Pagzi\NovaWebhooks\Traits\DeletedWebhook::deletedWebhookPayload
     */
    public function webhook_fires_with_json_resource()
    {
        Queue::fake();

        Webhook::factory()
            ->create([
                'settings' => [
                    PageLike::class.':'.ModelEvents::Created,
                    PageLike::class.':'.ModelEvents::Deleted,
                ]
            ]);

        $like = PageLike::factory()
            ->create();

        $like->page = 'new name';

        $like->save();
        $like->delete();

        Queue::assertPushed(CallWebhookJob::class, function(\Spatie\WebhookServer\CallWebhookJob $job) {
            $this->assertIsArray($job->payload);
            $this->assertArrayHasKey('page', $job->payload);
            $this->assertArrayHasKey('created', $job->payload);
            $this->assertEquals('Page Like', $job->payload['page']);
            $this->assertEquals('recently', $job->payload['created']);

            return true;
        });
    }
}
