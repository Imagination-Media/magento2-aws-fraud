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

namespace ImaginationMedia\AwsFraud\Block\Adminhtml\Customer\Edit\Tab;

use ImaginationMedia\AwsFraud\Model\Fraud\Detector;
use ImaginationMedia\AwsFraud\Setup\Patch\Data\AddCustomerAttributes;
use Magento\Backend\Block\Template\Context;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Customer\Model\Customer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\Element\Template;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Magento\Customer\Model\CustomerFactory;

class Fraud extends Template implements TabInterface
{
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var AssetRepository
     */
    protected $assetRepository;

    /**
     * @var Detector
     */
    protected $detector;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * Fraud constructor.
     * @param Context $context
     * @param Registry $registry
     * @param AssetRepository $assetRepository
     * @param Detector $detector
     * @param TimezoneInterface $timezone
     * @param CustomerFactory $customerFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        AssetRepository $assetRepository,
        Detector $detector,
        TimezoneInterface $timezone,
        CustomerFactory $customerFactory,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->assetRepository = $assetRepository;
        $this->detector = $detector;
        $this->timezone = $timezone;
        $this->customerFactory = $customerFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get current customer id
     * @return int
     */
    public function getCustomerId() : int
    {
        return (int)$this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * Return customer
     * @return Customer
     */
    public function getCustomer() : Customer
    {
        $customer = $this->customerFactory->create();
        $customer->load((int)$this->getCustomerId());

        try {
            if (!$customer->getData(AddCustomerAttributes::CUSTOMER_ATTRIBUTE_AWS_FRAUD_RATE)) {
                $date = $timestamp = new \DateTime(
                    $customer->getCreatedAt(),
                    new \DateTimeZone($this->timezone->getConfigTimezone())
                );
                $this->detector->getCustomerFraudScore([
                    "email_address" => $customer->getEmail(),
                    "ip_address" => $this->detector->getCustomerIp((int)$customer->getId()),
                    "event_timestamp" => $date->getTimestamp()
                ], $customer);
            }
        } catch (\Exception $ex) {
            /**
             * Not possible to use AWS Fraud to validate the account
             */
        }

        return $customer;
    }

    /**
     * Get tab's label
     * @return Phrase
     */
    public function getTabLabel() : Phrase
    {
        return __('Aws Fraud Detector');
    }

    /**
     * Get tab title
     * @return Phrase
     */
    public function getTabTitle() : Phrase
    {
        return __('Aws Fraud Detector');
    }

    /**
     * Can we show the tab?
     * @return bool
     */
    public function canShowTab() : bool
    {
        if ($this->getCustomerId()) {
            return true;
        }
        return false;
    }

    /**
     * Is hidden?
     * @return bool
     */
    public function isHidden() : bool
    {
        if ($this->getCustomerId()) {
            return false;
        }
        return true;
    }

    /**
     * Get tab's css class
     * @return string
     */
    public function getTabClass() : string
    {
        return '';
    }

    /**
     * Url used to render the tab
     * @return string
     */
    public function getTabUrl() : string
    {
        return $this->getUrl('awsfraud/customer/index', ['_current' => true]);
    }

    /**
     * Is tab loaded using ajax?
     * @return bool
     */
    public function isAjaxLoaded() : bool
    {
        return true;
    }

    /**
     * Get AWS logo url
     * @return string
     */
    public function getAwsLogoUrl() : string
    {
        return $this->assetRepository->getUrl("ImaginationMedia_AwsFraud/images/AWS_4923041.png");
    }
}
