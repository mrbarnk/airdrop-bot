<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;



class TelegramController extends Controller
{

    // https://api.telegram.org/bot1884100401:AAEwF5rBg6ZkU94EeNwyZdhdpv-5684ArQ0/setWebhook?url=https://telegram.waabot.com/public/botman
    
    protected $messages = [];

    private $errorMessage = "\u{274c} Unknown Command!

    You have send a Message directly into the Bot's chat or
    Menu structure has been modified by Admin.
    
    \u{2139} Do not send Messages directly to the Bot or
    reload the Menu by pressing /start";

    public function __construct() {
        $messages = ([
            'hi' => 'hello',
            '/start' => "
Hello, Bankole! I am your friendly Refract Airdrop bot

\u{2705}Please do the required tasks to be eligible to get airdrop tokens.

\u{1f539}1 RFR = 615.85 USD
\u{1f538}For Joining - Get 0.44 RFR
\u{2b50} For each referral - Get 0.01 RFR

\u{1f4d8}By Participating you are agreeing to the Refract (Airdrop) Program Terms and Conditions. Please see pinned post for more information.”

Click \"Join Airdrop\" to proceed\"
(button)[Join Airdrop]",
"Join Airdrop" => "
\u{2139} You were invited by user Ezekiel.
---
\u{1f51d} Main Menu
---
\u{2b50} 1 token = $615.85

---

\u{1f51d} Main Menu
\u{2b50} 1 token = $615.85

\u{1f539} Total to earn per participant (if you complete all the tasks) =0.44 RFR ($300)
\u{1f539} Per referral = 0.01 RFR ($7)

\u{1f4c5} Airdrop end date: June 16th
\u{1f3e6} Distribution date: June 26th

\u{1f4e2} Airdrop Rules

\u{270f} Mandatory Tasks:
\u{1f539} Join our telegram group and channel
\u{1f539}Follow us on Twitter, like and retweet pinned message

(button)[\u{1f4d8} Submit my details]
            ",
            '📘 Submit my details' => "
\u{1f539} Join our telegram group and channel
(button)[D O N E]",
            ':wink: Done' => "\u{1f539} Follow us on Twitter, like and retweet pinned message

            Submit your Twitter profile link (Example: https://www.twitter.com/yourusername)',
            
            'Https://www.twitter.com/mrbarnk' => '[Photo]
            Submit Address ERC20 (Ethereum)
            
            You can find this wallet address at Binance and Trustwallet',

            '0xDd5eDa67A50FAe4156DEE440Aa79675477caFC0e' => '
\u{1f539}Join Advertiser Telegram Channel 
\u{1f539}Follow Advertiser twitter , like and retweet airdrop post.

Submit your retweeted link.",

            'https://twitter.com/mrbarnk/twitted_link' => "Thankyou Bankole ! 

Don\'t forget to:
\u{1f538} Stay in the telegram channels
\u{1f538} Follow all the social media channels 

Your personal referral link:
https://t.me/refractRFRbot?start=r04951003650

(button)[\u{1f4ca} Statistics]",
    '\u{1f4ca} Statistics' => "\u{1f4ca} Referral Balance: 0 tokens
Tokens for joining Social Media will be updated after verifying manually by bounty manager at the end of airdrop.

\u{1f4ce} Referral link: https://t.me/refractRFRbot?start=r04951003650

\u{1f46c} Referrals: 0

Your details:
-------------------

Telegram: Mrbarnk 
Twitter: Https://www.twitter.com/mrbarnk
ERC20 wallet: 0xDd5eDa67A50FAe4156DEE440Aa79675477caFC0e

If your submitted data wrong then you can restart the bot and resubmit the data again by clicking /start before airdrop end."
]);

        $this->messages = $messages;
    }
    public function handle(Request $request)
    {
        // return $request->getContent();
        $request = json_decode(file_get_contents('php://input'), true);
        // dd($request['message']);
        // return $request;
        

        // return $this->messages[$request->message['text']];
        $config = [
            // Your driver-specific configuration
            "telegram" => [
               "token" => "1884100401:AAEwF5rBg6ZkU94EeNwyZdhdpv-5684ArQ0"
            ]
        ];
        
        // Load the driver(s) you want to use
        DriverManager::loadDriver(\BotMan\Drivers\Telegram\TelegramDriver::class);

        // Create an instance
        $botman = BotManFactory::create($config);

        // Give the bot something to listen for.
        try {
            if (!$request['message']['text']) throw new \Exception("Error Processing Request", 1);
            
            $botman->hears($request['message']['text'], function (BotMan $bot) use ($request) {
                try {
                    $messages = $this->messages[$request['message']['text']];
                } catch (\Throwable $th) {
                    $bot->reply($this->errorMessage);
                    return;
                }
    
                $messages = explode('---', $messages);
                $reg = '/\(button\)\[(.*)]/m';

                preg_match_all($reg, $this->messages[$request['message']['text']], $matches);
                
                $messageToAsk = "test";
                $keyboard = [$matches[1]];

                if ($matches[1]) {
                    $messageToAsk = end($messages);
                    $messageToAsk = preg_replace($reg, "", $messageToAsk);
                    // unset($messages[count($messages)-1]);
                }

                for ($i=0; $i < count($messages); $i++) {
                    // $bot->reply($this->getEmojiInTexts($messages[$i]));
                    $bot->reply(preg_replace($reg, "", $messages[$i]));
                }
                
    
                
                if ($messageToAsk != 'test') {
                    $bot->ask($messageToAsk,
                        function (Answer $answer) {
                            $bot->askAdress();
                        }, ['reply_markup' => json_encode([
                            'keyboard' => $keyboard,
                            'one_time_keyboard' => true,
                            'resize_keyboard' => true
                        ])]
                    );
                }
            });
        } catch (\Throwable $th) {
            //throw $th;
        }

        $botman->fallback(function($bot) {
            $bot->reply($this->errorMessage);
        });

        // Start listening
        $botman->listen();

    }

    public function getEmojiInTexts($str)
    {
        $str = $this->messages['/start'];
        $str = '(button)[\u{1f4d8} Submit my details]

        (button)[\u{1f4d8} Submit my details2]';

        $reg = '/\(button\)\[\\\\(.*)\]/m';

        preg_match_all($reg, $str, $matches);//, PREG_SET_ORDER, 0);

        // Print the entire match result
        return ([$matches]);
        return;

        $re = '/:(.*?):/m';
        

        $words = preg_split('/[,;\s]+/', $str, -1, PREG_SPLIT_NO_EMPTY);
        // return $words[2];

        // Print the entire match result
        // return $matches;
        try {
            // $emojis = $explode(' ', $str);
        
            $returnValue = '';
            foreach ($words as $key => $value) {
                preg_match_all($re, $value, $matches, PREG_SET_ORDER, 0);
                echo json_encode($matches);
                // echo $value . " | ". Emojies::getEmoji()[$value] ."<br />";
                try {
                    // echo $value." | ". Emojies::getEmoji()[$value] . "<br />";
                    $str = str_replace($value, Emojies::getEmoji()[$value], $str);
                } catch (\Throwable $th) {
                    echo $th->getMessage();
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
            // var_dump($matches);
        }
        return ;$str;

    }
}
