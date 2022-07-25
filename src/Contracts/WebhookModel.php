<?php

namespace Pagzi\NovaWebhooks\Contracts;

use Pagzi\NovaWebhooks\Enums\ModelEvents;

class WebhookModel
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $label;

    /**
     * @var string[]
     */
    public $actions = [];

    /**
     * @param string $name
     * @param string $label
     * @param string[] $actions
     */
    public function __construct(string $name, $actions = [])
    {
        $this->name = $name;
        $this->label = method_exists($name, 'webhookLabel') ? $name::webhookLabel() : $name;;
        $this->actions = $actions;
    }

    /**
     * @param string $action
     * @return void
     */
    public function addAction(string $action) : void
    {
        if (ModelEvents::hasValue($action) && !in_array($action, $this->actions)) {
            $this->actions[] = $action;
        }
    }

    /**
     * @param string $action
     * @return string
     */
    public function actionName(string $action) : string
    {
        return $this->name.':'.$action;
    }

    /**
     * @param string $action
     * @return string
     */
    public function label(string $action) : string
    {
        return $this->label.':'.$action;
    }
}
