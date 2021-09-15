<?php

trait Fixtures
{
    public function getFixturePath(string $path): string
    {
        return __DIR__ . "/__fixtures__/$path";
    }

    public function getFixtureContents(string $path): string
    {
        return trim(file_get_contents($this->getFixturePath($path)) ?: '');
    }
}
