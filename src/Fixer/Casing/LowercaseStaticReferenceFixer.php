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

namespace PhpCsFixer\Fixer\Casing;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Kuba Werłos <werlos@gmail.com>
 */
final class LowercaseStaticReferenceFixer extends AbstractFixer
{
    private const PREV_TOKEN_KINDS = [
        T_CASE,
        T_INSTANCEOF,
        T_NEW,
        T_PRIVATE,
        T_PROTECTED,
        T_PUBLIC,
        CT::T_NULLABLE_TYPE,
        CT::T_TYPE_COLON,
        CT::T_TYPE_ALTERNATION,
    ];

    private const NEXT_TOKEN_KINDS = [
        T_DOUBLE_COLON,
        T_VARIABLE,
        CT::T_TYPE_ALTERNATION,
    ];

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Class static references `self`, `static` and `parent` MUST be in lower case.',
            [
                new CodeSample('<?php
class Foo extends Bar
{
    public function baz1()
    {
        return STATIC::baz2();
    }

    public function baz2($x)
    {
        return $x instanceof Self;
    }

    public function baz3(PaRent $x)
    {
        return true;
    }
}
'),
                new CodeSample(
                    '<?php
class Foo extends Bar
{
    public function baz(?self $x) : SELF
    {
        return false;
    }
}
'
                ),
            ]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([T_STATIC, T_STRING]);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            if (!$token->equalsAny([[T_STRING, 'self'], [T_STATIC, 'static'], [T_STRING, 'parent']], false)) {
                continue;
            }

            $newContent = strtolower($token->getContent());
            if ($token->getContent() === $newContent) {
                continue; // case is already correct
            }

            $prevIndex = $tokens->getPrevMeaningfulToken($index);
            if (!$tokens[$prevIndex]->isGivenKind(self::PREV_TOKEN_KINDS) && !$tokens[$prevIndex]->equalsAny(['(', '{'])) {
                continue;
            }

            $nextIndex = $tokens->getNextMeaningfulToken($index);
            if (!$tokens[$nextIndex]->isGivenKind(self::NEXT_TOKEN_KINDS) && !$tokens[$nextIndex]->equalsAny(['(', ')', '{'])) {
                continue;
            }

            if ($tokens[$prevIndex]->equals('(') && $tokens[$nextIndex]->equals(')')) {
                continue;
            }

            if ('static' === $newContent && $tokens[$nextIndex]->isGivenKind(T_VARIABLE)) {
                continue;
            }

            $tokens[$index] = new Token([$token->getId(), $newContent]);
        }
    }
}
