<?php
declare(strict_types=1);

namespace Autopilot\AP3Connector\Setup;

interface SchemaInterface
{
    const TABLE_SYNC_JOBS = 'autopilot_sync_jobs';
    const TABLE_CRON_CHECKPOINT = 'autopilot_cron_checkpoint';
    const TABLE_CUSTOMER_ATTRIBUTES = 'autopilot_customer_attributes';

    const COLUMN_CUSTOMER_AUTOPILOT_CONTACT_ID = 'autopilot_contact_id';
}
