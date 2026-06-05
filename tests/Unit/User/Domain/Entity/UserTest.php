<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Domain\Entity;

use App\User\Domain\Entity\User;
use App\User\Domain\Event\UserWasCreated;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\UserId;
use App\User\Domain\ValueObject\UserName;
use App\User\Domain\ValueObject\UserStatus;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function test_create_user__should_have_active_status(): void
    {
        $user = User::create(UserId::generate(), Email::fromString('john@test.com'), UserName::fromString('John'));

        $this->assertSame(UserStatus::Active, $user->status());
    }

    public function test_create_user__should_emit_user_was_created_event_with_correct_data(): void
    {
        $id    = UserId::generate();
        $email = Email::fromString('john@test.com');
        $name  = UserName::fromString('John');

        $user   = User::create($id, $email, $name);
        $events = $user->pullDomainEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(UserWasCreated::class, $events[0]);
        $this->assertSame($id->value(), $events[0]->userId->value());
        $this->assertSame('john@test.com', $events[0]->email->value());
        $this->assertSame('John', $events[0]->name->value());
    }

    public function test_pull_domain_events__should_clear_events_after_first_pull(): void
    {
        $user = User::create(UserId::generate(), Email::fromString('john@test.com'), UserName::fromString('John'));
        $user->pullDomainEvents();

        $this->assertEmpty($user->pullDomainEvents());
    }
}
