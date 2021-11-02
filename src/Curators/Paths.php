<?php

declare(strict_types=1);

namespace Beblife\SpecCurator\Curators;

use Beblife\SpecCurator\Spec;
use cebe\openapi\spec\Paths as SpecPaths;

final class Paths implements Curator
{
    private array $paths;

    public function __construct(array $paths)
    {
        $this->paths = $paths;
    }

    public function curate(Spec $spec): Spec
    {
        $paths = $this->paths;

        foreach ($paths as $key => $value) {
            if (is_numeric($key)) {
                $name = $value;
                unset($paths[$key]);
            }

            if (substr((string) $key, 0, 1) === '/') {
                $name = $key;
            }

            $path = $spec->paths->getPath($name);

            if (is_array($value)) {
                $operationsToRemove = array_map('strtolower', array_diff(['GET', 'POST', 'PATCH', 'PUT', 'DELETE'], $value));

                foreach ($operationsToRemove as $operation) {
                    unset($path->$operation);
                }
            }

            $paths[$name] = $path;
        }

        $spec->paths = new SpecPaths($paths);

        return $spec;
    }
}
