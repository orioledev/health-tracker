<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/health-check", name: "health_check", methods: ["GET"])]
class HealthCheckController extends AbstractController
{
    public function __invoke(): Response
    {
        return new Response("It works!");
    }
}
