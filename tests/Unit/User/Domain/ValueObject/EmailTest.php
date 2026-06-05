<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Domain\ValueObject;

use App\User\Domain\ValueObject\Email;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    public function test_create_email__should_preserve_value(): void
    {
        $email = Email::fromString('john@test.com');

        $this->assertSame('john@test.com', $email->value());
    }

    public function test_create_email__when_invalid_format__should_throw_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"not-an-email" is not a valid email address.');

        Email::fromString('not-an-email');
    }

    public function test_create_email__when_empty__should_throw_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Email::fromString('');
    }

    public function test_equals__when_same_value__should_return_true(): void
    {
        $a = Email::fromString('john@test.com');
        $b = Email::fromString('john@test.com');

        $this->assertTrue($a->equals($b));
    }

    public function test_equals__when_different_value__should_return_false(): void
    {
        $a = Email::fromString('john@test.com');
        $b = Email::fromString('jane@test.com');

        $this->assertFalse($a->equals($b));
    }
}
