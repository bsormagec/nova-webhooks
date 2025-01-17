<?php

namespace Pagzi\NovaWebhooks\Nova;

use ChrisWare\NovaBreadcrumbs\Traits\Breadcrumbs;
use Coroowicaksono\ChartJsIntegration\StackedChart;
use Pagzi\NovaWebhooks\Models\WebhookLog;
use Pagzi\NovaWebhooks\Nova\WebhookLog as WebhookLogResource;
use Pagzi\NovaWebhooks\Nova\Actions\WebhookTestAction;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Webhook extends WebhookResource
{
    use Breadcrumbs;
    /**
     * Get the fields displayed by the Webhook resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            Text::make(__('nova-webhooks::nova.name'), 'name')
                ->help(__('nova-webhooks::nova.name_help'))
                ->placeholder(__('nova-webhooks::nova.name_placeholder'))
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make(__('nova-webhooks::nova.url'), 'url')
                ->help(__('nova-webhooks::nova.url_help'))
                ->placeholder('https://hooks.zapier.com/hooks/catch/abcd1234')
                ->sortable()
                ->rules('required', 'url'),

            Text::make(__('nova-webhooks::nova.secret'), 'secret')
                ->help(__('nova-webhooks::nova.secret_help'))
                ->hideFromIndex()
                ->placeholder(null)
                ->updateRules('required', 'string', 'min:10', 'max:100')
                ->creationRules('nullable', 'string'),

            $this->optionGroup()
                ->help(__('nova-webhooks::nova.available_actions_help')),

            BelongsTo::make(__('nova-webhooks::nova.last_modified_by'), 'modifiedBy', config('nova-webhooks.users.resource'))
                ->exceptOnForms()
                ->readonly(),

            HasMany::make(__('nova-webhooks::nova.webhook_failure_logs'), 'logs', WebhookLogResource::class)
                ->readonly(),
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            (new WebhookTestAction($this->model()))
                ->confirmButtonText(__('nova-webhooks::nova.test')),
        ];
    }

    /**
     * @return array
     */
    public function cards(Request $request)
    {
        $resourceId = $request->get('resourceId');
        $dataSeries = [
            [
                'label' => __('nova-webhooks::nova.successful_calls'),
                'backgroundColor' => '#2BAE68',
                'filter' => [
                    'key' => 'successful',
                    'operator' => '=',
                    'value' => 1
                ],
            ],
            [
                'label' => __('nova-webhooks::nova.failed_calls'),
                'backgroundColor' => '#FF4D4F',
                'filter' => [
                    'key' => 'error_code',
                    'operator' => 'IS NOT NULL',
                ],
            ]
        ];

        if ($resourceId) {
            return [
                (new StackedChart())
                    ->title('Webhook Activity')
                    ->model(WebhookLog::class)
                    ->series($dataSeries)
                    ->options([
                        'uom' => 'month', // available in 'day', 'week', 'month', 'hour'
                        'showTotal' => false,
                        'queryFilter' => [
                            [
                                'key' => 'created_at',
                                'operator' => '>=',
                                'value' => now()
                                    ->startOfDay()
                                    ->subMonths(12)
                                    ->format('Y-m-d'),
                            ],
                            [
                                'key' => 'webhook_id',
                                'operator' => '=',
                                'value' => $resourceId,
                            ]
                        ],
                    ])
                    ->onlyOnDetail()
                    ->width('2/3'),
            ];
        }

        return [
            (new StackedChart())
                ->title('Webhook Activity')
                ->model(WebhookLog::class)
                ->series($dataSeries)
                ->options([
                    'uom' => 'month', // available in 'day', 'week', 'month', 'hour'
                    'showTotal' => false,
                    'queryFilter' => [
                        [
                            'key' => 'created_at',
                            'operator' => '>=',
                            'value' => now()
                                ->startOfDay()
                                ->subMonths(12)
                                ->format('Y-m-d'),
                        ]
                    ],
                ])
                ->width('2/3')
        ];
    }
}
