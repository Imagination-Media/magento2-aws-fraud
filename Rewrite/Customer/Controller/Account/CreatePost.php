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

namespace ImaginationMedia\AwsFraud\Rewrite\Customer\Controller\Account;

use ImaginationMedia\AwsFraud\Model\Fraud\Detector;
use ImaginationMedia\AwsFraud\Model\System\Config;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Controller\Account\CreatePost as CoreCreatePost;
use Magento\Customer\Helper\Address;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Customer\Model\Registration;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\UrlFactory;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Model\StoreManagerInterface;

class CreatePost extends CoreCreatePost
{
    /**
     * @var Detector
     */
    protected $fraudDetector;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var RemoteAddress
     */
    protected $remoteAddress;

    /**
     * CreatePost constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param AccountManagementInterface $accountManagement
     * @param Address $addressHelper
     * @param UrlFactory $urlFactory
     * @param FormFactory $formFactory
     * @param SubscriberFactory $subscriberFactory
     * @param RegionInterfaceFactory $regionDataFactory
     * @param AddressInterfaceFactory $addressDataFactory
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param CustomerUrl $customerUrl
     * @param Registration $registration
     * @param Escaper $escaper
     * @param CustomerExtractor $customerExtractor
     * @param DataObjectHelper $dataObjectHelper
     * @param AccountRedirect $accountRedirect
     * @param CustomerRepository $customerRepository
     * @param Detector $fraudDetector
     * @param Config $config
     * @param RemoteAddress $remoteAddress
     * @param Validator|null $formKeyValidator
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        AccountManagementInterface $accountManagement,
        Address $addressHelper,
        UrlFactory $urlFactory,
        FormFactory $formFactory,
        SubscriberFactory $subscriberFactory,
        RegionInterfaceFactory $regionDataFactory,
        AddressInterfaceFactory $addressDataFactory,
        CustomerInterfaceFactory $customerDataFactory,
        CustomerUrl $customerUrl,
        Registration $registration,
        Escaper $escaper,
        CustomerExtractor $customerExtractor,
        DataObjectHelper $dataObjectHelper,
        AccountRedirect $accountRedirect,
        CustomerRepository $customerRepository,
        Detector $fraudDetector,
        Config $config,
        RemoteAddress $remoteAddress,
        Validator $formKeyValidator = null
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $scopeConfig,
            $storeManager,
            $accountManagement,
            $addressHelper,
            $urlFactory,
            $formFactory,
            $subscriberFactory,
            $regionDataFactory,
            $addressDataFactory,
            $customerDataFactory,
            $customerUrl,
            $registration,
            $escaper,
            $customerExtractor,
            $dataObjectHelper,
            $accountRedirect,
            $customerRepository,
            $formKeyValidator
        );
        $this->fraudDetector = $fraudDetector;
        $this->config = $config;
        $this->remoteAddress = $remoteAddress;
    }

    /**
     * Validate data before we proceed to the account creation
     */
    public function execute()
    {
        if ($this->config->isEnabled()) {
            $email = $this->getRequest()->getParam("email");
            $ipAddress = $this->remoteAddress->getRemoteAddress();
            $timestamp = $this->fraudDetector->getCurrentTimestamp();

            $scoreNumber = $this->fraudDetector->getCustomerFraudScore([
                "email_address" => $email,
                "ip_address" => $ipAddress,
                "event_timestamp" => (string)$timestamp
            ]);

            if ($scoreNumber >= $this->config->getAutoRate()) {
                $this->messageManager->addErrorMessage(
                    __("This account was flagged as fraud and can't be created. Please contact us.")
                );
                $url = $this->urlModel->getUrl('*/*/create', ['_secure' => true]);
                return $this->resultRedirectFactory->create()
                    ->setUrl($this->_redirect->error($url));
            }
        }

        parent::execute();
    }
}
