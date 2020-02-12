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
use ImaginationMedia\AwsFraud\Setup\Patch\Data\AddCustomerAttributes;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Controller\Account\LoginPost as AccountLoginPost;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;

class LoginPost extends AccountLoginPost
{
    /**
     * @var CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Detector
     */
    protected $detector;

    /**
     * LoginPost constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerUrl $customerHelperData
     * @param Validator $formKeyValidator
     * @param AccountRedirect $accountRedirect
     * @param CollectionFactory $collectionFactory
     * @param Config $config
     * @param Detector $detector
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        CustomerUrl $customerHelperData,
        Validator $formKeyValidator,
        AccountRedirect $accountRedirect,
        CollectionFactory $collectionFactory,
        Config $config,
        Detector $detector
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $customerAccountManagement,
            $customerHelperData,
            $formKeyValidator,
            $accountRedirect
        );
        $this->customerCollectionFactory = $collectionFactory;
        $this->config = $config;
        $this->detector = $detector;
    }

    /**
     * @return Redirect
     * @throws LocalizedException
     */
    public function execute()
    {
        if ($this->config->isEnabled()) {
            $login = $this->getRequest()->getPost('login');
            $customerCollection = $this->customerCollectionFactory->create()
                ->addAttributeToSelect([
                    "email",
                    AddCustomerAttributes::CUSTOMER_ATTRIBUTE_AWS_FRAUD_RATE,
                    AddCustomerAttributes::CUSTOMER_ATTRIBUTE_AWS_FRAUD_FLAG
                ])->addAttributeToFilter("email", $login['username']);

            if ($customerCollection->count() > 0) {
                $customer = $customerCollection->getFirstItem();
                if ($customer->getData(AddCustomerAttributes::CUSTOMER_ATTRIBUTE_AWS_FRAUD_FLAG)
                    === Detector::AWS_FLAG_FRAUD) {
                    $this->messageManager->addErrorMessage(
                        __("This account was flagged as fraud and you are blocked to login. Please contact us.")
                    );
                    $url = $this->_url->getUrl('*/*/login', ['_secure' => true]);
                    return $this->resultRedirectFactory->create()
                        ->setUrl($this->_redirect->error($url));
                }
            }
        }
        return parent::execute();
    }
}
