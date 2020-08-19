<?php

namespace Tests\Feature\API;

use Illuminate\Testing\TestResponse;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

trait OpenApiSchemaValidator
{
    protected string $schemaFile;

    protected function schemaValidate(
        string $requestPath,
        string $requestMethod,
        TestResponse $response,
        ?string $schema = null
    ): bool {
        $schemaFile = $schema ?? $this->schemaFile;

        $requestPath = \str_replace('/api', '', $requestPath);

        // @todo extract this into its own class/service
        $psr17Factory = new Psr17Factory();
        $psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        $psrResponse = $psrHttpFactory->createResponse($response->baseResponse);

        $validator = (new ValidatorBuilder())->fromYamlFile($schemaFile)->getResponseValidator();
        $operation = new OperationAddress($requestPath, $requestMethod);

        $validator->validate($operation, $psrResponse);

        return true;
    }

}
