<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Gateway;

use App\HealthTracker\Application\Gateway\FoodInfo\FoodInfoGatewayInterface;
use App\HealthTracker\Application\Gateway\FoodInfo\FoodInfoGatewayRequest;
use App\HealthTracker\Application\Gateway\FoodInfo\FoodInfoGatewayResponse;
use App\Shared\Application\HttpClient\HttpClientInterface;

final readonly class FoodInfoGateway implements FoodInfoGatewayInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
    ) {}

    public function findOne(FoodInfoGatewayRequest $request): ?FoodInfoGatewayResponse
    {
        $response = $this->httpClient->get(
            'https://world.openfoodfacts.org/cgi/search.pl',
            [
                'query' => [
                    'search_terms' => $request->name,
                    'search_simple' => '1',
                    'action' => 'process',
                    'json' => '1',
                    'categories_lc' => 'ru',
                    'lang' => 'ru',
                    'fields' => 'id,product_name_ru,product_name,nutriments',
                ]
            ]
        );

        if (!array_key_exists('count', $response) || $response['count'] == 0) {
            return null;
        }

        foreach ($response['products'] as $currentProduct) {
            $name = $currentProduct['product_name_ru'] ?? $currentProduct['product_name'] ?? null;
            $nutriments = $currentProduct['nutriments'] ?? [];

            if (empty($name) || empty($nutriments)) {
                continue;
            }

            $calories = $nutriments['energy-kcal_100g'] ?? $nutriments['energy-kcal'] ?? $nutriments['energy-kcal_value'] ?? null;
            $proteins = $nutriments['proteins_100g'] ?? $nutriments['proteins'] ?? $nutriments['proteins_value'] ?? null;
            $fats = $nutriments['fat_100g'] ?? $nutriments['fat'] ?? $nutriments['fat_value'] ?? null;
            $carbohydrates = $nutriments['carbohydrates_100g'] ?? $nutriments['carbohydrates'] ?? $nutriments['carbohydrates_value'] ?? null;

            $hasAllNutriments = $calories !== null
                && $proteins !== null
                && $fats !== null
                && $carbohydrates !== null;

            if ($hasAllNutriments) {
                return new FoodInfoGatewayResponse(
                    externalId: $currentProduct['id'],
                    externalName: $name,
                    calories: (int)$calories,
                    proteins: (float)$proteins,
                    fats: (float)$fats,
                    carbohydrates: (float)$carbohydrates,
                );
            }
        }

        return null;
    }
}
