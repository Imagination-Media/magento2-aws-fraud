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

namespace ImaginationMedia\AwsFraud\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddCustomerAttributes implements DataPatchInterface
{
    const CUSTOMER_ATTRIBUTE_AWS_FRAUD_FLAG = "aws_fraud_flag";
    const CUSTOMER_ATTRIBUTE_AWS_FRAUD_RATE = "aws_fraud_rate";

    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var EavConfig
     */
    protected $eavConfig;

    /**
     * AddCustomerAttributes constructor.
     * @param EavSetupFactory $eavSetupFactory
     * @param EavConfig $eavConfig
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        EavConfig $eavConfig
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * No aliases
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * No dependencies
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    public function apply()
    {
        /**
         * @var $eavSetup EavSetup
         */
        $eavSetup = $this->eavSetupFactory->create();

        /**
         * Add AWS Fraud Flag to customer entity
         */
        $eavSetup->addAttribute(
            Customer::ENTITY,
            self::CUSTOMER_ATTRIBUTE_AWS_FRAUD_FLAG,
            [
                'type'                  => 'varchar',
                'label'                 => 'Fraud Flag (AWS)',
                'input'                 => 'text',
                'required'              => false,
                'visible'               => true,
                'user_defined'          => true,
                'position'              => 10001,
                'system'                => 0,
                'is_used_in_grid'       => 1,
                'is_visible_in_grid'    => 1
            ]
        );
        $fraudFlag = $this->eavConfig->getAttribute(Customer::ENTITY, self::CUSTOMER_ATTRIBUTE_AWS_FRAUD_FLAG);
        $fraudFlag->setData(
            'used_in_forms',
            ['adminhtml_customer']

        );
        $fraudFlag->save();

        /**
         * Add AWS Fraud Rate to customer entity
         */
        $eavSetup->addAttribute(
            Customer::ENTITY,
            self::CUSTOMER_ATTRIBUTE_AWS_FRAUD_RATE,
            [
                'type'                  => 'int',
                'label'                 => 'Fraud Rate (AWS)',
                'input'                 => 'text',
                'required'              => false,
                'visible'               => true,
                'user_defined'          => true,
                'position'              => 10002,
                'system'                => 0,
                'is_used_in_grid'       => 1,
                'is_visible_in_grid'    => 1
            ]
        );
        $fraudRate = $this->eavConfig->getAttribute(Customer::ENTITY, self::CUSTOMER_ATTRIBUTE_AWS_FRAUD_RATE);
        $fraudRate->setData(
            'used_in_forms',
            ['adminhtml_customer']

        );
        $fraudRate->save();
    }
}
