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

namespace ImaginationMedia\AwsFraud\Controller\Checkout;

use ImaginationMedia\AwsFraud\Model\Fraud\Detector;
use ImaginationMedia\AwsFraud\Model\System\Config;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

class Validator extends Action
{
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var Detector
     */
    protected $detector;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var RemoteAddress
     */
    protected $remoteAddress;

    /**
     * Validator constructor.
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Cart $cart
     * @param RemoteAddress $remoteAddress
     * @param Detector $detector
     * @param Config $config
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Cart $cart,
        RemoteAddress $remoteAddress,
        Detector $detector,
        Config $config
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->cart = $cart;
        $this->remoteAddress = $remoteAddress;
        $this->detector = $detector;
        $this->config = $config;
    }

    /**
     * @return Json
     * @throws \Exception
     */
    public function execute()
    {
        $result = true;
        if (!$this->cart->getQuote()->getCustomerId() && $this->getRequest()->getParam("customer_email")) {
            $email = $this->getRequest()->getParam("customer_email");
            $ipAddress = $this->remoteAddress->getRemoteAddress();
            $timestamp = $this->detector->getCurrentTimestamp();

            $scoreNumber = $this->detector->getCustomerFraudScore([
                "email_address" => $email,
                "ip_address" => $ipAddress,
                "event_timestamp" => (string)$timestamp
            ]);

            $result = ($scoreNumber < $this->config->getAutoRate());
        }

        $jsonResult = $this->jsonFactory->create();
        $jsonResult->setData([
            'result' => $result
        ]);
        return $jsonResult;
    }
}
