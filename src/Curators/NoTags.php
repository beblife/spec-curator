<?php

declare(strict_types=1);

namespace Beblife\SpecCurator\Curators;

use Beblife\SpecCurator\Spec;

final class NoTags implements Curator
{
    public function curate(Spec $spec): Spec
    {
        $spec->tags = [];

        foreach ($spec->paths as $path) {
            foreach ($path->getOperations() as $operation) {
                unset($operation->tags);
            }
        }

        return $spec;
    }
}
