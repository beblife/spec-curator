<?php

declare(strict_types=1);

namespace Beblife\SpecCurator;

use Beblife\SpecCurator\Curators\Curator;
use Beblife\SpecCurator\Curators\Paths;
use Beblife\SpecCurator\Curators\Security;
use Beblife\SpecCurator\Curators\Servers;
use Beblife\SpecCurator\Curators\WithoutTags;
use Beblife\SpecCurator\Curators\WithoutUnusedReferences;

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
        $this->withCurator(new WithoutUnusedReferences());

        return array_reduce($this->curators, function (Spec $spec, Curator $curator) {
            return $curator->curate($spec);
        }, $this->curated);
    }
}
