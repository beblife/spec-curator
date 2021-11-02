<?php

declare(strict_types=1);

namespace Beblife\SpecCurator\Curators;

use Beblife\SpecCurator\Spec;

interface Curator
{
    public function curate(Spec $spec): Spec;
}
