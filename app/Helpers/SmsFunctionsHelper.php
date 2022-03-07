<?php

namespace App\Helpers;

use Exception;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use infobip\api\client\SendMultipleTextualSmsAdvanced;
use infobip\api\configuration\BasicAuthConfiguration;
use infobip\api\model\Destination;
use infobip\api\model\sms\mt\send\Message;
use infobip\api\model\sms\mt\send\textual\SMSAdvancedTextualRequest;

class SmsFunctionsHelper
{
    static function sendMessage($phonenumber, $messageText, $messageId = 1)
    {

        $configuration = new BasicAuthConfiguration(env('INFOBIP_USERNAME'), env('INFOBIP_PASSWORD'), 'http://api.infobip.com/');
        $client = new SendMultipleTextualSmsAdvanced($configuration);

        $destination = new Destination();
        $destination->setTo($phonenumber);
        $destination->setMessageId($messageId);


        $message = new Message();
        $message->setDestinations([$destination]);
        $message->setFrom(env('INFOBIP_FORM'));
        $message->setText($messageText);
        //$message->setNotifyUrl($_POST['notifyUrlInput']);
        //$message->setNotifyContentType($_POST['notifyContentTypeInput']);
        //$message->setCallbackData($_POST['callbackDataInput']);

        $requestBody = new SMSAdvancedTextualRequest();
        $requestBody->setMessages([$message]);

        $response = $client->execute($requestBody);
        return 1;
    }
}
