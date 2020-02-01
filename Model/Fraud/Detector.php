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

namespace ImaginationMedia\AwsFraud\Model\Fraud;

use Aws\FraudDetector\FraudDetectorClient;
use ImaginationMedia\AwsFraud\Model\System\Config;
use ImaginationMedia\AwsFraud\Setup\Patch\Data\AddCustomerAttributes;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

class Detector
{
    const AWS_FLAG_FRAUD     = "fraud";
    const AWS_FLAG_NOT_FRAUD = "not_fraud";

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var FraudDetectorClient
     */
    protected $fraudClient;

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var CustomerResource
     */
    protected $customerResource;

    /**
     * Detector constructor.
     * @param Config $config
     * @param ResourceConnection $resourceConnection
     * @param CustomerResource $customerResource
     */
    public function __construct(
        Config $config,
        ResourceConnection $resourceConnection,
        CustomerResource $customerResource
    ) {
        $this->config = $config;
        $this->customerResource = $customerResource;
        $this->connection = $resourceConnection->getConnection();

        if ($config->isEnabled()) {
            $this->fraudClient = new FraudDetectorClient([
                'profile' => $this->config->getProfile(),
                'version' => $this->config->getVersion(),
                'region' => $this->config->getRegion()
            ]);
        }
    }

    /**
     * Get customer IP
     * @param int $customerId
     * @return string
     */
    public function getCustomerIp(int $customerId) : string
    {
        $quoteTable = $this->connection->getTableName("quote");
        $query = $this->connection->select()->from(
            $quoteTable,
            ["remote_ip"]
        )->where($quoteTable . ".customer_id = " . $customerId)
        ->order("entity_id DESC");

        $result = $this->connection->fetchOne($query);

        return ($result) ? $result : "127.0.0.1";
    }

    /**
     * @param array $customerData
     * @param Customer|null $customer
     * @return int
     * @throws \Exception
     */
    public function getCustomerFraudScore(array $customerData, ?Customer $customer): int
    {
        $score = $this->fraudClient->getPrediction([
            "detectorId" => $this->config->getDetector(),
            "eventAttributes" => [
                "email_address" => $customerData["email_address"],
                "ip_address" => $customerData["ip_address"],
                "event_timestamp" => (string)$customerData["event_timestamp"]
            ],
            "eventId" => $this->config->getEvent()
        ]);

        if (isset($score['modelScores'][0]['scores'])) {
            $scoreNumber = (int)reset($score['modelScores'][0]['scores']);
        } else {
            throw new \Error(__("Invalid score number"));
        }

        if ($customer) {
            $customer->setData(AddCustomerAttributes::CUSTOMER_ATTRIBUTE_AWS_FRAUD_RATE, $scoreNumber);
            $this->customerResource->saveAttribute($customer, AddCustomerAttributes::CUSTOMER_ATTRIBUTE_AWS_FRAUD_RATE);

            if ($scoreNumber > $this->config->getAutoRate()) {
                $customer->setData(AddCustomerAttributes::CUSTOMER_ATTRIBUTE_AWS_FRAUD_FLAG, "fraud");
            } else {
                $customer->setData(AddCustomerAttributes::CUSTOMER_ATTRIBUTE_AWS_FRAUD_FLAG, "not_fraud");
            }
            $this->customerResource->saveAttribute($customer, AddCustomerAttributes::CUSTOMER_ATTRIBUTE_AWS_FRAUD_FLAG);
        }

        return $scoreNumber;
    }
}
