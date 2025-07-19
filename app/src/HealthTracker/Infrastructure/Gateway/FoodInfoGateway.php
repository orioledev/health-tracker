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
        private HttpClientInterface $httpClient
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
                    'fields' => 'categories_hierarchy,nutriments,product_name_ru,id',
                ]
            ]
        );

        if (!array_key_exists('count', $response) || $response['count'] == 0) {
            return null;
        }

        $product = $response['products'][0];

        return new FoodInfoGatewayResponse(
            externalId: $product['id'],
            externalName: $product['product_name_ru'],
            calories: $product['nutriments']['energy-kcal_100g'] ?? 0,
            proteins: $product['nutriments']['proteins_100g'] ?? 0,
            fats: $product['nutriments']['fat_100g'] ?? 0,
            carbohydrates: $product['nutriments']['carbohydrates_100g'] ?? 0
        );
    }
}
