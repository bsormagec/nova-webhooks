<?php

namespace Pagzi\NovaWebhooks\Tests\Feature;

use Pagzi\NovaWebhooks\Tests\Models\PageView;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Pagzi\NovaWebhooks\Tests\TestCase;

class ModelTraitsTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    /**
     * @test
     * @covers \Pagzi\NovaWebhooks\Traits\CreatedWebhook::bootCreatedWebhook
     * @covers \Pagzi\NovaWebhooks\Traits\CreatedWebhook::createdWebhookPayload
     * @covers \Pagzi\NovaWebhooks\Traits\UpdatedWebhook::bootUpdatedWebhook
     * @covers \Pagzi\NovaWebhooks\Traits\UpdatedWebhook::updatedWebhookPayload
     * @covers \Pagzi\NovaWebhooks\Traits\DeletedWebhook::bootDeletedWebhook
     * @covers \Pagzi\NovaWebhooks\Traits\DeletedWebhook::deletedWebhookPayload
     * @covers \Pagzi\NovaWebhooks\Traits\AllWebhooks
     */
    public function model_has_all_available_bootable_methods_and_payloads()
    {
        $this->assertTrue(trait_exists(\Pagzi\NovaWebhooks\Traits\CreatedWebhook::class));
        $this->assertTrue(trait_exists(\Pagzi\NovaWebhooks\Traits\UpdatedWebhook::class));
        $this->assertTrue(trait_exists(\Pagzi\NovaWebhooks\Traits\DeletedWebhook::class));
        $this->assertTrue(trait_exists(\Pagzi\NovaWebhooks\Traits\AllWebhooks::class));

        $this->assertTrue(method_exists(PageView::class, 'bootCreatedWebhook'));
        $this->assertTrue(method_exists(PageView::class, 'bootUpdatedWebhook'));
        $this->assertTrue(method_exists(PageView::class, 'bootDeletedWebhook'));

        $this->assertTrue(method_exists(PageView::class, 'createdWebhookPayload'));
        $this->assertTrue(method_exists(PageView::class, 'updatedWebhookPayload'));
        $this->assertTrue(method_exists(PageView::class, 'deletedWebhookPayload'));
    }
}
