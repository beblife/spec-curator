<?php

declare(strict_types=1);

namespace Beblife\SpecCurator\Support;

trait ensureAtLeastOneItemInArray
{
    private function ensureAtLeastOneItemInArray(array $items, string $type): void
    {
        if (empty($items)) {
            throw new InvalidArgumentException(sprintf('Please provide at least one %s.', $type));
        }
    }
}
