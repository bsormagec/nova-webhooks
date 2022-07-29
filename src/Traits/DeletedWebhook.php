<?php

namespace Pagzi\NovaWebhooks\Traits;

use Pagzi\NovaWebhooks\Enums\ModelEvents;
use Pagzi\NovaWebhooks\Library\WebhookUtility;
use Illuminate\Database\Eloquent\Model;

/**
 * Executes a webhook when the extended model is emits a "deleted" event
 * @package pagzi/nova-webhooks
 */
trait DeletedWebhook
{
    use WebhookModelLabel;

    /**
     * @return void
     */
    public static function bootDeletedWebhook() : void
    {
        static::deleted(function ($model) {
            self::deletedWebhook($model);
        });
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param boolean $isTest If the webhook is running as a test through the testing action
     * @return void
     * @throws \Exception
     */
    public static function deletedWebhook($model, bool $isTest = false)
    {
        /**
         * @param \Illuminate\Database\Eloquent\Model $model
         */
        $payload = self::deletedWebhookPayload($model);
        WebhookUtility::executeWebhook($model, ModelEvents::Deleted, $payload, $isTest);
    }

    /**
     * @param Model $model
     * @return array|mixed
     */
    protected static function deletedWebhookPayload($model)
    {
        return [
            'event' => ModelEvents::Deleted,
            'payload' => $model->toArray(),
        ];
    }
}
