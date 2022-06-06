<?php
declare(strict_types=1);


namespace Ortto\Connector\Model;

use Magento\Framework\Data\OptionSourceInterface;
use Ortto\Connector\Api\JobStatusInterface;

class JobStatuses implements OptionSourceInterface
{
    public function toOptionArray()
    {
        $statuses = [
            JobStatusInterface::QUEUED => "Queued",
            JobStatusInterface::IN_PROGRESS => "In progress",
            JobStatusInterface::SUCCEEDED => "Succeeded",
            JobStatusInterface::FAILED => "Failed",
        ];

        $options = [];
        foreach ($statuses as $key => $status) {
            $options[] = [
                'label' => $status,
                'value' => $key,
            ];
        }
        return $options;
    }
}
