<?php

namespace Pagzi\NovaWebhooks\Library;

use Pagzi\NovaWebhooks\Contracts\WebhookModel;
use Pagzi\NovaWebhooks\Enums\ModelEvents;
use Pagzi\NovaWebhooks\Traits\DeletedWebhook;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Pagzi\NovaWebhooks\Traits\CreatedWebhook;
use Pagzi\NovaWebhooks\Traits\UpdatedWebhook;

class ModelUtility
{
    /**
     * @return WebhookModel[]
     */
    public static function availableModelActions(): array
    {
        $models = [];
        $availableModels = self::getModels();

        foreach ($availableModels as $class) {
            $classes = class_uses_recursive($class);
            $classes = array_keys($classes);

            $model = new WebhookModel($class);

            if (in_array(CreatedWebhook::class, $classes)) {
                $model->addAction(ModelEvents::Created);
            }
            if (in_array(UpdatedWebhook::class, $classes)) {
                $model->addAction(ModelEvents::Updated);
            }
            if (in_array(DeletedWebhook::class, $classes)) {
                $model->addAction(ModelEvents::Deleted);
            }

            $models[] = $model;
        }

        return $models;
    }

    /**
     * @param array $settings
     * @return WebhookModel[]
     */
    public static function parseSavedList(array $settings): array
    {
        $models = [];

        foreach ($settings as $action => $selected) {
            if ($selected) {
                $class = \Str::before($action, ':');
                $actionName = \Str::after($action, ':');
                $model = new WebhookModel($class);
                $models[$action] = $model->label.':'.$actionName;
            }
        }

        return $models;
    }

    /**
     * Returns all the available models in the application's namespace
     *
     * @return Collection
     */
    public static function getModels(): Collection
    {
        $modelFiles = File::allFiles(self::path());
        $models = collect($modelFiles)
            ->map(function ($item) {
                $path = $item->getRelativePathName();
                return sprintf('\%s%s',
                    self::namespace(),
                    str_replace('/', '\\', substr($path, 0, strrpos($path, '.'))));
            })
            ->filter(function ($class) {
                $valid = false;

                if (class_exists($class)) {
                    $reflection = new \ReflectionClass($class);
                    $valid = $reflection->isSubclassOf(Model::class) &&
                        ! $reflection->isAbstract();
                }

                return $valid;
            });

        return $models->values();
    }

    /**
     * Compile the available models and actions to select for the webhook
     *
     * @return array
     */
    public static function fieldArray(): array
    {
        $models = self::availableModelActions();
        $array = [];

        foreach ($models as $model) {
            foreach ($model->actions as $action) {
                $array[$model->actionName($action)] = $model->label($action);
            }
        }

        return collect($array)
            ->sort()
            ->all();
    }

    /**
     * @return string
     */
    private static function path(): string
    {
        if (\App::runningUnitTests()) {
            return __DIR__.'/../../tests/Models';
        }

        return app_path();
    }

    /**
     * @return string
     */
    private static function namespace()
    {
        if (\App::runningUnitTests()) {
            return "Pagzi\NovaWebhooks\Tests\Models\\";
        }

        return Container::getInstance()->getNamespace();
    }
}
