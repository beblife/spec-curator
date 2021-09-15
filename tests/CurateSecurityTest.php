<?php

use Beblife\SpecCurator\Curate;
use Beblife\SpecCurator\Spec;

it('can curate security schemes', function () {
    $spec = Spec::fromFile($this->getFixturePath('curate/security/all.json'));
    $curate = Curate::fromSpec($spec);

    $curated = $curate->security([
        'User Access Token',
    ]);

    expect($curated->toCuratedSpec()->toString())->toEqual($this->getFixtureContents('curate/security/curated.json'));
});
