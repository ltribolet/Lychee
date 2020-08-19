<?php

declare(strict_types=1);

use PHP_CodeSniffer\Standards\PSR2\Sniffs\ControlStructures\ControlStructureSpacingSniff;
use PHP_CodeSniffer\Standards\PSR2\Sniffs\Namespaces\NamespaceDeclarationSniff;
use PhpCsFixer\Fixer\Alias\MbStrFunctionsFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoWhitespaceBeforeCommaInArrayFixer;
use PhpCsFixer\Fixer\ArrayNotation\WhitespaceAfterCommaInArrayFixer;
use PhpCsFixer\Fixer\Basic\BracesFixer;
use PhpCsFixer\Fixer\Basic\Psr0Fixer;
use PhpCsFixer\Fixer\CastNotation\CastSpacesFixer;
use PhpCsFixer\Fixer\CastNotation\LowercaseCastFixer;
use PhpCsFixer\Fixer\CastNotation\ShortScalarCastFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassDefinitionFixer;
use PhpCsFixer\Fixer\ClassNotation\NoBlankLinesAfterClassOpeningFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\ClassNotation\VisibilityRequiredFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use PhpCsFixer\Fixer\FunctionNotation\PhpdocToReturnTypeFixer;
use PhpCsFixer\Fixer\FunctionNotation\ReturnTypeDeclarationFixer;
use PhpCsFixer\Fixer\Import\FullyQualifiedStrictTypesFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\CombineConsecutiveIssetsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\CombineConsecutiveUnsetsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\DeclareEqualNormalizeFixer;
use PhpCsFixer\Fixer\NamespaceNotation\SingleBlankLineBeforeNamespaceFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\ConcatSpaceFixer;
use PhpCsFixer\Fixer\Operator\NewWithBracesFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\Operator\StandardizeIncrementFixer;
use PhpCsFixer\Fixer\Operator\TernaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\UnaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAlignFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoEmptyReturnFixer;
use PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitSetUpTearDownVisibilityFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitStrictFixer;
use PhpCsFixer\Fixer\Semicolon\NoSinglelineWhitespaceBeforeSemicolonsFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Whitespace\BlankLineBeforeStatementFixer;
use PhpCsFixer\Fixer\Whitespace\NoExtraBlankLinesFixer;
use PhpCsFixer\Fixer\Whitespace\NoTrailingWhitespaceFixer;
use SlevomatCodingStandard\Sniffs\Arrays\TrailingArrayCommaSniff;
use SlevomatCodingStandard\Sniffs\Classes\ModernClassNameReferenceSniff;
use SlevomatCodingStandard\Sniffs\Classes\UnusedPrivateElementsSniff;
use SlevomatCodingStandard\Sniffs\Commenting\ForbiddenCommentsSniff;
use SlevomatCodingStandard\Sniffs\Commenting\UselessInheritDocCommentSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\AssignmentInConditionSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\AlphabeticallySortedUsesSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\DisallowGroupUseSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\FullyQualifiedGlobalFunctionsSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\MultipleUsesPerLineSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\ReferenceUsedNamesOnlySniff;
use SlevomatCodingStandard\Sniffs\Namespaces\RequireOneNamespaceInFileSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\UnusedUsesSniff;
use SlevomatCodingStandard\Sniffs\PHP\TypeCastSniff;
use SlevomatCodingStandard\Sniffs\PHP\UselessParenthesesSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\DeclareStrictTypesSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\LongTypeHintsSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\NullableTypeForNullDefaultValueSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\ParameterTypeHintSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\ParameterTypeHintSpacingSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\ReturnTypeHintSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\ReturnTypeHintSpacingSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\UselessConstantTypeHintSniff;
use SlevomatCodingStandard\Sniffs\Variables\UnusedVariableSniff;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\CodingStandard\Sniffs\Debug\CommentedOutCodeSniff;
use Symplify\EasyCodingStandard\Configuration\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/vendor/symplify/easy-coding-standard/config/set/clean-code.php');
    $containerConfigurator->import(__DIR__ . '/vendor/symplify/easy-coding-standard/config/set/common.php');
    $containerConfigurator->import(__DIR__ . '/vendor/symplify/easy-coding-standard/config/set/symplify.php');

    $services = $containerConfigurator->services();

    $services->set(TrailingArrayCommaSniff::class);
    $services->set(ForbiddenCommentsSniff::class);
    $services->set(UselessInheritDocCommentSniff::class);
    $services->set(AssignmentInConditionSniff::class);
    $services->set(ModernClassNameReferenceSniff::class);
    $services->set(AlphabeticallySortedUsesSniff::class);
    $services->set(DisallowGroupUseSniff::class);
    $services->set(FullyQualifiedGlobalFunctionsSniff::class);
    $services->set(MultipleUsesPerLineSniff::class);
    $services->set(RequireOneNamespaceInFileSniff::class);
    $services->set(TypeCastSniff::class);
    $services->set(UselessParenthesesSniff::class);
    $services->set(LongTypeHintsSniff::class);
    $services->set(NullableTypeForNullDefaultValueSniff::class);
    $services->set(ParameterTypeHintSniff::class);
    $services->set(ParameterTypeHintSpacingSniff::class);
    $services->set(ReturnTypeHintSniff::class);
    $services->set(ReturnTypeHintSpacingSniff::class);
    $services->set(UnusedVariableSniff::class)
        ->property('ignoreUnusedValuesWhenOnlyKeysAreUsedInForeach', true);
    $services->set(UselessConstantTypeHintSniff::class);
    $services->set(UnusedUsesSniff::class)
        ->property('searchAnnotations', true);

    $services->set(NoExtraBlankLinesFixer::class);
    $services->set(LowercaseCastFixer::class);
    $services->set(ShortScalarCastFixer::class);
    $services->set(BlankLineAfterOpeningTagFixer::class);
    $services->set(DeclareEqualNormalizeFixer::class)
        ->call('configure', [['space' => 'none']]);
    $services->set(NewWithBracesFixer::class);
    $services->set(BracesFixer::class)
        ->call('configure', [[
            'allow_single_line_closure' => false,
            'position_after_functions_and_oop_constructs' => 'next',
            'position_after_control_structures' => 'same',
            'position_after_anonymous_constructs' => 'same',
        ]]);
    $services->set(NoBlankLinesAfterClassOpeningFixer::class);
    $services->set(VisibilityRequiredFixer::class)
        ->call('configure', [[
            'elements' => ['const', 'method', 'property'],
        ]]);
    $services->set(TernaryOperatorSpacesFixer::class);
    $services->set(ReturnTypeDeclarationFixer::class);
    $services->set(NoTrailingWhitespaceFixer::class);
    $services->set(NoSinglelineWhitespaceBeforeSemicolonsFixer::class);
    $services->set(NoWhitespaceBeforeCommaInArrayFixer::class);
    $services->set(WhitespaceAfterCommaInArrayFixer::class);
    $services->set(CombineConsecutiveIssetsFixer::class);
    $services->set(CombineConsecutiveUnsetsFixer::class);
    $services->set(PhpdocToReturnTypeFixer::class);
    $services->set(FullyQualifiedStrictTypesFixer::class);
    $services->set(CastSpacesFixer::class)
        ->call('configure', [['space' => 'single']]);
    $services->set(OrderedClassElementsFixer::class)
        ->call('configure', [[
            'order' => ['use_trait'],
        ]]);
    $services->set(OrderedImportsFixer::class)
        ->call('configure', [[
            'imports_order' => ['class', 'const', 'function'],
        ]]);
    $services->set(BinaryOperatorSpacesFixer::class);
    $services->set(UnaryOperatorSpacesFixer::class);
    $services->set(ConcatSpaceFixer::class)
        ->call('configure', [['spacing' => 'one']]);
    $services->set(BlankLineBeforeStatementFixer::class)
        ->call('configure', [[
            'statements' => ['return'],
        ]]);
    $services->set(ClassDefinitionFixer::class)
        ->call('configure', [['single_line' => true]]);
    $services->set(StandardizeIncrementFixer::class);
    $services->set(DeclareStrictTypesFixer::class);
    $services->set(MbStrFunctionsFixer::class);

    $services->set(CommentedOutCodeSniff::class)
        ->property('maxPercentage', 60);
    $services->set(DeclareStrictTypesSniff::class)
        ->property('newlinesCountBetweenOpenTagAndDeclare', 2)
        ->property('newlinesCountAfterDeclare', 2)
        ->property('spacesCountAroundEqualsSign', 0);

    $parameters = $containerConfigurator->parameters();
    $parameters->set('sets', ['php-70', 'php-71', 'common', 'psr-12', 'symfony']);
    $parameters->set('cache_directory', __DIR__ . '/ecs_cache');
    $parameters->set('cache_namespace', 'lychee');
    $parameters->set('exclude_files', [
        __DIR__ . '/_ide_helper.php',
        __DIR__ . '/.phpstorm.meta.php',
        __DIR__ . '/_ide_helper_models.php',
        '.docker/*',
        '.github/*',
        'bootstrap/*',
        'ecs_cache/*',
        'public/*',
        'resources/assets/*',
        'resources/views/*',
        'storage/*',
        'tests/*',
        'vendor/*',
    ]);

    $parameters->set(Option::SKIP, [
        AssignmentInConditionSniff::class . '.FoundInWhileCondition' => null,
        ControlStructureSpacingSniff::class => null,
        NamespaceDeclarationSniff::class => '~ - \'*/migrations/*\' - \'*/seeds/*\'',
        Psr0Fixer::class => null,
        ClassAttributesSeparationFixer::class => null,
        YodaStyleFixer::class => null,
        DeclareEqualNormalizeFixer::class => null,
        SingleBlankLineBeforeNamespaceFixer::class => null,
        NotOperatorWithSuccessorSpaceFixer::class => null,
        NoSuperfluousPhpdocTagsFixer::class => null,
        PhpdocNoEmptyReturnFixer::class => null,
        BlankLineAfterOpeningTagFixer::class => null,
        PhpUnitSetUpTearDownVisibilityFixer::class => null,
        PhpUnitStrictFixer::class => null,
        DeclareStrictTypesFixer::class => null,
        UnusedPrivateElementsSniff::class . '.UnusedMethod' => null,
        ReferenceUsedNamesOnlySniff::class => null,
        PhpdocAlignFixer::class => null,
        ParameterTypeHintSniff::class . '.MissingAnyTypeHint' => [
            'app/Http/Middleware/VerifyCsrfToken.php',
            'app/Image/GdHandler.php',
            'app/Exceptions/Handler.php',
        ],
        ParameterTypeHintSniff::class . '.MissingNativeTypeHint' => [
            'app/Http/Resources/Album.php',
            'app/Rules/AlbumExists.php',
        ],
        CommentedOutCodeSniff::class . '.Found' => ['config/*'],
    ]);
};
