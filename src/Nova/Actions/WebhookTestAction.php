<?php

namespace Pagzi\NovaWebhooks\Nova\Actions;

use Pagzi\NovaWebhooks\Enums\ModelEvents;
use Pagzi\NovaWebhooks\Facades\WebhookModels;
use Pagzi\NovaWebhooks\Facades\Webhooks;
use Pagzi\NovaWebhooks\Library\WebhookUtility;
use Pagzi\NovaWebhooks\Models\Webhook;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use OwenMelbz\RadioField\RadioButton;
use TuneZilla\DynamicActionFields\DynamicFieldAction;

class WebhookTestAction extends Action
{
    use InteractsWithQueue, Queueable, DynamicFieldAction;

    public $showOnTableRow = true;

    public $showOnIndex = false;

    /**
     * @var Model|Webhook
     */
    protected $model;

    /**
     * @return string
     */
    public function name()
    {
        return __('nova-webhooks::nova.test_webhook');
    }

    /**
     * Perform the action on the given models.
     *
     * @param \Laravel\Nova\Fields\ActionFields $fields
     * @param \Illuminate\Support\Collection $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        /**
         * @var Webhook $model
         */
        foreach ($models as $model) {
            $webhookAction = $fields->get('hook');

            $class = \Str::before($webhookAction, ':');
            $webhookModel = $class::inRandomOrder()->first();
            $actionName = \Str::after($webhookAction, ':');
            $actionName = ModelEvents::fromValue($actionName);

            if (empty($webhookModel)) {
                return Action::danger(__('nova-webhooks::nova.no_models_available', ['model' => $class]));
            }

            if ($actionName->is(ModelEvents::Created)) {
                $class::createdWebhook($webhookModel, true);
            } elseif ($actionName->is(ModelEvents::Updated)) {
                $class::updatedWebhook($webhookModel, true);
            } elseif ($actionName->is(ModelEvents::Deleted)) {
                $class::deletedWebhook($webhookModel, true);
            }
        }
    }

    public function fieldsForModels(Collection $models): array
    {

        if ($models->isEmpty()) {
            return [];
        }
        return [
            RadioButton::make(__('nova-webhooks::nova.webhook_to_test'), 'hook')
                ->options(
                    WebhookModels::parseSavedList((array) $models->first()->settings)
                )
        ];
    }
}
