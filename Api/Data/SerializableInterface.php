<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api\Data;

use InvalidArgumentException;

interface SerializableInterface
{
    /**
     * Convert array of object data with to array with keys requested in $keys array
     *
     * @param array $keys array of required keys
     * @return array
     */
    public function toArray(array $keys = []);

    /**
     * Convert object data to JSON
     *
     * @param array $keys array of required keys
     * @return bool|string
     * @throws InvalidArgumentException
     */
    public function toJson(array $keys = []);
}
