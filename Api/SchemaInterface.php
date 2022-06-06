<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

interface SchemaInterface
{
    const TABLE_SYNC_JOBS = 'ortto_sync_jobs';
    const TABLE_CRON_CHECKPOINT = 'ortto_cron_checkpoint';
    const TABLE_ORDER_ATTRIBUTES = 'ortto_order_attributes';
}
