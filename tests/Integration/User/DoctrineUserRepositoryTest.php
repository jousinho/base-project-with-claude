<?php

declare(strict_types=1);

namespace App\Tests\Integration\User;

use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\UserId;
use App\User\Domain\ValueObject\UserName;
use App\User\Domain\ValueObject\UserStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineUserRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private UserRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em         = self::getContainer()->get(EntityManagerInterface::class);
        $this->repository = self::getContainer()->get(UserRepositoryInterface::class);
        $this->em->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->em->rollback();
        parent::tearDown();
    }

    public function test_save_user__should_persist_and_retrieve_by_id(): void
    {
        $user = User::create(
            UserId::generate(),
            Email::fromString('john@test.com'),
            UserName::fromString('John'),
        );

        $this->repository->save($user);
        $this->em->clear();

        $found = $this->repository->findById($user->id());

        $this->assertNotNull($found);
        $this->assertSame($user->id()->value(), $found->id()->value());
        $this->assertSame('john@test.com', $found->email()->value());
        $this->assertSame('John', $found->name()->value());
        $this->assertSame(UserStatus::Active, $found->status());
    }

    public function test_find_user_by_email__when_exists__should_return_correct_user(): void
    {
        $user = User::create(
            UserId::generate(),
            Email::fromString('jane@test.com'),
            UserName::fromString('Jane'),
        );

        $this->repository->save($user);
        $this->em->clear();

        $found = $this->repository->findByEmail(Email::fromString('jane@test.com'));

        $this->assertNotNull($found);
        $this->assertSame('jane@test.com', $found->email()->value());
        $this->assertSame('Jane', $found->name()->value());
    }

    public function test_find_user_by_email__when_not_exists__should_return_null(): void
    {
        $found = $this->repository->findByEmail(Email::fromString('nobody@test.com'));

        $this->assertNull($found);
    }

    public function test_find_user_by_id__when_not_exists__should_return_null(): void
    {
        $found = $this->repository->findById(UserId::fromString('00000000-0000-0000-0000-000000000000'));

        $this->assertNull($found);
    }

    public function test_find_all__should_return_all_saved_users(): void
    {
        $this->repository->save(
            User::create(UserId::generate(), Email::fromString('alice@test.com'), UserName::fromString('Alice')),
        );
        $this->repository->save(
            User::create(UserId::generate(), Email::fromString('bob@test.com'), UserName::fromString('Bob')),
        );
        $this->em->clear();

        $users = $this->repository->findAll();

        $this->assertCount(2, $users);
    }
}
