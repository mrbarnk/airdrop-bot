<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Chats;
use BotMan\BotMan\BotMan;
use App\Models\TelegramRequests;
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

    public function __construct()
    {
        $welcomeMsg = "😇 Welcome
Reply with <b>/start</b>. (button)[/start]";
        $messages = ([
            'hi' => $welcomeMsg,
            'Hi' => $welcomeMsg,
            'hello' => $welcomeMsg,
            'Hello' => $welcomeMsg,
            '/start' => "
Hello, {first_name}! I am your friendly Refract Airdrop bot

\u{2705}Please do the required tasks to be eligible to get airdrop tokens.

\u{1f539}1 RFR = 615.85 USD
\u{1f538}For Joining - Get 0.44 RFR
\u{2b50} For each referral - Get 0.01 RFR

\u{1f4d8}By Participating you are agreeing to the Refract (Airdrop) Program Terms and Conditions. Please see pinned post for more information.”

Click \"Join Airdrop\" to proceed\"
(button)[Join Airdrop]",
"Join Airdrop" => "
\u{2139} You were invited by user {referred_by}.
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
            'D O N E' => "\u{1f539} Follow us on Twitter, like and retweet pinned message

Submit your Twitter profile link (Example: https://www.twitter.com/yourusername)",

'twitter_profile' => "[Photo]
Submit Address ERC20 (Ethereum)

You can find this wallet address at Binance and Trustwallet",

            "wallet_address" => "
\u{1f539} Join Advertiser Telegram Channel 
\u{1f539} Follow Advertiser twitter , like and retweet airdrop post.

Submit your retweeted link.",

"twitted_link" => "Thankyou {first_name} ! 

Don't forget to:
\u{1f538} Stay in the telegram channels
\u{1f538} Follow all the social media channels 

Your personal referral link:
https://t.me/eti_airdrop_bot?start=r{chat_id}

(button)[📊 Statistics]",
    '📊 Statistics' => "\u{1f4ca} Referral Balance: {ammount_earned_from_referral} tokens
Tokens for joining Social Media will be updated after verifying manually by bounty manager at the end of airdrop.

\u{1f4ce} Referral link: https://t.me/eti_airdrop_bot?start=r{chat_id}

\u{1f46c} Referrals: {ammount_referred}

Your details:
________________

<b>Telegram: {username}</b>
<b>Twitter: {twitter_profile_link}</b>
<b>ERC20 wallet</b>: <i>{coin_address}</i>

<i>If your submitted data wrong then you can restart the bot and resubmit the data again by clicking /start before airdrop end.</i>"
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

        // try {
            if (!$request['message']['chat']) {
                return;
            }
            TelegramRequests::create(['user_id' => $request['message']['chat']['id'], 'request' => json_encode($request)]);

            $first = Chats::where([
            'chat_id' => $request['message']['chat']['id'],
            ]);
            if ($first->count() == 0) {
                Chats::create([
                'chat_id' => $request['message']['chat']['id'],
                'first_name' => $this->getArrayKey($request['message']['chat'], 'first_name'),
                'last_name' => $this->getArrayKey($request['message']['chat'], 'last_name'),
                'username' => $this->getArrayKey($request['message']['chat'], 'username'),
                'referred_by' => '',
                'twitter_link' => '',
                'twitter_profile_link' => '',
                'ammount_referred' => 0,
                'coin_address' => ''
            ]);
            } else {
                Chats::where(['chat_id' => $request['message']['chat']['id']])->update([
                'first_name' => $this->getArrayKey($request['message']['chat'], 'first_name'),
                'last_name' => $this->getArrayKey($request['message']['chat'], 'last_name'),
                'username' => $this->getArrayKey($request['message']['chat'], 'username'),
            ]);
                // dd($this->getArrayKey($request['message']['chat'], 'first_name'));
            }
        // } catch (\Throwable $th) {
        //     //throw $th;
        // }
        // Give the bot something to listen for.
        try {
            if (!$request['message']['text']) {
                throw new \Exception("Error Processing Request", 1);
            }
            if ($request['message']['text'] == ';-) Done') {
                return false;
            }//throw new \Exception("Error Processing Request", 1);

            

            $botman->hears($request['message']['text'], function (BotMan $bot) use ($request) {
                try {
                    $messages = $this->messages[$request['message']['text']];
                } catch (\Throwable $th) {
                    // $bot->reply($this->errorMessage);
                    $this->checkMsg2($request, $bot);
                    return;
                }
    
                $messages = explode('---', $messages);
                $this->replyChat($messages, $bot, $this->getUser($request));
            });
        } catch (\Throwable $th) {
            throw $th;
        }

        $botman->fallback(function ($bot) {
            $bot->reply($this->errorMessage);
        });

        // Start listening
        $botman->listen();
    }
    public function checkMsg2($request, $bot)
    {
        $re = '/(\/start r[0-9]+)/m';
        $str = $request['message']['text'];
        $twitterProfileRegex = '/http(?:s)?:\/\/(?:www\.)?twitter\.com\/([a-zA-Z0-9_]+)$/m';
        $twitterLinkRegex = '/^https?:\/\/twitter\.com\/([a-zA-Z0-9_]+)\/status(es)?\/(.*)/m';
        
        $user = $this->getUser($request);

        if ($str) {
            preg_match($re, $str, $matches, PREG_OFFSET_CAPTURE, 0);
            
            preg_match($twitterProfileRegex, $str, $twitterProfileLink, PREG_OFFSET_CAPTURE, 0);

            preg_match($twitterLinkRegex, $str, $twitterLink, PREG_OFFSET_CAPTURE, 0);

            try {
                if (count($matches) > 0) {
                    $messages = [$this->messages['/start']];
                    $arrays = explode(' ', $matches[1][0]);
                    $arrayMsg = end($arrays);

                    $referral = Chats::where(
                        [
                            'chat_id' => str_replace('r', '', $arrayMsg),
                        ]
                    )
                    ->first();
                    $this->updateReferral(str_replace('r', '', $arrayMsg), $request);
                            
                    $this->replyChat($messages, $bot, $user);
                
                    return;
                } elseif (count($twitterProfileLink) > 0) {
                    $messages = [$this->messages['twitter_profile']];
                    $this->updateTwitterProfileUrl($request, $twitterProfileLink);
                    $this->replyChat($messages, $bot, $user);
                } elseif (count($twitterLink) > 0) {
                    $messages = [$this->messages['twitted_link']];
                    $this->replyChat($messages, $bot, $user);
                    $this->updateTwitterLinkUrl($request, $twitterLink[0][0]);
                } elseif ((new EthereumValidator)->isAddress($str)) {
                    $messages = [$this->messages['wallet_address']];
                    $this->replyChat($messages, $bot, $user);
                    $this->updateWalletAddress($request, $str);
                } else {
                    $bot->reply($this->errorMessage);
                }
            } catch (\Throwable $th) {
                $bot->reply($this->errorMessage);
                return;
            }
        } else {
            $bot->reply($this->errorMessage);
        }
    }
    public function updateTwitterProfileUrl($request, $url)
    {
        return $this->getUser($request)->update(['twitter_profile_link' => $url]);
    }
    public function getArrayKey($array, $key)
    { 
        try {
            return $array[$key];
        } catch (\Throwable $th) {
            return "";
        }
    }
    public function updateTwitterLinkUrl($request, $url)
    {
        return $this->getUser($request)->update(['twitter_link' => $url]);
    }
    public function updateWalletAddress($request, $address)
    {
        return $this->getUser($request)->update(['coin_address' => $address]);
    }
    public function updateReferral($chat_id, $request)
    {
        $referralExist = Chats::where(['chat_id' => $chat_id])->first();
        // throw new \Exception(json_encode($this->getUser($request)), 1);
        if ($referralExist) {
            return $this->getUser($request)->update(['referred_by' => $chat_id]);
        }
        return;
    }
    public function getUser($request)
    {
        // throw new \Exception($request['message']['chat']['id'], 1);
        
        return Chats::where(['chat_id' => $request['message']['chat']['id']])->first();
    }

    public function referral($user)
    {
        return Chats::where(['chat_id' => $user->referred_by])->first();
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
        return ;
        $str;
    }

    public function replaceTexts($message, $user)
    {
        $referralusername = $this->referral($user) ? $this->referral($user)->username : "";

        $message = str_replace('{first_name}', $user->first_name, $message);
        $message = str_replace('{last_name}', $user->last_name, $message);
        $message = str_replace('{username}', $user->username, $message);
        $message = str_replace('{referred_by}', $referralusername, $message);
        $message = str_replace('{chat_id}', $user->chat_id, $message);
        $message = str_replace('{twitter_profile_link}', $user->twitter_profile_link, $message);
        $message = str_replace('{twitter_link}', $user->twitter_link, $message);
        $message = str_replace('{ammount_referred}', $user->ammount_referred, $message);
        $message = str_replace('{ammount_earned_from_referral}', $user->ammount_earned_from_referral, $message);
        $message = str_replace('{coin_address}', $user->coin_address, $message);


        return $message;
    }

    public function replyChat($messages, $bot, $user)
    {
        $reg = '/\(button\)\[(.*)]/m';

        preg_match_all($reg, $messages[count($messages)-1], $matches);
                
        $messageToAsk = "test";
        $keyboard = [$matches[1]];

        if ($matches[1]) {
            $messageToAsk = end($messages);
            $messageToAsk = preg_replace($reg, "", $messageToAsk);

            $referralusername = $this->referral($user) ? $this->referral($user)->username : "";
            
            $messageToAsk = $this->replaceTexts($messageToAsk, $user);
            
            unset($messages[count($messages)-1]);
        }

        for ($i=0; $i < count($messages); $i++) {
            // $bot->reply($this->getEmojiInTexts($messages[$i]));
            $messages[$i] = $this->replaceTexts($messages[$i], $user);
            

            $bot->reply(preg_replace($reg, "", $messages[$i]), ['parse_mode' => 'HTML']);
        }
                
    
                
        if ($messageToAsk != 'test') {
            $bot->ask(
                $messageToAsk,
                function (Answer $answer) {
                    $bot->askAdress();
                },
                [
                    'reply_markup' => json_encode([
                    'keyboard' => $keyboard,
                    'one_time_keyboard' => true,
                    'resize_keyboard' => true
                ]),
                    'parse_mode' => 'HTML'
                ]
            );
        }
    }
}
