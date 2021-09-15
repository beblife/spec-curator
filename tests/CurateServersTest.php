<?php

use Beblife\SpecCurator\Curate;
use Beblife\SpecCurator\Spec;

beforeEach(function () {
});

it('can curate servers by their url', function () {
    $spec = Spec::fromFile($this->getFixturePath('curate/servers/all.json'));
    $curate = Curate::fromSpec($spec);

    $curated = $curate->servers([
        'https://production.packages.com/api',
    ]);

    expect($curated->toCuratedSpec()->toString())->toEqual($this->getFixtureContents('curate/servers/curated.json'));
});
