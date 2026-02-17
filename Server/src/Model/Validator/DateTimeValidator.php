<?php
/*
 * Copyright (c) 2024.
 */

namespace App\Model\Validator;

use App\Model\Validator\Exception\InvalidDateTimeException;

/**
 * @class DateTimeValidator
 */
class DateTimeValidator
{
    const YEAR_MIN_VALUE = 2010;
    private $year;
    private $month;
    private $day;
    private $hours;
    private $minutes;
    private $seconds;

    /**
     * @param int $year
     * @param int $month
     * @param int $day
     * @param int $hours
     * @param int $minutes
     * @param int $seconds
     */
    public function __construct(int $year, int $month, int $day, int $hours, int $minutes, int $seconds)
    {
        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
        $this->hours = $hours;
        $this->minutes = $minutes;
        $this->seconds = $seconds;
    }

    /**
     * @throws InvalidDateTimeException
     *
     * @return bool
     */
    public function validate(): bool
    {
        $this->validateYear();
        $this->validateMonth();
        $this->validateDay();
        $this->validateHours();
        $this->validateMinutes();
        $this->validateSeconds();

        return true;
    }

    /**
     *
     * @return bool
     */
    private function validateYear(): bool
    {
        $date = getdate();
        if ($this->year > $date['year'] || $this->year < self::YEAR_MIN_VALUE) {
            throw new InvalidDateTimeException();
        }

        return true;
    }

    /**
     *
     * @return bool
     */
    private function validateMonth(): bool
    {
        if ($this->month > 12 || $this->month < 1) {
            throw new InvalidDateTimeException();
        }

        return true;
    }

    /**
     *
     * @return bool
     */
    private function validateDay(): bool
    {
        if ($this->day < 1) {
            throw  new InvalidDateTimeException();
        }
        $maxDay = 31;

        if ($this->month % 2 === 0) {
            if (2 === $this->month) {
                if ($this->year % 4 === 0) {
                    $maxDay = 29;
                } else {
                    $maxDay = 28;
                }
            } else {
                $maxDay = 30;
            }
        }

        if ($this->day > $maxDay) {
            throw  new InvalidDateTimeException();
        }

        return true;
    }

    /**
     *
     * @return bool
     */
    private function validateHours(): bool
    {
        if ($this->hours > 23 || $this->hours < 0) {
            throw new InvalidDateTimeException();
        }

        return true;
    }

    /**
     *
     * @return bool
     */
    private function validateMinutes(): bool
    {
        return self::validate60($this->minutes);
    }

    /**
     *
     * @return bool
     */
    private function validateSeconds(): bool
    {
        return self::validate60($this->seconds);
    }

    /**
     * @param int $unitTime
     *
     * @return bool
     */
    private static function validate60(int $unitTime): bool
    {
        if ($unitTime > 59 || $unitTime < 0) {
            throw new InvalidDateTimeException();
        }

        return true;
    }


}
