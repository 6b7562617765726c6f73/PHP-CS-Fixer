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

namespace PhpCsFixer\Tokenizer;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class CT
{
    public const int T_ARRAY_INDEX_CURLY_BRACE_CLOSE = 10_001;
    public const int T_ARRAY_INDEX_CURLY_BRACE_OPEN = 10_002;
    public const int T_ARRAY_SQUARE_BRACE_CLOSE = 10_003;
    public const int T_ARRAY_SQUARE_BRACE_OPEN = 10_004;
    public const int T_ARRAY_TYPEHINT = 10_005;
    public const int T_BRACE_CLASS_INSTANTIATION_CLOSE = 10_006;
    public const int T_BRACE_CLASS_INSTANTIATION_OPEN = 10_007;
    public const int T_CLASS_CONSTANT = 10_008;
    public const int T_CONST_IMPORT = 10_009;
    public const int T_CURLY_CLOSE = 10_010;
    public const int T_DESTRUCTURING_SQUARE_BRACE_CLOSE = 10_011;
    public const int T_DESTRUCTURING_SQUARE_BRACE_OPEN = 10_012;
    public const int T_DOLLAR_CLOSE_CURLY_BRACES = 10_013;
    public const int T_DYNAMIC_PROP_BRACE_CLOSE = 10_014;
    public const int T_DYNAMIC_PROP_BRACE_OPEN = 10_015;
    public const int T_DYNAMIC_VAR_BRACE_CLOSE = 10_016;
    public const int T_DYNAMIC_VAR_BRACE_OPEN = 10_017;
    public const int T_FUNCTION_IMPORT = 10_018;
    public const int T_GROUP_IMPORT_BRACE_CLOSE = 10_019;
    public const int T_GROUP_IMPORT_BRACE_OPEN = 10_020;
    public const int T_NAMESPACE_OPERATOR = 10_021;
    public const int T_NULLABLE_TYPE = 10_022;
    public const int T_RETURN_REF = 10_023;
    public const int T_TYPE_ALTERNATION = 10_024;
    public const int T_TYPE_COLON = 10_025;
    public const int T_USE_LAMBDA = 10_026;
    public const int T_USE_TRAIT = 10_027;
    public const int T_CONSTRUCTOR_PROPERTY_PROMOTION_PUBLIC = 10_028;
    public const int T_CONSTRUCTOR_PROPERTY_PROMOTION_PROTECTED = 10_029;
    public const int T_CONSTRUCTOR_PROPERTY_PROMOTION_PRIVATE = 10_030;
    public const int T_ATTRIBUTE_CLOSE = 10_031;
    public const int T_NAMED_ARGUMENT_NAME = 10_032;
    public const int T_NAMED_ARGUMENT_COLON = 10_033;
    public const int T_FIRST_CLASS_CALLABLE = 10_034;
    public const int T_TYPE_INTERSECTION = 10_035;
    public const int T_DISJUNCTIVE_NORMAL_FORM_TYPE_PARENTHESIS_OPEN = 10_036;
    public const int T_DISJUNCTIVE_NORMAL_FORM_TYPE_PARENTHESIS_CLOSE = 10_037;
    public const int T_DYNAMIC_CLASS_CONSTANT_FETCH_CURLY_BRACE_OPEN = 10_038;
    public const int T_DYNAMIC_CLASS_CONSTANT_FETCH_CURLY_BRACE_CLOSE = 10_039;
    public const int T_PROPERTY_HOOK_BRACE_OPEN = 10_040;
    public const int T_PROPERTY_HOOK_BRACE_CLOSE = 10_041;

    private function __construct() {}

    /**
     * Get name for custom token.
     *
     * @param int $value custom token value
     *
     * @return non-empty-string
     */
    public static function getName(int $value): string
    {
        if (!self::has($value)) {
            throw new \InvalidArgumentException(\sprintf('No custom token was found for "%s".', $value));
        }

        $tokens = self::getMapById();

        \assert(isset($tokens[$value]));

        return 'CT::'.$tokens[$value];
    }

    /**
     * Check if given custom token exists.
     *
     * @param int $value custom token value
     */
    public static function has(int $value): bool
    {
        $tokens = self::getMapById();

        return isset($tokens[$value]);
    }

    /**
     * @return array<self::T_*, non-empty-string>
     */
    private static function getMapById(): array
    {
        static $constants;

        if (null === $constants) {
            $reflection = new \ReflectionClass(self::class);
            $constants = array_flip($reflection->getConstants());
        }

        return $constants;
    }
}
