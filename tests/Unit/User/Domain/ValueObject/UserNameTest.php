<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Domain\ValueObject;

use App\User\Domain\ValueObject\UserName;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class UserNameTest extends TestCase
{
    public function test_create_username__should_preserve_value(): void
    {
        $name = UserName::fromString('John Doe');

        $this->assertSame('John Doe', $name->value());
    }

    public function test_create_username__when_empty__should_throw_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('UserName cannot be empty.');

        UserName::fromString('');
    }

    public function test_create_username__when_only_spaces__should_throw_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        UserName::fromString('   ');
    }

    public function test_create_username__when_100_chars__should_be_valid(): void
    {
        $name = UserName::fromString(str_repeat('a', 100));

        $this->assertSame(100, strlen($name->value()));
    }

    public function test_create_username__when_101_chars__should_throw_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('UserName cannot exceed 100 characters.');

        UserName::fromString(str_repeat('a', 101));
    }
}
