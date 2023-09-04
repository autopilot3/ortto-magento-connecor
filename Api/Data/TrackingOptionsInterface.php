<?php
declare(strict_types=1);

namespace Ortto\Connector\Api\Data;

use Ortto\Connector\Api\ConfigScopeInterface;

interface TrackingOptionsInterface
{
    const SCOPE = 'scope';
    const EMAIL = 'email';
    const PHONE = 'phone';
    const CAPTURE_API = 'capture_api';
    const CAPTURE_JS = 'capture_js';
    const MAGENTO_JS = 'magento_js';
    const NEEDS_CONSENT_TO_TRACK = 'needs_consent_to_track';
    const TRACKING_CODE = 'tracking_code';
    const ACTIVE = 'active';

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email);

    /**
     * @return string
     */
    public function getEmail(): string;

    /**
     * @param string $phone
     * @return $this
     */
    public function setPhone(string $phone);

    /**
     * @return string|null
     */
    public function getPhone();

    /**
     * @param string $url
     * @return $this
     */
    public function setCaptureAPI(string $url);

    /**
     * @return string
     */
    public function getCaptureAPI(): string;

    /**
     * @param string $url
     * @return $this
     */
    public function setCaptureJS(string $url);

    /**
     * @return string
     */
    public function getCaptureJS(): string;

    /**
     * @param string $url
     * @return $this
     */
    public function setMagentoJS(string $url);

    /**
     * @return string
     */
    public function getMagentoJS(): string;

    /**
     * @param string $code
     * @return $this
     */
    public function setTrackingCode(string $code);

    /**
     * @return string
     */
    public function getTrackingCode(): string;

    /**
     * @param bool $required
     * @return $this
     */
    public function setNeedsConsent(bool $required);

    /**
     * @return bool
     */
    public function getNeedsConsent(): bool;

    /**
     * @param ConfigScopeInterface $scope
     * @return $this
     */
    public function setScope(ConfigScopeInterface $scope);

    /**
     * @return ConfigScopeInterface
     */
    public function getScope(): ConfigScopeInterface;
}
