<?php

declare(strict_types=1);

namespace Beblife\SpecCurator\Curators;

use Beblife\SpecCurator\Spec;
use cebe\openapi\spec\Reference;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

final class NoUnusedReferences implements Curator
{
    private array $usedReferences = [];

    public function curate(Spec $spec): Spec
    {
        $this->determineUsedReferences($spec);

        $spec = $this->removeUnusedFromComponents($spec, 'responses');
        $spec = $this->removeUnusedFromComponents($spec, 'schemas');

        return $spec;
    }

    private function determineUsedReferences(Spec $spec): void
    {
        $specProperties =  new RecursiveIteratorIterator(new RecursiveArrayIterator($spec->toArray()));

        foreach ($specProperties as $key => $value) {
            if ($key === '$ref') {
                array_push($this->usedReferences, new Reference([
                    '$ref' => $value,
                ]));
            }

            if (is_string($value) && substr($value, 0, 2) === '#/' && strpos(serialize($spec->paths->getPaths()), '"discriminator"')) {
                array_push($this->usedReferences, new Reference([
                    '$ref' => $value,
                ]));
            }
        }

        $this->usedReferences = array_map(function (Reference $ref) {
            [$_, $section, $key] = $ref->getJsonReference()->getJsonPointer()->getPath();

            return [
                'section' => $section,
                'key' => $key,
            ];
        }, $this->usedReferences);

        $this->usedReferences = array_reduce($this->usedReferences, function ($usedReferences, $usedReference) {
            $usedReferences[$usedReference['section']][] = $usedReference['key'];

            return $usedReferences;
        }, [
            'schemas' => [],
            'responses' => [],
        ]);
    }

    private function removeUnusedFromComponents($spec, string $section): Spec
    {
        if (isset($spec->components->{$section}) && count($spec->components->{$section})) {
            $spec->components->{$section} = array_filter($spec->components->{$section}, function ($key) use ($section) {
                return in_array($key, $this->usedReferences[$section], false);
            }, ARRAY_FILTER_USE_KEY);

            if (empty($spec->components->{$section})) {
                unset($spec->components->{$section});
            }
        }

        return $spec;
    }
}
