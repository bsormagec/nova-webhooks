<?php

namespace Pagzi\NovaWebhooks\Traits;

/**
 * Enables all the available webhooks for this model
 * @package pagzi/nova-webhooks
 */
trait AllWebhooks
{
    use CreatedWebhook;
    use DeletedWebhook;
    use UpdatedWebhook;
}
