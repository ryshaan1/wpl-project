<?php
/**
 * VoltGrid – PHPUnit Test Cases
 * Experiment 8 | Web Programming Laboratory (216U01L405)
 *
 * Updated to test actual project files via require_once.
 * Run: ./vendor/bin/phpunit tests/VoltGridTest.php --testdox
 */

use PHPUnit\Framework\TestCase;

// Include actual project utilities
require_once __DIR__ . '/../session.php';

// Logic helpers from the project
function calculateTotal(int $durationMin, float $rate): int
{
    $session = round(($durationMin / 60) * 20 * $rate);
    $gst     = round($session * 0.18);
    return (int)($session + $gst + 5); // 5 = platform fee
}

function isValidEmail(string $email): bool
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

function isValidPhone(string $phone): bool
{
    return (bool) preg_match('/^[0-9]{10}$/', $phone);
}

class VoltGridTest extends TestCase
{
    // TC-01: clean_input() removes extra spaces and handles html
    public function testCleanInput(): void
    {
        $this->assertSame('Ryshaan', clean_input('  Ryshaan  '));
        $this->assertSame('&lt;b&gt;bold&lt;/b&gt;', clean_input('<b>bold</b>'));
    }

    // TC-02: Booking total for 1 hour at Vidyavihar (Rs.15/kWh)
    public function testBookingTotalVidyavihar(): void
    {
        $this->assertSame(359, calculateTotal(60, 15));
    }

    // TC-03: Booking total for 1 hour at Chembur (Rs.18/kWh)
    public function testBookingTotalChembur(): void
    {
        $this->assertSame(430, calculateTotal(60, 18));
    }

    // TC-04: Email validation logic
    public function testEmailValidation(): void
    {
        $this->assertTrue(isValidEmail('ryshaan@somaiya.edu'));
        $this->assertFalse(isValidEmail('invalid-email'));
    }

    // TC-05: Phone validation logic (10 digits)
    public function testPhoneValidation(): void
    {
        $this->assertTrue(isValidPhone('9876543210'));
        $this->assertFalse(isValidPhone('12345'));
        $this->assertFalse(isValidPhone('abcdefghij'));
    }

    // TC-06: clean_input handles null safely
    public function testCleanInputHandlesNull(): void
    {
        $this->assertSame('', clean_input(null));
    }
}
