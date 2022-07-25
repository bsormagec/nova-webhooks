<?php

namespace Pagzi\NovaWebhooks\Tests\Feature;

use Pagzi\NovaWebhooks\Library\ModelUtility;
use Pagzi\NovaWebhooks\Tests\Models\PageView;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Pagzi\NovaWebhooks\Tests\TestCase;

class ModelUtilityTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    /**
     * @test
     * @covers \Pagzi\NovaWebhooks\Library\ModelUtility::availableModelActions
     * @covers \Pagzi\NovaWebhooks\Library\ModelUtility::getModels
     */
    public function can_retrieve_all_of_the_available_models()
    {
        $actions = ModelUtility::availableModelActions();

        $this->assertCount(2, $actions);
        $this->assertStringContainsString(PageView::class, $actions[1]->name);
    }
}
