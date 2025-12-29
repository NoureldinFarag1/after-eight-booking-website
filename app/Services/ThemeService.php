<?php

namespace App\Services;

class ThemeService
{
    /**
     * Generate a consistent, visually distinct color for a user based on their name.
     * This helps in UIs where users need to be quickly distinguished.
     *
     * @param string $name The user's name.
     * @return string A hex color code.
     */
    public static function getUserColor(string $name): string
    {
        // Simple hash to get a number from the name string
        $hash = crc32($name);

        // Base HSV values
        $hue = $hash % 360;
        $saturation = 70; // Keep saturation and lightness constant for a consistent theme
        $lightness = 40;

        // Convert HSL to RGB, then to Hex
        return self::hslToHex($hue, $saturation, $lightness);
    }

    /**
     * Converts HSL color values to a hex string.
     */
    private static function hslToHex($h, $s, $l): string
    {
        $s /= 100;
        $l /= 100;

        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $c / 2;

        if ($h < 60) {
            $r = $c; $g = $x; $b = 0;
        } elseif ($h < 120) {
            $r = $x; $g = $c; $b = 0;
        } elseif ($h < 180) {
            $r = 0; $g = $c; $b = $x;
        } elseif ($h < 240) {
            $r = 0; $g = $x; $b = $c;
        } elseif ($h < 300) {
            $r = $x; $g = 0; $b = $c;
        } else {
            $r = $c; $g = 0; $b = $x;
        }

        $r = round(($r + $m) * 255);
        $g = round(($g + $m) * 255);
        $b = round(($b + $m) * 255);

        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }
}
