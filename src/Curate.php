<?php

declare(strict_types=1);

namespace Beblife\SpecCurator;

use Beblife\SpecCurator\Curators\Curator;
use Beblife\SpecCurator\Curators\Paths;
use Beblife\SpecCurator\Curators\Security;
use Beblife\SpecCurator\Curators\Servers;
use Beblife\SpecCurator\Curators\WithoutTags;
use cebe\openapi\spec\Reference;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

final class Curate
{
    private Spec $curated;

    /**
     * @var Curator[]
     */
    private array $curators;

    private function __construct(Spec $spec)
    {
        $this->curated = clone $spec;
        $this->curators = [];
    }

    public static function fromSpec(Spec $spec): self
    {
        return new self($spec);
    }

    public function withCurator(Curator $curator): self
    {
        array_push($this->curators, $curator);

        return $this;
    }

    public function withoutTags(): self
    {
        $this->withCurator(new WithoutTags());

        return $this;
    }

    public function servers(array $servers): self
    {
        $this->withCurator(new Servers($servers));

        return $this;
    }

    public function paths(array $paths): self
    {
        $this->withCurator(new Paths($paths));

        return $this;
    }

    public function security(array $securities): self
    {
        $this->withCurator(new Security($securities));

        return $this;
    }

    public function toCuratedSpec(): Spec
    {
        $this->curated = array_reduce($this->curators, function (Spec $spec, Curator $curator) {
            return $curator->curate($spec);
        }, $this->curated);

        $this->removeUnusedReferences();

        return $this->curated;
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
