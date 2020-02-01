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

namespace ImaginationMedia\AwsFraud\Controller\Adminhtml\Customer;

use Magento\Customer\Controller\Adminhtml\Index as CustomerIndex;
use Magento\Framework\View\Result\Layout;

class Index extends CustomerIndex
{
    public const ADMIN_RESOURCE = "ImaginationMedia_AwsFraud::settings";

    /**
     * @return Layout
     */
    public function execute() : Layout
    {
        $this->initCurrentCustomer();
        return $this->resultLayoutFactory->create();
    }
}
