<?php
declare(strict_types=1);

namespace Ortto\Connector\Service;

use Ortto\Connector\Api\OrttoSerializerInterface;
use Magento\Framework\Serialize\Serializer\Json;

// Magento and PHP have ten million different ways of JSON serialization
// Let's keep this as a service in case we need to change the method in the future.
class OrttoSerializer implements OrttoSerializerInterface
{
    private Json $json;

    public function __construct(Json $json)
    {
        $this->json = $json;
    }

    /**
     * @inheritDoc
     */
    public function serializeJson($data)
    {
        return $this->json->serialize($data);
    }
}
