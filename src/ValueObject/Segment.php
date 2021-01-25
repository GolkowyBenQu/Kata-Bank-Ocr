<?php

namespace App\ValueObject;

class Segment
{
    private static array $numbers = [
        0 => " _ ".
            "| |".
            "|_|",
        1 => "   ".
            "  |".
            "  |",
        2 => " _ ".
            " _|".
            "|_ ",
        3 => " _ ".
            " _|".
            " _|",
        4 => "   ".
            "|_|".
            "  |",
        5 => " _ ".
            "|_ ".
            " _|",
        6 => " _ ".
            "|_ ".
            "|_|",
        7 => " _ ".
            "  |".
            "  |",
        8 => " _ ".
            "|_|".
            "|_|",
        9 => " _ ".
            "|_|".
            " _|",
    ];

    private static array $segmentNumbers = [];

    public static function findNumberBySegment(string $segmentNumber): string
    {
        if (!self::$segmentNumbers) {
            self::$segmentNumbers = array_flip(self::$numbers);
        }

        return self::$segmentNumbers[$segmentNumber] ?? '?';
    }

    public static function findSimilarDigits(string $segmentFragment): array
    {
        $similarDigits = [];

        foreach (self::$numbers as $digit => $segment) {
            $diff = array_diff_assoc(str_split($segmentFragment), str_split($segment));

            if (count($diff) === 1) {
                $similarDigits[] = (string)$digit;
            }
        }

        return $similarDigits;
    }
}
