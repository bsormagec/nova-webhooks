<?php

namespace Pagzi\NovaWebhooks\Enums;

final class ModelEvents
{
    use EnumTrait;

    public const Created = 'created';
    public const Updated = 'updated';
    public const Deleted = 'deleted';
}
