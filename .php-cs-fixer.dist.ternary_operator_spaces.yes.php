<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$config = require __DIR__.'/.php-cs-fixer.dist.php';

$config->setRules(array_merge($config->getRules(), [
    'ternary_operator_spaces' => false,
]));

return $config;
