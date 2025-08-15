<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'OpenEnergyCoop API',
    description: 'API completa para la gestión de cooperativas energéticas con funcionalidades de CMS, equipos, desafíos y más.',
    contact: new OA\Contact(
        name: 'Soporte OpenEnergyCoop',
        email: 'soporte@openenergycoop.com'
    ),
    license: new OA\License(
        name: 'MIT',
        url: 'https://opensource.org/licenses/MIT'
    )
)]
#[OA\Server(
    url: '/api/v1',
    description: 'Servidor API V1'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Autenticación mediante Laravel Sanctum'
)]
class OpenApiInfo {}
