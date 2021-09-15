<?php

use Beblife\SpecCurator\Spec;

it('can be constructed from valid OpenAPI specification files', function ($path) {
    $instance = Spec::fromFile($path);

    expect($instance)->toBeObject();
})->with([
    'in JSON-format' =>  __DIR__ . '/__fixtures__/spec/valid.json',
    'in YAML-format' =>  __DIR__ . '/__fixtures__/spec/valid.yaml',
]);

it('throws an execption when constructed with invalid files', function () {
    Spec::fromFile(__DIR__ . '/__fixtures__/spec/invalid.txt');
})->throws(
    InvalidArgumentException::class,
    'Only valid OpenAPI JSON or YAML files are supported.'
);
