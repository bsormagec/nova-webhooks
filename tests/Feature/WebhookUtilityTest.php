<?php

namespace Pagzi\NovaWebhooks\Tests\Feature;

use Pagzi\NovaWebhooks\Library\WebhookUtility;
use Pagzi\NovaWebhooks\Models\Webhook;
use Pagzi\NovaWebhooks\Tests\Models\PageView;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Pagzi\NovaWebhooks\Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use Spatie\WebhookServer\CallWebhookJob;

class WebhookUtilityTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    /**
     * @test
     * @covers \Pagzi\NovaWebhooks\Library\WebhookUtility::getWebhooks
     */
    public function user_can_get_a_list_of_webhooks_from_a_model_and_action()
    {
        Webhook::factory()
            ->count(3)
            ->create([
                'settings' => [
                    PageView::class . ':created'
                ]
            ]);

        Webhook::factory()
            ->create([
                'settings' => [
                    PageView::class . ':updated'
                ]
            ]);

        $hooks = WebhookUtility::getWebhooks(PageView::class.':created');

        $this->assertCount(3, $hooks);
    }

    /**
     * @test
     * @covers \Pagzi\NovaWebhooks\Library\WebhookUtility::processWebhooks
     */
    public function processed_webhooks_will_be_dispatched_based_on_model_and_query()
    {
        Queue::fake();
        PageView::unsetEventDispatcher();

        Webhook::factory()
            ->count(2)
            ->create([
                'settings' => [
                    PageView::class . ':created'
                ],
                'secret' => Webhook::newSecret(),
            ]);

        Webhook::factory()
            ->create([
                'settings' => [
                    PageView::class . ':updated'
                ],
                'secret' => Webhook::newSecret(),
            ]);

        $pageView = PageView::factory()
            ->create();

        WebhookUtility::processWebhooks($pageView, 'created', $pageView->toArray());
        Queue::assertPushed(CallWebhookJob::class, 2);
    }

    /**
     * @test
     * @covers \Pagzi\NovaWebhooks\Library\WebhookUtility::executeWebhook
     * @covers \Pagzi\NovaWebhooks\Traits\ShouldQueueWebhook
     */
    public function all_webhooks_will_be_executed_via_job()
    {
        Queue::fake();

        PageView::unsetEventDispatcher();

        Webhook::factory()
            ->count(2)
            ->create([
                'settings' => [
                    PageView::class . ':created'
                ],
                'secret' => Webhook::newSecret(),
            ]);

        Webhook::factory()
            ->create([
                'settings' => [
                    PageView::class . ':deleted'
                ],
                'secret' => Webhook::newSecret(),
            ]);

        $pageView = PageView::factory()
            ->create();

        WebhookUtility::executeWebhook($pageView, 'created', $pageView->toArray(), true);
        Queue::assertPushed(PageView::$job, 1);
    }
}
