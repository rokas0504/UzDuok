<?php

namespace App\Enums;

enum TaskStatus: string
{
    case PENDING = 'pending'; //Pagrindinis, vaikas mato uzduoti, kad jam reikia atlikti
    case IN_PROGRESS = 'in_progress'; //Vaikas paaprovina, tevas gali atsaukti/paaprovinti/grazinti
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case RETURNED = 'returned';

    /**
     * Get all status values as an array.
     *
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get a human-readable label for the status using translations.
     *
     * @return string
     */
    public function label(): string
    {
        return __('enums.' . $this->value);
    }
}
