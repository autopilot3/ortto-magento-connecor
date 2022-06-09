<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

use InvalidArgumentException;

interface OrttoSerializerInterface
{
    /**
     * Serialize data into string
     *
     * @param string|int|float|bool|array|null $data
     * @return string|bool
     * @throws InvalidArgumentException
     */
    public function serializeJson($data);
}
