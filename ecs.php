<?php

declare(strict_types=1);

use PHP_CodeSniffer\Standards\Squiz\Sniffs\Arrays\ArrayDeclarationSniff;
use PhpCsFixer\Fixer\Operator\UnaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitStrictFixer;
use SlevomatCodingStandard\Sniffs\TypeHints\ParameterTypeHintSniff;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\CodingStandard\Sniffs\Debug\CommentedOutCodeSniff;
use Symplify\EasyCodingStandard\Configuration\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(LineLengthFixer::class);

    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::SETS, [
        SetList::PHP_70,
        SetList::PHP_71,
        SetList::CLEAN_CODE,
        SetList::SYMPLIFY,
        SetList::COMMON,
        SetList::PSR_12,
        SetList::DEAD_CODE,
    ]);

    $parameters->set(Option::CACHE_DIRECTORY, __DIR__ . '/ecs_cache');
    $parameters->set(Option::CACHE_NAMESPACE, 'lychee');

    $parameters->set(Option::EXCLUDE_PATHS, [
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
        'vendor/*',
    ]);

    $parameters->set(Option::SKIP, [
        ArrayDeclarationSniff::class => null,
        BlankLineAfterOpeningTagFixer::class => null,
        UnaryOperatorSpacesFixer::class => null,
        PhpUnitStrictFixer::class => [
            __DIR__ . '/packages/easy-coding-standard/tests/Indentation/IndentationTest.php',
            __DIR__ . '/packages/set-config-resolver/tests/ConfigResolver/SetAwareConfigResolverTest.php',
        ],
        ParameterTypeHintSniff::class . '.MissingAnyTypeHint' => [
            'app/Http/Middleware/VerifyCsrfToken.php',
            'app/Image/GdHandler.php',
            'app/Exceptions/Handler.php',
        ],
        ParameterTypeHintSniff::class . '.MissingNativeTypeHint' => [
            'app/Http/Resources/Album.php',
            'app/Rules/AlbumExists.php',
        ],
        CommentedOutCodeSniff::class . '.Found' => ['config/*', 'database/*', 'routes/*', 'app/Http/Kernel.php'],
    ]);
};
