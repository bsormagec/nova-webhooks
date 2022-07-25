<?php

namespace Pagzi\NovaWebhooks\Tests\Models\Api;

use Pagzi\NovaWebhooks\Tests\Database\Factories\Api\PageLikeFactory;
use Pagzi\NovaWebhooks\Tests\Resources\PageLikeResource;
use Pagzi\NovaWebhooks\Traits\DeletedWebhook;
use Pagzi\NovaWebhooks\Traits\UpdatedWebhook;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PageLike extends \Illuminate\Database\Eloquent\Model
{
    use HasFactory;
    use UpdatedWebhook;
    use DeletedWebhook;

    protected $fillable = [
        'page',
    ];

    public static function boot()
    {
        parent::boot();
    }

    protected static function newFactory()
    {
        return PageLikeFactory::new();
    }

    protected static function updatedWebhookPayload($model)
    {
        return new PageLikeResource($model);
    }

    protected static function deletedWebhookPayload($model)
    {
        return new PageLikeResource($model);
    }
}
