<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

interface JobStatusInterface
{
    // NOTE: Update Models/JobStatuses if this is changed
    const QUEUED = "queued";
    const IN_PROGRESS = "in-progress";
    const SUCCEEDED = "succeeded";
    const FAILED = "failed";
}
