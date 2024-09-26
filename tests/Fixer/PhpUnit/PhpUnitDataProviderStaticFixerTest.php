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

namespace PhpCsFixer\Tests\Fixer\PhpUnit;

use PhpCsFixer\Tests\Test\AbstractFixerTestCase;

/**
 * @internal
 *
 * @covers \PhpCsFixer\Fixer\PhpUnit\PhpUnitDataProviderStaticFixer
 *
 * @extends AbstractFixerTestCase<\PhpCsFixer\Fixer\PhpUnit\PhpUnitDataProviderStaticFixer>
 *
 * @phpstan-import-type _AutogeneratedInputConfiguration from \PhpCsFixer\Fixer\PhpUnit\PhpUnitDataProviderStaticFixer
 */
final class PhpUnitDataProviderStaticFixerTest extends AbstractFixerTestCase
{
    /**
     * @param _AutogeneratedInputConfiguration $configuration
     *
     * @dataProvider provideFixCases
     */
    public function testFix(string $expected, ?string $input = null, array $configuration = []): void
    {
        $this->fixer->configure($configuration);
        $this->doTest($expected, $input);
    }

    /**
     * @return iterable<array{0: string, 1?: null|string, 2?: array<string, bool>}>
     */
    public static function provideFixCases(): iterable
    {
        yield 'do not fix when containing dynamic calls by default' => [
            '<?php
class FooTest extends TestCase {
    /**
     * @dataProvider provideFoo1Cases
     */
    public function testFoo1() {}
    public function provideFoo1Cases() { $this->init(); }
}',
        ];

        yield 'fix single' => [
            '<?php
class FooTest extends TestCase {
    /**
     * @dataProvider provideFooCases
     */
    public function testFoo() {}
    public static function provideFooCases() { $x->getData(); }
}',
            '<?php
class FooTest extends TestCase {
    /**
     * @dataProvider provideFooCases
     */
    public function testFoo() {}
    public function provideFooCases() { $x->getData(); }
}',
        ];

        yield 'fix multiple' => [
            '<?php
class FooTest extends TestCase {
    /** @dataProvider provider1 */
    public function testFoo1() {}
    /** @dataProvider provider2 */
    public function testFoo2() {}
    /** @dataProvider provider3 */
    public function testFoo3() {}
    /** @dataProvider provider4 */
    public function testFoo4() {}
    public static function provider1() {}
    public function provider2() { $this->init(); }
    public static function provider3() {}
    public static function provider4() {}
}',
            '<?php
class FooTest extends TestCase {
    /** @dataProvider provider1 */
    public function testFoo1() {}
    /** @dataProvider provider2 */
    public function testFoo2() {}
    /** @dataProvider provider3 */
    public function testFoo3() {}
    /** @dataProvider provider4 */
    public function testFoo4() {}
    public function provider1() {}
    public function provider2() { $this->init(); }
    public function provider3() {}
    public static function provider4() {}
}',
        ];

        yield 'fix with multilines' => [
            '<?php
class FooTest extends TestCase {
    /**
     * @dataProvider provideFooCases
     */
    public function testFoo() {}
    public
        static function
            provideFooCases() { $x->getData(); }
}',
            '<?php
class FooTest extends TestCase {
    /**
     * @dataProvider provideFooCases
     */
    public function testFoo() {}
    public
        function
            provideFooCases() { $x->getData(); }
}',
        ];

        yield 'fix when data provider is abstract' => [
            '<?php
abstract class FooTest extends TestCase {
    /**
     * @dataProvider provideFooCases
     */
    public function testFoo() {}
    abstract public static function provideFooCases();
}',
            '<?php
abstract class FooTest extends TestCase {
    /**
     * @dataProvider provideFooCases
     */
    public function testFoo() {}
    abstract public function provideFooCases();
}',
        ];

        yield 'fix when containing dynamic calls and with `force` disabled' => [
            '<?php
class FooTest extends TestCase {
    /**
     * @dataProvider provideFooCases1
     * @dataProvider provideFooCases2
     */
    public function testFoo() {}
    public function provideFooCases1() { return $this->getFoo(); }
    public static function provideFooCases2() { /* no dynamic calls */ }
}',
            '<?php
class FooTest extends TestCase {
    /**
     * @dataProvider provideFooCases1
     * @dataProvider provideFooCases2
     */
    public function testFoo() {}
    public function provideFooCases1() { return $this->getFoo(); }
    public function provideFooCases2() { /* no dynamic calls */ }
}',
            ['force' => false],
        ];

        yield 'fix when containing dynamic calls and with `force` enabled' => [
            '<?php
class FooTest extends TestCase {
    /**
     * @dataProvider provideFooCases1
     * @dataProvider provideFooCases2
     */
    public function testFoo() {}
    public static function provideFooCases1() { return $this->getFoo(); }
    public static function provideFooCases2() { /* no dynamic calls */ }
}',
            '<?php
class FooTest extends TestCase {
    /**
     * @dataProvider provideFooCases1
     * @dataProvider provideFooCases2
     */
    public function testFoo() {}
    public function provideFooCases1() { return $this->getFoo(); }
    public function provideFooCases2() { /* no dynamic calls */ }
}',
            ['force' => true],
        ];
    }

    /**
     * @requires PHP ^8.0
     *
     * @dataProvider provideFix80Cases
     */
    public function testFix80(string $expected, ?string $input = null): void
    {
        $this->doTest($expected, $input);
    }

    /**
     * @return iterable<array{string, string}>
     */
    public static function provideFix80Cases(): iterable
    {
        yield 'with an attribute between PHPDoc and test method' => [
            <<<'PHP'
                <?php
                class FooTest extends TestCase {
                    /**
                     * @dataProvider provideFooCases
                     */
                    #[CustomAttribute]
                    public function testFoo(): void {}
                    public static function provideFooCases(): iterable {}
                }
                PHP,
            <<<'PHP'
                <?php
                class FooTest extends TestCase {
                    /**
                     * @dataProvider provideFooCases
                     */
                    #[CustomAttribute]
                    public function testFoo(): void {}
                    public function provideFooCases(): iterable {}
                }
                PHP,
        ];

        yield 'with data provider as an attribute' => [
            <<<'PHP'
                <?php
                class FooTest extends TestCase {
                    #[\PHPUnit\Framework\Attributes\DataProvider('addStaticToMe')]
                    public function testFoo(): void {}
                    public static function addStaticToMe() {}
                }
                PHP,
            <<<'PHP'
                <?php
                class FooTest extends TestCase {
                    #[\PHPUnit\Framework\Attributes\DataProvider('addStaticToMe')]
                    public function testFoo(): void {}
                    public function addStaticToMe() {}
                }
                PHP,
        ];

        $withAttributesTemplate = <<<'PHP'
            <?php
            namespace N;
            use PHPUnit\Framework as PphUnitAlias;
            use PHPUnit\Framework\Attributes;
            class FooTest extends TestCase {
                #[\PHPUnit\Framework\Attributes\DataProvider('provider1')]
                #[\PHPUnit\Framework\Attributes\DataProvider('doNotGetFooledByConcatenation' . 'notProvider1')]
                #[PHPUnit\Framework\Attributes\DataProvider('notProvider2')]
                #[
                    \PHPUnit\Framework\Attributes\BackupGlobals(true),
                    \PHPUnit\Framework\Attributes\DataProvider('provider2'),
                    \PHPUnit\Framework\Attributes\Group('foo'),
                ]
                #[Attributes\DataProvider('provider3')]
                #[PphUnitAlias\Attributes\DataProvider('provider4')]
                #[\PHPUnit\Framework\Attributes\DataProvider]
                #[\PHPUnit\Framework\Attributes\DataProvider('provider5')]
                #[\PHPUnit\Framework\Attributes\DataProvider(123)]
                public function testSomething(int $x): void {}
                public%1$s function provider1() {}
                public%1$s function provider2() {}
                public%1$s function provider3() {}
                public%1$s function provider4() {}
                public%1$s function provider5() {}
                public function notProvider1() {}
                public function notProvider2() {}
            }
            PHP;

        yield 'with multiple data providers as an attributes' => [
            \sprintf($withAttributesTemplate, ' static'),
            \sprintf($withAttributesTemplate, ''),
        ];
    }
}
