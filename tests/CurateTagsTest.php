<?php

use Beblife\SpecCurator\Curate;
use Beblife\SpecCurator\Spec;

it('can curate to remove all tags', function () {
    $spec = Spec::fromFile($this->getFixturePath('curate/no-tags/all.json'));
    $curate = Curate::fromSpec($spec);

    $curated = $curate->withoutTags();

    expect($curated->toCuratedSpec()->toString())->toEqual($this->getFixtureContents('curate/no-tags/curated.json'));
});
