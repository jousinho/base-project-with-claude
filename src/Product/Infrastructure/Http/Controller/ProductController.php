<?php

declare(strict_types=1);

namespace App\Product\Infrastructure\Http\Controller;

use App\Product\Application\Command\CreateProductCommand;
use App\Product\Application\Command\GetProductCommand;
use App\Product\Application\Service\CreateProductService;
use App\Product\Application\Service\GetProductService;
use App\Product\Application\Service\ListProductsService;
use App\Product\Domain\Exception\ProductNotFoundException;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController
{
    public function __construct(
        private readonly CreateProductService $createProduct,
        private readonly GetProductService $getProduct,
        private readonly ListProductsService $listProducts,
    ) {}

    #[Route('/api/products', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data    = json_decode($request->getContent(), true) ?? [];
        $command = new CreateProductCommand(
            name:          $data['name'] ?? '',
            priceAmount:   (int) ($data['price_amount'] ?? 0),
            priceCurrency: $data['price_currency'] ?? '',
        );

        try {
            $id = $this->createProduct->execute($command);
        } catch (InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse(null, Response::HTTP_CREATED, [
            'Location' => '/api/products/' . $id,
        ]);
    }

    #[Route('/api/products/{id}', methods: ['GET'])]
    public function get(string $id): JsonResponse
    {
        try {
            $dto = $this->getProduct->execute(new GetProductCommand($id));
        } catch (ProductNotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'id'             => $dto->id,
            'name'           => $dto->name,
            'price_amount'   => $dto->priceAmount,
            'price_currency' => $dto->priceCurrency,
        ]);
    }

    #[Route('/api/products', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $dtos = $this->listProducts->execute();

        return new JsonResponse(array_map(
            fn($dto) => ['id' => $dto->id, 'name' => $dto->name, 'price_amount' => $dto->priceAmount, 'price_currency' => $dto->priceCurrency],
            $dtos,
        ));
    }
}
