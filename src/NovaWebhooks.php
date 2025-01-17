<?php

namespace Pagzi\NovaWebhooks;

use Pagzi\NovaWebhooks\Nova\Webhook;
use Pagzi\NovaWebhooks\Nova\WebhookLog;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class NovaWebhooks extends Tool
{
    /**
     * Perform any tasks that need to happen when the tool is booted.
     *
     * @return void
     */
    public function boot()
    {
        Nova::script('nova-webhooks', __DIR__.'/../dist/js/tool.js');
        Nova::style('nova-webhooks', __DIR__.'/../dist/css/tool.css');
        Nova::resources([
            Webhook::class,
            WebhookLog::class
        ]);
    }

    /**
     * Build the view that renders the navigation links for the tool.
     *
     * @return \Illuminate\View\View
     */
    public function renderNavigation()
    {
        //return view('nova-webhooks::navigation');
    }
}
