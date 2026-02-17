<?php
/*
 * Copyright (c) 2024.
 */

namespace App\Model\Validator;


use App\Model\Validator\Exception\InvalidCoordinateException;
use PHPUnit\Framework\Warning;

/**
 *
 */
class CoordinateValidator
{
    /**
     * @param float $latitude
     *
     * @throws InvalidCoordinateException
     *
     * @return bool
     */
    public static function validateLatitude(float $latitude): bool
    {
        if ($latitude < -90 || $latitude > 90) {
            throw new InvalidCoordinateException();
        }

        return true;
    }

    /**
     * @param float $longitude
     *
     * @throws InvalidCoordinateException
     *
     * @return bool
     */
    public static function validateLongitude(float $longitude): bool
    {
        if ($longitude < -180 || $longitude > 180) {
            throw new InvalidCoordinateException();
        }

        return true;
    }
}
