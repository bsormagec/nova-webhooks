<?php

namespace Pagzi\NovaWebhooks\Traits;

use Pagzi\NovaWebhooks\Enums\ModelEvents;
use Pagzi\NovaWebhooks\Library\WebhookUtility;
use Illuminate\Database\Eloquent\Model;

/**
 * Executes a webhook when the extended model is emits a "created" event
 * @package pagzi/nova-webhooks
 */
trait CreatedWebhook
{
    use WebhookModelLabel;

    /**
     * @return void
     */
    public static function bootCreatedWebhook(): void
    {
        static::created(function ($model) {
            self::createdWebhook($model);
        });
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param boolean $isTest If the webhook is running as a test through the testing action
     * @return void
     * @throws \Exception
     */
    public static function createdWebhook($model, bool $isTest = false)
    {
        $payload = self::createdWebhookPayload($model);
        WebhookUtility::executeWebhook($model, ModelEvents::Created, $payload, $isTest);
    }

    /**
     * @param Model $model
     * @return array|mixed
     */
    protected static function createdWebhookPayload($model)
    {
        return [
            'event' => ModelEvents::Created,
            'entity' => class_basename($model),
            'payload' => $model->toArray(),
        ];
    }
}
