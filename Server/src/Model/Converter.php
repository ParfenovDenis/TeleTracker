<?php
/**
 * Copyright (c) 2019.
 */

namespace App\Model;

use App\Model\Validator\DateTimeValidator;
use App\Model\Validator\Exception\InvalidDateTimeException;
use DoctrineExtensions\Query\Mysql\Date;

/**
 * @class Converter
 */
class Converter
{
    /**
     * Return int value
     * Первый байт - старший
     * @param string $string
     *
     * @return int
     */
    public static function convertCharsToLong(string $string)
    {
        $num = 0;
        $length = \strlen($string);
        for ($i = $length; $i > 0; $i--) {
            $num = $num | ord($string[$length - $i]) << (($i - 1) * 8);
        }

        return $num;
    }

    /**
     * @param int $n
     *
     * @return string
     */
    public static function convertLongToChars(int $n): string
    {
        $pbytes = '';
        $pbytes[0] = ($n >> 24);
        $pbytes[1] = ($n >> 16);
        $pbytes[2] = ($n >> 8);
        $pbytes[3] = $n;

        return $pbytes;
    }

    /**
     * Последний байт - старший
     * @param string $string
     *
     * @return int
     */
    public static function convertCharsToLong2(string $string)
    {
        $num = 0;
        $length = \strlen($string);
        for ($i = 0; $i < $length; $i++) {
            $num = $num | ord($string[$i]) << ($i * 8);
        }

        return $num;
    }

    /**
     * @param string $bytes
     *
     * @return \DateTimeInterface|null
     *
     * @throws InvalidDateTimeException
     *
     */
    public static function getDateTime(string $bytes): ?\DateTimeInterface
    {
        $yearmonth = ord($bytes[0]);
        $year = ($yearmonth >> 4) + 2020;
        $month = $yearmonth;
        for ($i = 4; $i > 0; $i--) {
            $month = $month >= (2 ** $i * 8) ? $month - 2 ** $i * 8 : $month;
        }
        $day = ord($bytes[1]);
        $hour = ord($bytes[2]);
        $min = ord($bytes[3]);
        $sec = ord($bytes[4]);
        try {
            $validator = new DateTimeValidator($year, $month, $day, $hour, $min, $sec);
            $validator->validate();

            return self::createDateTime($year, $month, $day, $hour, $min, $sec);
        } catch (InvalidDateTimeException $exception) {
            return null;
        }
    }

    /**
     * @param int $year
     * @param int $month
     * @param int $day
     * @param int $hour
     * @param int $min
     * @param int $sec
     *
     * @return \DateTime
     */
    private static function createDateTime(int $year, int $month, int $day, int $hour, int $min, int $sec): \DateTime
    {
        $datetime = new \DateTime();
        $datetime->setDate($year, $month, $day)->setTime($hour, $min, $sec);

        return $datetime;
    }
}
