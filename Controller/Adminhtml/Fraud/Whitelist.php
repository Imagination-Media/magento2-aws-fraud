<?php

declare(strict_types=1);

namespace ImaginationMedia\AwsFraud\Controller\Adminhtml\Fraud;

use ImaginationMedia\AwsFraud\Setup\Patch\Data\AddCustomerAttributes;
use Magento\Backend\App\Action;
use Magento\Customer\Model\ResourceModel\Customer as ResourceModel;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

class Whitelist extends Action
{
    public const ADMIN_RESOURCE = "ImaginationMedia_AwsFraud::settings";

    /**
     * @var ResourceModel
     */
    protected $resourceModel;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Whitelist constructor.
     * @param Action\Context $context
     * @param ResourceModel $resourceModel
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Action\Context $context,
        ResourceModel $resourceModel,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->resourceModel = $resourceModel;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        if ($this->getRequest()->getParam("customer_id")) {
            $customer = $this->collectionFactory->create()
                ->addAttributeToFilter("entity_id", $this->getRequest()->getParam("customer_id"))
                ->addAttributeToSelect([
                    "entity_id",
                    "email",
                    AddCustomerAttributes::CUSTOMER_ATTRIBUTE_AWS_FRAUD_RATE,
                    AddCustomerAttributes::CUSTOMER_ATTRIBUTE_AWS_FRAUD_FLAG
                ])
                ->getFirstItem();

            $customer->setData(AddCustomerAttributes::CUSTOMER_ATTRIBUTE_AWS_FRAUD_FLAG, "not_fraud");
            $this->resourceModel->saveAttribute($customer, AddCustomerAttributes::CUSTOMER_ATTRIBUTE_AWS_FRAUD_FLAG);
            $this->messageManager->addSuccessMessage(__("The customer was set as a non fraud account."));
        } else {
            $this->messageManager->addErrorMessage(__("Please provide a valid customer id."));
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
