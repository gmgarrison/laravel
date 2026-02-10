<?php

namespace App;

use App\Enums\Rooms;
use Aws\S3\S3Client;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Fluent;

class IamtHelpers
{
    /**
     * @return array two-dimensional array generated from CSV
     */
    public static function arrayFromCSV(string $csvFileName, bool $importHeaders = true, bool $sameColumns = true): ?array
    {

        $toReturn = [];

        // open csv
        $array = array_map('str_getcsv', file($csvFileName));

        if ($importHeaders) {
            // Get field names from header column and remove the header column
            $fields = array_map('strtolower', $array[0]);
            array_shift($array);
        } else {
            return null;
        }

        if ($sameColumns) {
            foreach ($array as $row) {
                if (count($fields) == count($row)) {
                    $row = array_map('html_entity_decode', $row);
                    $toReturn[] = array_combine($fields, $row);

                }
            }

            return $toReturn;
        }

        return $array;
    }

    public static function stripNonNumeric(?string $from): ?string
    {
        return preg_replace('/[^0-9]/', '', $from);
    }

    public static function extractEmailsFromApps(Collection $apps, bool $includeParents = false): string
    {
        $emails = [];
        foreach ($apps as $app) {
            if ($app->user?->email) {
                $emails[] = $app->user->email;
            }
            if ($includeParents) {
                foreach ($app->user?->parents as $parent) {
                    if ($parent->email) {
                        $emails[] = $parent->email;
                    }
                }
            }
        }

        return implode(',', $emails);
    }

    public static function extractEmailsFromEnrollments(Collection $enrollments, bool $includeParents = false): string
    {
        $emails = [];
        foreach ($enrollments as $enrollment) {
            if ($enrollment->student->email) {
                $emails[] = $enrollment->student->email;
                if ($includeParents) {
                    foreach ($enrollment->student->parents as $parent) {
                        if ($parent->email) {
                            $emails[] = $parent->email;
                        }
                    }
                }
            }
        }

        return implode(',', $emails);
    }

    public static function arrayStringToFluent(string $arrayString): Fluent
    {
        $result = [];
        preg_match_all('/\[\s*([^\]]+)\s*\]\s*=>\s*([^\n]+)/', $arrayString, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $key = trim($match[1]);
            $value = trim($match[2]);

            // Convert numeric values to their appropriate type
            if (is_numeric($value)) {
                $value = $value + 0; // Convert to int or float
            }

            $result[$key] = $value;
        }

        return new Fluent($result);
    }

    public static function closestRoom(float $lat, float $long): array
    {
        $distance = PHP_INT_MAX;
        $room_to_return = null;

        foreach (Rooms::center() as $room_id => $room) {
            $center_distance = self::distance($lat, $long, $room[0], $room[1]);

            if ($center_distance < $distance) {
                $distance = $center_distance;
                $room_to_return = $room_id;
            }
        }

        return ['room' => $room_to_return, 'distance' => $distance];
    }

    public static function distance($lat1, $lon1, $lat2, $lon2): float
    {
        //        $earthRadius = 3959; // Earth radius in miles
        $earthRadius = 6371; // Earth's radius in km

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c * 3280.84; // Distance in feet, converted from km
    }

    public static function isWeekday(): bool
    {
        return ! in_array(
            ((int) date('N')), [6, 7], true);
    }

    public static function isWeekend(): bool
    {
        return in_array(date('N'), [6, 7], true);
    }

    public static function getFullSql($query): string
    {
        $sql = $query->toSql();
        foreach ($query->getBindings() as $binding) {
            $value = is_numeric($binding) ? $binding : "'{$binding}'";
            $sql = preg_replace('/\?/', $value, $sql, 1);
        }

        return $sql;
    }

    public static function client(): S3Client
    {
        return new S3Client([
            'version' => 'latest',
            'region' => 'auto',
            'endpoint' => config('cloudflare.r2_endpoint'),
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key' => config('cloudflare.r2_access_key'),
                'secret' => config('cloudflare.r2_secret_key'),
            ],
        ]);
    }
}
