<?php

namespace Pagzi\NovaWebhooks\Http\Controllers;

use Pagzi\NovaWebhooks\Http\Resources\WebhookResource;
use Pagzi\NovaWebhooks\Models\Webhook;
use Illuminate\Http\Request;

class NovaWebhooksController
{

    public function store(Request $request) // TODO add validation
    {
        $webhook = new Webhook($request->validated());
        $webhook->save();

        return new WebhookResource($webhook);
    }

}
