<?php

use Beblife\SpecCurator\Curate;
use Beblife\SpecCurator\Spec;

it('can curate paths with all operations', function () {
    $spec = Spec::fromFile($this->getFixturePath('curate/paths/all.json'));
    $curate = Curate::fromSpec($spec);

    $curated = $curate->paths([
        '/comments',
    ]);

    expect($curated->toCuratedSpec()->toString())->toEqual($this->getFixtureContents('curate/paths/curated.json'));
});

it('can curate paths with only the specified operations', function () {
    $spec = Spec::fromFile($this->getFixturePath('curate/path-operations/all.json'));
    $curate = Curate::fromSpec($spec);

    $curated = $curate->paths([
        '/comments' => ['GET'],
    ]);

    expect($curated->toCuratedSpec()->toString())->toEqual($this->getFixtureContents('curate/path-operations/curated.json'));
});

it('removes unused response components when curating paths', function () {
    $spec = Spec::fromFile($this->getFixturePath('curate/path-response-components/all.json'));
    $curate = Curate::fromSpec($spec);

    $curated = $curate->paths([
        '/comments' => ['GET'],
    ]);

    expect($curated->toCuratedSpec()->toString())->toEqual($this->getFixtureContents('curate/path-response-components/curated.json'));
});

it('removes all response components when none are used in curated paths', function () {
    $spec = Spec::fromFile($this->getFixturePath('curate/no-path-response-components/all.json'));
    $curate = Curate::fromSpec($spec);

    $curated = $curate->paths([
        '/comments' => ['GET'],
    ]);

    expect($curated->toCuratedSpec()->toString())->toEqual($this->getFixtureContents('curate/no-path-response-components/curated.json'));
});

it('removes unused schema components when curating paths', function () {
    $spec = Spec::fromFile($this->getFixturePath('curate/path-schema-components/all.json'));
    $curate = Curate::fromSpec($spec);

    $curated = $curate->paths([
        '/packages',
        '/tags',
    ]);

    expect($curated->toCuratedSpec()->toString())->toEqual($this->getFixtureContents('curate/path-schema-components/curated.json'));
});

it('removes all schema components when none are used in curated paths', function () {
    $spec = Spec::fromFile($this->getFixturePath('curate/no-path-schema-components/all.json'));
    $curate = Curate::fromSpec($spec);

    $curated = $curate->paths([
        '/tags',
    ]);

    expect($curated->toCuratedSpec()->toString())->toEqual($this->getFixtureContents('curate/no-path-schema-components/curated.json'));
});

it('can curate schemas that use discriminator with component references', function () {
    $spec = Spec::fromFile($this->getFixturePath('curate/path-discriminator-components/all.json'));
    $curate = Curate::fromSpec($spec);

    $curated = $curate->paths([
        '/packages',
    ]);

    expect($curated->toCuratedSpec()->toString())->toEqual($this->getFixtureContents('curate/path-discriminator-components/curated.json'));
});
