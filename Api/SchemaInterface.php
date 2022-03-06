<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Api;

interface SchemaInterface
{
    const TABLE_SYNC_JOBS = 'autopilot_sync_jobs';
    const TABLE_CRON_CHECKPOINT = 'autopilot_cron_checkpoint';
    const TABLE_CUSTOMER_ATTRIBUTES = 'autopilot_customer_attributes';
}
