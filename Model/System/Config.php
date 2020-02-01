<?php

/**
 * Amazon Fraud integration (https://aws.amazon.com/fraud-detector/?nc1=h_ls)
 *
 * Use AWS Fraud to detect fake customers
 *
 * @package     ImaginationMedia\AwsFraud
 * @author      Igor Ludgero Miura <igor@imaginationmedia.com>
 * @copyright   Copyright (c) 2020 Imagination Media (https://www.imaginationmedia.com/)
 * @license     https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

declare(strict_types=1);

namespace ImaginationMedia\AwsFraud\Model\System;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    const SYSTEM_CONFIG_FRAUD_ENABLED   = "fraud/general/enable";
    const SYSTEM_CONFIG_FRAUD_PROFILE   = "fraud/general/profile";
    const SYSTEM_CONFIG_FRAUD_VERSION   = "fraud/general/version";
    const SYSTEM_CONFIG_FRAUD_REGION    = "fraud/general/region";
    const SYSTEM_CONFIG_FRAUD_DETECTOR  = "fraud/general/detector";
    const SYSTEM_CONFIG_FRAUD_EVENT     = "fraud/general/event";
    const SYSTEM_CONFIG_FRAUD_AUTO_RATE = "fraud/general/auto_rate";

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Is integration enabled
     * @return bool
     */
    public function isEnabled() : bool
    {
        return $this->scopeConfig->isSetFlag(self::SYSTEM_CONFIG_FRAUD_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get profile name
     * @return string
     */
    public function getProfile() : string
    {
        return (string)$this->scopeConfig->getValue(self::SYSTEM_CONFIG_FRAUD_PROFILE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get version
     * @return string
     */
    public function getVersion() : string
    {
        return (string)$this->scopeConfig->getValue(self::SYSTEM_CONFIG_FRAUD_VERSION, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get region
     * @return string
     */
    public function getRegion() : string
    {
        return (string)$this->scopeConfig->getValue(self::SYSTEM_CONFIG_FRAUD_REGION, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get detector id
     * @return string
     */
    public function getDetector() : string
    {
        return (string)$this->scopeConfig->getValue(self::SYSTEM_CONFIG_FRAUD_DETECTOR, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get event id
     * @return string
     */
    public function getEvent() : string
    {
        return (string)$this->scopeConfig->getValue(self::SYSTEM_CONFIG_FRAUD_EVENT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get minimum auto rate value
     * @return int
     */
    public function getAutoRate() : int
    {
        return (int)$this->scopeConfig->getValue(self::SYSTEM_CONFIG_FRAUD_AUTO_RATE, ScopeInterface::SCOPE_STORE);
    }
}
