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

namespace PhpCsFixer\Tests\Fixer\LanguageConstruct;

use PhpCsFixer\ConfigurationException\InvalidFixerConfigurationException;
use PhpCsFixer\Tests\Test\AbstractFixerTestCase;

/**
 * @internal
 *
 * @covers \PhpCsFixer\Fixer\LanguageConstruct\FunctionToConstantFixer
 */
final class FunctionToConstantFixerTest extends AbstractFixerTestCase
{
    /**
     * @param array<string, mixed> $config
     *
     * @dataProvider provideFixCases
     */
    public function testFix(string $expected, ?string $input = null, array $config = []): void
    {
        $this->fixer->configure($config);
        $this->doTest($expected, $input);
    }

    public static function provideFixCases(): iterable
    {
        yield 'Minimal case, alternative casing, alternative statement end.' => [
            '<?php echo PHP_VERSION?>',
            '<?php echo PHPversion()?>',
        ];

        yield 'With embedded comment.' => [
            '<?php echo PHP_VERSION/**/?>',
            '<?php echo phpversion(/**/)?>',
        ];

        yield 'With white space.' => [
            '<?php echo PHP_VERSION      ;',
            '<?php echo phpversion  (  )  ;',
        ];

        yield 'With multi line whitespace.' => [
            '<?php echo
                PHP_VERSION
                '.'
                '.'
                ;',
            '<?php echo
                phpversion
                (
                )
                ;',
        ];

        yield 'Global namespaced.' => [
            '<?php echo \PHP_VERSION;',
            '<?php echo \phpversion();',
        ];

        yield 'Wrong number of arguments.' => [
            '<?php phpversion($a);',
        ];

        yield 'Wrong namespace.' => [
            '<?php A\B\phpversion();',
        ];

        yield 'Class creating.' => [
            '<?php new phpversion();',
        ];

        yield 'Class static method call.' => [
            '<?php A::phpversion();',
        ];

        yield 'Class method call.' => [
            '<?php $a->phpversion();',
        ];

        yield 'Overridden function.' => [
            '<?php if (!function_exists("phpversion")){function phpversion(){}}?>',
        ];

        yield 'phpversion only' => [
            '<?php echo PHP_VERSION; echo php_sapi_name(); echo pi();',
            '<?php echo phpversion(); echo php_sapi_name(); echo pi();',
            ['functions' => ['phpversion']],
        ];

        yield 'php_sapi_name only' => [
            '<?php echo phpversion(); echo PHP_SAPI; echo pi();',
            '<?php echo phpversion(); echo php_sapi_name(); echo pi();',
            ['functions' => ['php_sapi_name']],
        ];

        yield 'php_sapi_name in conditional' => [
            '<?php if ("cli" === PHP_SAPI && $a){ echo 123;}',
            '<?php if ("cli" === php_sapi_name() && $a){ echo 123;}',
            ['functions' => ['php_sapi_name']],
        ];

        yield 'pi only' => [
            '<?php echo phpversion(); echo php_sapi_name(); echo M_PI;',
            '<?php echo phpversion(); echo php_sapi_name(); echo pi();',
            ['functions' => ['pi']],
        ];

        yield 'multi line pi' => [
            '<?php
$a =
    $b
    || $c < M_PI
;',
            '<?php
$a =
    $b
    || $c < pi()
;',
            ['functions' => ['pi']],
        ];

        yield 'phpversion and pi' => [
            '<?php echo PHP_VERSION; echo php_sapi_name(); echo M_PI;',
            '<?php echo phpversion(); echo php_sapi_name(); echo M_PI;',
            ['functions' => ['pi', 'phpversion']],
        ];

        yield 'diff argument count than native allows' => [
            '<?php
                    echo phpversion(1);
                    echo php_sapi_name(1,2);
                    echo pi(1);
                ',
        ];

        yield 'get_class => T_CLASS' => [
            '<?php
                    class A
                    {
                        public function echoClassName($notMe)
                        {
                            echo get_class($notMe);
                            echo self::class/** 1 *//* 2 */;
                            echo self::class;
                        }
                    }

                    class B
                    {
                        use A;
                    }
                ',
            '<?php
                    class A
                    {
                        public function echoClassName($notMe)
                        {
                            echo get_class($notMe);
                            echo get_class(/** 1 *//* 2 */);
                            echo GET_Class();
                        }
                    }

                    class B
                    {
                        use A;
                    }
                ',
        ];

        yield 'get_class with leading backslash' => [
            '<?php self::class;',
            '<?php \get_class();',
        ];

        yield [
            '<?php class A { function B(){ echo static::class; }}',
            '<?php class A { function B(){ echo get_called_class(); }}',
            ['functions' => ['get_called_class']],
        ];

        yield [
            '<?php class A { function B(){
echo#.
#0
static::class#1
#2
#3
#4
#5
#6
;#7
}}
                ',
            '<?php class A { function B(){
echo#.
#0
get_called_class#1
#2
(#3
#4
)#5
#6
;#7
}}
                ',
            ['functions' => ['get_called_class']],
        ];

        yield 'get_called_class with leading backslash' => [
            '<?php class A { function B(){echo static::class; }}',
            '<?php class A { function B(){echo \get_called_class(); }}',
            ['functions' => ['get_called_class']],
        ];

        yield 'get_called_class overridden' => [
            '<?php echo get_called_class(1);',
            null,
            ['functions' => ['get_called_class']],
        ];

        yield [
            '<?php class Foo{ public function Bar(){ echo static::class  ; }}',
            '<?php class Foo{ public function Bar(){ echo get_class( $This ); }}',
            ['functions' => ['get_class_this']],
        ];

        yield [
            '<?php class Foo{ public function Bar(){ echo static::class; get_class(1, 2); get_class($a); get_class($a, $b);}}',
            '<?php class Foo{ public function Bar(){ echo get_class($this); get_class(1, 2); get_class($a); get_class($a, $b);}}',
            ['functions' => ['get_class_this']],
        ];

        yield [
            '<?php class Foo{ public function Bar(){ echo static::class /* 0 */  /* 1 */ ;}}',
            '<?php class Foo{ public function Bar(){ echo \get_class( /* 0 */ $this /* 1 */ );}}',
            ['functions' => ['get_class_this']],
        ];

        yield [
            '<?php class Foo{ public function Bar(){ echo static::class; echo self::class; }}',
            '<?php class Foo{ public function Bar(){ echo \get_class((($this))); echo get_class(); }}',
            ['functions' => ['get_class_this', 'get_class']],
        ];

        yield [
            '<?php
                    class Foo{ public function Bar(){ echo $reflection = new \ReflectionClass(get_class($this->extension)); }}
                    class Foo{ public function Bar(){ echo $reflection = new \ReflectionClass(get_class($this() )); }}
                ',
            null,
            ['functions' => ['get_class_this']],
        ];

        yield [
            "<?php namespace Foo;\nfunction &PHPversion(){}",
        ];
    }

    /**
     * @param array<string, mixed> $config
     *
     * @dataProvider provideInvalidConfigurationKeysCases
     */
    public function testInvalidConfigurationKeys(array $config): void
    {
        $this->expectException(InvalidFixerConfigurationException::class);
        $this->expectExceptionMessageMatches('#^\[function_to_constant\] Invalid configuration: The option "functions" with value array is invalid\.$#');

        $this->fixer->configure($config);
    }

    public static function provideInvalidConfigurationKeysCases(): iterable
    {
        yield [['functions' => ['a']]];

        yield [['functions' => [false => 1]]];

        yield [['functions' => ['abc' => true]]];
    }

    public function testInvalidConfigurationValue(): void
    {
        $this->expectException(InvalidFixerConfigurationException::class);
        $this->expectExceptionMessageMatches('#^\[function_to_constant\] Invalid configuration: The option "0" does not exist\. Defined options are: "functions"\.$#');

        // @phpstan-ignore-next-line
        $this->fixer->configure(['pi123']);
    }

    /**
     * @dataProvider provideFix81Cases
     *
     * @requires PHP 8.1
     */
    public function testFix81(string $expected, ?string $input = null): void
    {
        $this->doTest($expected, $input);
    }

    public static function provideFix81Cases(): iterable
    {
        yield 'first callable class' => [
            '<?php $a = get_class(...);',
        ];
    }
}
