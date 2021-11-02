<?php

declare(strict_types=1);

namespace Beblife\SpecCurator\Curators;

use Beblife\SpecCurator\Spec;
use Webmozart\Assert\Assert;

final class Servers implements Curator
{
    private array $servers;

    public function __construct(array $servers)
    {
        Assert::notEmpty($servers, 'Please provide at least one server.');

        $this->servers = $servers;
    }

    public function curate(Spec $spec): Spec
    {
        $spec->servers = $this->filterByUrl($spec->servers);

        foreach ($spec->paths as $name => $path) {
            if (empty($path->servers)) {
                continue;
            }

            $path->servers = $this->filterByUrl($path->servers);

            if (empty($path->servers)) {
                $spec->paths->removePath($name);
            }
        }

        return $spec;
    }

    private function filterByUrl(array $servers): array
    {
        return array_values(array_filter($servers, function ($server) {
            return in_array($server->url, $this->servers, true);
        }));
    }
}
