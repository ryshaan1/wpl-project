<?php
/**
 * Booking Calculator – Manual Version
 * Experiment 9 | Web Programming Laboratory (216U01L405)
 */

// Calculates total booking cost
function calcTotal($duration, $rate) {
    $session  = ($duration / 60) * 20 * $rate;
    $gst      = $session * 0.18;
    $total    = $session + $gst + 5;
    return round($total);
}

// Quick test
echo calcTotal(60, 15);  // Expected: 359
echo "\n";
echo calcTotal(60, 18);  // Expected: 430
