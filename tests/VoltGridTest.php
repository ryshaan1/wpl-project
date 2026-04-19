<?php
/**
 * VoltGrid – PHPUnit Test Cases
 * Experiment 8 | Web Programming Laboratory (216U01L405)
 *
 * Run: ./vendor/bin/phpunit tests/VoltGridTest.php --testdox
 */

use PHPUnit\Framework\TestCase;

// ── Functions copied from the project ─────────────────────────────────────

// From post_registration.php / save_booking.php
function clean(string $data): string
{
    return htmlspecialchars(trim($data));
}

// From booking.html JS logic (converted to PHP)
function calculateTotal(int $durationMin, float $rate): int
{
    $session = round(($durationMin / 60) * 20 * $rate);
    $gst     = round($session * 0.18);
    return (int)($session + $gst + 5); // 5 = platform fee
}

// From post_registration.php
function isValidEmail(string $email): bool
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

// ── Test Cases ─────────────────────────────────────────────────────────────

class VoltGridTest extends TestCase
{
    // TC-01: clean() removes extra spaces
    public function testCleanTrimsSpaces(): void
    {
        $this->assertSame('Ryshaan', clean('  Ryshaan  '));
    }

    // TC-02: clean() blocks XSS attacks
    public function testCleanBlocksXSS(): void
    {
        $result = clean('<script>alert("hack")</script>');
        $this->assertStringNotContainsString('<script>', $result);
    }

    // TC-03: Booking total for 1 hour at Vidyavihar (Rs.15/kWh)
    // session = 300, GST = 54, platform = 5 → total = 359
    public function testBookingTotalVidyavihar(): void
    {
        $this->assertSame(359, calculateTotal(60, 15));
    }

    // TC-04: Booking total for 1 hour at Chembur (Rs.18/kWh)
    // session = 360, GST = 65, platform = 5 → total = 430
    public function testBookingTotalChembur(): void
    {
        $this->assertSame(430, calculateTotal(60, 18));
    }

    // TC-05: Valid email passes
    public function testValidEmail(): void
    {
        $this->assertTrue(isValidEmail('ryshaan@somaiya.edu'));
    }

    // TC-06: Invalid email fails
    public function testInvalidEmail(): void
    {
        $this->assertFalse(isValidEmail('not-an-email'));
    }
}
