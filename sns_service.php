<?php
require 'vendor/autoload.php';


use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;

// Create SNS client
$sns = new SnsClient([
    'region'  => 'us-east-1',   // your region
    'version' => '2010-03-31'
]);

// Your SNS topic ARN
$topicArn = 'arn:aws:sns:*:211125691466:MyTopic';

function sendMessage($header, $body) {
    global $sns, $topicArn;

    try {
        $result = $sns->publish([
            'Subject' => $header,
            'Message' => $body,
            'TopicArn' => $topicArn,
        ]);
        echo "Message sent! ID: " . $result['MessageId'];
    } catch (AwsException $e) {
        echo "Error sending message: " . $e->getMessage();
    }
}

