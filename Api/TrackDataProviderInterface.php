<?php
declare(strict_types=1);

namespace Ortto\Connector\Api;

use Ortto\Connector\Api\Data\TrackingDataInterface;

interface TrackDataProviderInterface
{
    const CUSTOMER_ID_SESSION_KEY = "ortto_customer_id";
    const CUSTOMER_EMAIL_SESSION_KEY = "ortto_customer_email";
    const CUSTOMER_PHONE_SESSION_KEY = "ortto_customer_phn";

    public function getData(): TrackingDataInterface;
}
