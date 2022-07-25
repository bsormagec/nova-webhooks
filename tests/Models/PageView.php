<?php

namespace Pagzi\NovaWebhooks\Tests\Models;

use Pagzi\NovaWebhooks\Tests\Database\Factories\PageViewFactory;
use Pagzi\NovaWebhooks\Traits\AllWebhooks;
use Pagzi\NovaWebhooks\Traits\ShouldQueueWebhook;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PageView extends \Illuminate\Database\Eloquent\Model
{
    use HasFactory;
    use AllWebhooks;
    use ShouldQueueWebhook;

    protected $fillable = [
        'name',
        'number_of_views',
    ];

    public static function boot()
    {
        parent::boot();
    }

    protected static function newFactory()
    {
        return PageViewFactory::new();
    }
}
