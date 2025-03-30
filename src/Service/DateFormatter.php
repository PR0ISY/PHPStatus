<?php
namespace App\Service;

class DateFormatter
{
    public function convertToUserTimezone(\DateTime $date, string $userTimezone): \DateTime
    {
        try {
            $timezone = new \DateTimeZone($userTimezone);
            return $date->setTimezone($timezone);
        } catch (\Exception $e) {
            error_log('Failed to convert timezone: ' . $e->getMessage());
            return $date;
        }
    }

    public function formatDateForDisplay(\DateTime $date, string $userTimezone): string
    {
        $convertedDate = $this->convertToUserTimezone($date, $userTimezone);

        return sprintf(
            '%s (%s)',
            $convertedDate->format('F j, Y, H:i'),
            $userTimezone
        );
    }
}