<?php

namespace App\OpenApi;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Booking Lapangan API",
 *     version="1.0.0",
 *     description="API Booking Lapangan Olahraga menggunakan Laravel 12 dan Passport (client_credentials)."
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Local Development Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="passport",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Gunakan access token dari Passport client_credentials"
 * )
 */
class SwaggerConfig {}
