<?php

namespace Autopilot\AP3Connector\Api;

interface JobStatusInterface
{
    const QUEUED = "queued";
    const IN_PROGRESS = "in-progress";
    const SUCCESS = "success";
    const FAILED = "failed";
}
