<?php

namespace App\Http\Controllers\TG;

use danog\MadelineProto\MyTelegramOrgWrapper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Telegram extends Controller
{
    //
    public function login(Request $request)
    {
        $settings = [
            'app_info' => [
                    'api_id' => 'app_id',
                    'api_hash' => 'api_hash'
                ]
        ];
        $wrapper = new MyTelegramOrgWrapper($settings);
        $wrapper->async(true);

        yield $wrapper->login(yield $wrapper->readline('Enter your phone number (this number must already be signed up to telegram)'));

        yield $wrapper->completeLogin(yield $wrapper->readline('Enter the code'));

        if (yield $wrapper->loggedIn()) {
            if (yield $wrapper->hasApp()) {
                $app = yield $wrapper->getApp();
            } else {
                $app_title = yield $wrapper->readLine('Enter the app\'s name, can be anything: ');
                $short_name = yield $wrapper->readLine('Enter the app\'s short name, can be anything: ');
                $url = yield $wrapper->readLine('Enter the app/website\'s URL, or t.me/yourusername: ');
                $description = yield $wrapper->readLine('Describe your app: ');
        
                $app = yield $wrapper->createApp(['app_title' => $app_title, 'app_shortname' => $short_name, 'app_url' => $url, 'app_platform' => 'web', 'app_desc' => $description]);
            }
    
            \danog\MadelineProto\Logger::log($app);
        }

        return 'hello';
    }
}
