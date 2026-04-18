<?php
/**
 * Booking Calculator – AI-Generated Version (GitHub Copilot / Claude)
 * Experiment 9 | Web Programming Laboratory (216U01L405)
 */

/**
 * Calculates total booking cost including GST and platform fee.
 *
 * @param int   $durationMin  Duration in minutes
 * @param float $ratePerKwh   Price per kWh at the station
 * @return int  Total amount in rupees
 */
function calcTotal(int $durationMin, float $ratePerKwh): int
{
    if ($durationMin <= 0 || $ratePerKwh <= 0) {
        return 0;
    }

    $sessionFee = round(($durationMin / 60) * 20 * $ratePerKwh);
    $gst        = round($sessionFee * 0.18);
    $platform   = 5;

    return (int)($sessionFee + $gst + $platform);
}

// Quick test
echo calcTotal(60, 15);  // Expected: 359
echo "\n";
echo calcTotal(60, 18);  // Expected: 430
