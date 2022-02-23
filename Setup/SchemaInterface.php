<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Setup;

interface SchemaInterface
{
    const TABLE_SYNC_JOBS = 'autopilot_sync_jobs';
    const TABLE_CRON_CHECKPOINT = 'autopilot_cron_checkpoint';
}
