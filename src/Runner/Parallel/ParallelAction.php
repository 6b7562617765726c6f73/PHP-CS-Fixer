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

namespace PhpCsFixer\Runner\Parallel;

/**
 * @author Greg Korba <greg@codito.dev>
 *
 * @internal
 */
final class ParallelAction
{
    // Actions executed by the runner (main process)
    public const string RUNNER_REQUEST_ANALYSIS = 'requestAnalysis';
    public const string RUNNER_THANK_YOU = 'thankYou';

    // Actions executed by the worker
    public const string WORKER_ERROR_REPORT = 'errorReport';
    public const string WORKER_GET_FILE_CHUNK = 'getFileChunk';
    public const string WORKER_HELLO = 'hello';
    public const string WORKER_RESULT = 'result';

    private function __construct() {}
}
