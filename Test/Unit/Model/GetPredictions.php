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

namespace ImaginationMedia\AwsFraud\Test\Unit\Model;

use Aws\FraudDetector\FraudDetectorClient;
use PHPUnit\Framework\TestCase;

class GetPredictions extends TestCase
{
    /**
     * Test prediction
     */
    public function testPrediction() : void
    {
        $fraudClient = new FraudDetectorClient([
            'profile' => "default",
            'version' => "latest",
            'region' => "us-east-1"
        ]);

        $score = $fraudClient->getPrediction([
            "detectorId" => "review-fraud",
            "eventAttributes" => [
                "email_address" => "test@test.com",
                "ip_address" => "127.0.0.1",
                "event_timestamp" => "1580512424"
            ],
            "eventId" => "review-fraud"
        ]);

        $this->assertNotNull($score);
    }
}
