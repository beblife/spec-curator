<?php

declare(strict_types=1);

use Madewithlove\PhpCsFixer\Config;

require __DIR__ . '/vendor/autoload.php';

return Config::fromFolders([
    __DIR__ . '/src',
])->mergeRules([
    'no_superfluous_phpdoc_tags' => false,
    'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments']],
]);
