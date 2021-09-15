<?php

declare(strict_types=1);

namespace Beblife\SpecCurator;

use cebe\openapi\spec\OpenApi;
use InvalidArgumentException;
use Stringable;
use Symfony\Component\Yaml\Yaml;
use TypeError;

final class Spec implements Stringable
{
    private OpenApi $spec;

    private function __construct(array $contents)
    {
        $this->spec = new OpenApi($contents);
    }

    public static function fromFile(string $path): self
    {
        $contents = file_get_contents($path);

        try {
            return new self(json_decode($contents, true) ?? Yaml::parse($contents, Yaml::DUMP_OBJECT));
        } catch (TypeError $e) {
            throw new InvalidArgumentException('Only valid OpenAPI JSON or YAML files are supported.');
        }
    }

    public function resolveReferences(): self
    {
        $this->spec->resolveReferences();

        return $this;
    }

    public function toString(): string
    {
        return json_encode($this->spec->getSerializableData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function toArray(): array
    {
        return json_decode($this->toString(), true);
    }

    /**
     * @return mixed
     */
    public function __get(string $property)
    {
        return $this->spec->{$property};
    }

    public function __set($property, $value): void
    {
        $this->spec->{$property} = $value;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
