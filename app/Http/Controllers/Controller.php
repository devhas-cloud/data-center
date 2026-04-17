<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

abstract class Controller
{
    /**
     * Convert unix timestamp (UTC) to user timezone (Carbon)
     */
    protected function unixToDateTime(int $unixTimestamp): Carbon
    {
        return Carbon::createFromTimestamp($unixTimestamp, 'UTC')
            ->setTimezone(config('app.timezone'));
    }

    /**
     * Convert unix timestamp to formatted string (optional)
     */
    protected function unixToDateTimeString(
        int $unixTimestamp,
        string $format = 'Y-m-d H:i:s'
    ): string {
        return $this->unixToDateTime($unixTimestamp)->format($format);
    }

    protected function dateTimeToUnix(string $dateTimeString): int
    {
        return Carbon::parse($dateTimeString, config('app.timezone'))
            ->setTimezone('UTC')
            ->timestamp;
    }


}
