<?php

declare(strict_types=1);

namespace Beblife\SpecCurator\Curators;

use Beblife\SpecCurator\Spec;
use Webmozart\Assert\Assert;

final class Security implements Curator
{
    private array $names;

    public function __construct(array $names)
    {
        Assert::notEmpty($names, 'Please provide at least one security.');

        $this->names = $names;
    }

    public function curate(Spec $spec): Spec
    {
        $spec->security = $this->filterByName($spec->security);

        $spec->components->securitySchemes = array_filter($spec->components->securitySchemes, function ($security) {
            return in_array($security, $this->names, true);
        }, ARRAY_FILTER_USE_KEY);

        foreach ($spec->paths->getPaths() as $pathName => $path) {
            foreach ($path->getOperations() as $method => $operation) {
                if (empty($operation->security ?? [])) {
                    continue;
                }

                $operation->security = $this->filterByName($operation->security);
                $path->{$method} = $operation;

                if (empty($operation->security)) {
                    $spec->paths->removePath($pathName);
                }
            }
        }

        return $spec;
    }

    private function filterByName(array $names): array
    {
        return array_values(array_filter($names, function ($security) {
            foreach ($this->names as $key => $value) {
                if (is_numeric($key)) {
                    $name = $value;
                }

                if (is_null($security->{$name} ?? null)) {
                    continue;
                }

                return true;
            }
        }));
    }
}
