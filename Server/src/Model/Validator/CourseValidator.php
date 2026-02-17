<?php
/*
 * Copyright (c) 2024.
 */

namespace App\Model\Validator;

use App\Model\Validator\Exception\InvalidCourseException;

/**
 * @class CourseValidator
 */
class CourseValidator
{
    /**
     * @param int $course
     *
     * @throws InvalidCourseException
     *
     * @return bool
     */
    public static function validate(int $course): bool
    {
        if ($course < 0 || $course > 360) {
            throw new InvalidCourseException();
        }

        return true;
    }
}
