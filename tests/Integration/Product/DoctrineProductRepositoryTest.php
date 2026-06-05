<?php

declare(strict_types=1);

namespace App\Tests\Integration\Product;

use App\Product\Domain\Entity\Product;
use App\Product\Domain\Repository\ProductRepositoryInterface;
use App\Product\Domain\ValueObject\Currency;
use App\Product\Domain\ValueObject\Money;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductName;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineProductRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private ProductRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em         = self::getContainer()->get(EntityManagerInterface::class);
        $this->repository = self::getContainer()->get(ProductRepositoryInterface::class);
        $this->em->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->em->rollback();
        parent::tearDown();
    }

    public function test_save_product__should_persist_and_retrieve_by_id(): void
    {
        $product = Product::create(
            ProductId::generate(),
            ProductName::fromString('Widget'),
            Money::of(999, Currency::EUR),
        );

        $this->repository->save($product);
        $this->em->clear();

        $found = $this->repository->findById($product->id());

        $this->assertNotNull($found);
        $this->assertSame($product->id()->value(), $found->id()->value());
        $this->assertSame('Widget', $found->name()->value());
        $this->assertSame(999, $found->price()->amount());
        $this->assertSame(Currency::EUR, $found->price()->currency());
    }

    public function test_find_product_by_id__when_not_exists__should_return_null(): void
    {
        $found = $this->repository->findById(ProductId::fromString('00000000-0000-0000-0000-000000000000'));

        $this->assertNull($found);
    }

    public function test_find_all__should_return_all_saved_products(): void
    {
        $this->repository->save(
            Product::create(ProductId::generate(), ProductName::fromString('Widget A'), Money::of(100, Currency::EUR)),
        );
        $this->repository->save(
            Product::create(ProductId::generate(), ProductName::fromString('Widget B'), Money::of(200, Currency::USD)),
        );
        $this->em->clear();

        $products = $this->repository->findAll();

        $this->assertCount(2, $products);
    }
}
