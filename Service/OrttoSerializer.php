<?php
declare(strict_types=1);

namespace Ortto\Connector\Service;

use Ortto\Connector\Api\OrttoSerializerInterface;

class OrttoSerializer implements OrttoSerializerInterface
{
    /**
     * @inheritDoc
     */
    public function serializeJson($data)
    {
        return json_encode($data, JSON_HEX_APOS | JSON_UNESCAPED_SLASHES);
    }
}
