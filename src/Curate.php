<?php

declare(strict_types=1);

namespace Beblife\SpecCurator;

use Beblife\SpecCurator\Curators\Paths;
use Beblife\SpecCurator\Curators\Servers;
use Beblife\SpecCurator\Curators\WithoutTags;
use cebe\openapi\spec\Reference;
use InvalidArgumentException;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

final class Curate
{
    private Spec $curated;

    private function __construct(Spec $spec)
    {
        $this->curated = clone $spec;
    }

    public static function fromSpec(Spec $spec): self
    {
        return new self($spec);
    }

    public function withoutTags(): self
    {
        $this->curated = (new WithoutTags())->curate($this->curated);

        return $this;
    }

    /** @throws InvalidArgumentException */
    public function servers(array $servers): self
    {
        $this->ensureAtLeastOneItemInArray($servers, 'server');

        $this->curated = (new Servers($servers))->curate($this->curated);

        return $this;
    }

    /** @throws InvalidArgumentException */
    public function paths(array $paths): self
    {
        $this->ensureAtLeastOneItemInArray($paths, 'path');

        $this->curated = (new Paths($paths))->curate($this->curated);

        return $this;
    }

    /** @throws InvalidArgumentException */
    public function security(array $securities): self
    {
        $this->ensureAtLeastOneItemInArray($securities, 'security');

        $filterByName = function ($security) use ($securities) {
            foreach ($securities as $key => $value) {
                if (is_numeric($key)) {
                    $name = $value;
                }

                if (is_null($security->{$name} ?? null)) {
                    continue;
                }

                return true;
            }
        };

        $this->curated->security = array_values(array_filter($this->curated->security, $filterByName));

        $this->curated->components->securitySchemes = array_filter($this->curated->components->securitySchemes, function ($security) use ($securities) {
            return in_array($security, $securities, true);
        }, ARRAY_FILTER_USE_KEY);

        foreach ($this->curated->paths->getPaths() as $pathName => $path) {
            foreach ($path->getOperations() as $method => $operation) {
                if (empty($operation->security ?? [])) {
                    continue;
                }

                $operation->security = array_values(array_filter($operation->security, $filterByName));
                $path->{$method} = $operation;

                if (empty($operation->security)) {
                    $this->curated->paths->removePath($pathName);
                }
            }
        }

        return $this;
    }

    public function toCuratedSpec(): Spec
    {
        $this->removeUnusedReferences();

        return $this->curated;
    }

    private function ensureAtLeastOneItemInArray(array $items, string $type): void
    {
        if (empty($items)) {
            throw new InvalidArgumentException(sprintf('Please provide at least one %s.', $type));
        }
    }

    private function removeUnusedReferences(): void
    {
        $specProperties =  new RecursiveIteratorIterator(new RecursiveArrayIterator($this->curated->toArray()));

        $usedReferences = [];

        foreach ($specProperties as $key => $value) {
            if ($key === '$ref') {
                array_push($usedReferences, new Reference([
                    '$ref' => $value,
                ]));
            }

            if (is_string($value) && substr($value, 0, 2) === '#/' && strpos(serialize($this->curated->paths->getPaths()), '"discriminator"')) {
                array_push($usedReferences, new Reference([
                    '$ref' => $value,
                ]));
            }
        }

        $usedReferencesWithSection = array_map(function (Reference $ref) {
            [$_, $section, $key] = $ref->getJsonReference()->getJsonPointer()->getPath();

            return [
                'section' => $section,
                'key' => $key,
            ];
        }, $usedReferences);

        $groupedReferences = array_reduce($usedReferencesWithSection, function ($usedReferences, $usedReference) {
            $usedReferences[$usedReference['section']][] = $usedReference['key'];

            return $usedReferences;
        }, [
            'schemas' => [],
            'responses' => [],
        ]);

        if (isset($this->curated->components->responses) && count($this->curated->components->responses)) {
            $this->curated->components->responses = array_filter($this->curated->components->responses, function ($key) use ($groupedReferences) {
                return in_array($key, $groupedReferences['responses'], false);
            }, ARRAY_FILTER_USE_KEY);

            if (empty($this->curated->components->responses)) {
                unset($this->curated->components->responses);
            }
        }

        if (isset($this->curated->components->schemas) && count($this->curated->components->schemas)) {
            $this->curated->components->schemas = array_filter($this->curated->components->schemas, function ($key) use ($groupedReferences) {
                return in_array($key, $groupedReferences['schemas'], true);
            }, ARRAY_FILTER_USE_KEY);

            if (empty($this->curated->components->schemas)) {
                unset($this->curated->components->schemas);
            }
        }
    }
}
