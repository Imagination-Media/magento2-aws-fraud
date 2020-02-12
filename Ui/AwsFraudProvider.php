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

namespace ImaginationMedia\AwsFraud\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\UrlInterface;

class AwsFraudProvider implements ConfigProviderInterface
{
    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * AwsFraudProvider constructor.
     * @param UrlInterface $url
     */
    public function __construct(
        UrlInterface $url
    ) {
        $this->url = $url;
    }

    /**
     * Get config to be used on checkout validator
     * @return array
     */
    public function getConfig()
    {
        return [
            'aws_fraud' => [
                'validator' => $this->url->getUrl('awsfraud/checkout/validator')
            ]
        ];
    }
}
