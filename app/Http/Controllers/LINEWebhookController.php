<?php

namespace App\Http\Controllers;

//use Illuminate\Http\Request;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

use App\Models\User;

class LINEWebhookController extends Controller
{
    protected $client;
    protected $user;
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        //return ('hello line-bot-simed');
        $this->baseEndpoint = config('services.line.base_endpoint');

        $this->client = Http::withToken(config('services.line.bot_token'));

        if (! Request::has('events')) { // this should never happend
            Log::error('LINE bad response');

            return abort(400);
        }

        foreach (Request::input('events') as $event) {
           // $this->user = User::where('profile->social->id', $event['source']['userId'])->first();
            if ($event['type'] == 'follow') {
                $this->follow($event);
            } elseif ($event['type'] == 'unfollow') {
                $this->unfollow($event);
            } elseif ($event['type'] == 'message') {
                $this->message($event);
            } elseif ($event['type'] == 'unsend') {
                //
            } else {
                // unhandle type
            }
        }

        //return Request::input('events');
    }
    protected function follow($event)
    {
        Log::info('guest unfollow LINE bot '.$event['type']);
        Log::info('guest unfollow LINE bot '.$event['mode']);
        Log::info('guest unfollow LINE bot '.$event['timestamp']);
        Log::info('guest unfollow LINE bot '.$event['source']['userId']);
        return 'guest follow LINE bot';
        // get profile
        $profile = $this->getProfile($event['source']['userId']);
     
        if (! $this->user) {
            Log::info('guest add LINE bot '.$event['source']['userId']);
            $this->replyUnauthorized($event['replyToken'], $profile['displayName']);

            return;
        }

        // reply
        if ($this->user->getNotificationChannel() === null) {
            $this->user->setNotificationChannel('line', $event['source']['userId']);
            $this->replyMessage($event['replyToken'], [[
                'type' => 'text',
                'text' => __('reply_messages.bot.greeting', ['PLACEHOLDER' => $this->user->profile['full_name']]),
            ]]);
        }

        // need save or update profile
    }

    protected function unfollow($event)
    {
        Log::info('guest unfollow LINE bot '.$event['type']);
        Log::info('guest unfollow LINE bot '.$event['mode']);
        Log::info('guest unfollow LINE bot '.$event['timestamp']);
        Log::info('guest unfollow LINE bot '.$event['source']['userId']);
        return 'guest unfollow LINE bot';

        if ($this->user) {
            $this->user->disableNotificationChannel('line');
        } else {
            Log::info('guest '.$event['source']['userId'].' unsubscribed LINE bot');
        }
    }

    protected function message($event)
    {
        Log::info('guest unfollow LINE bot '.$event['type']);
        Log::info('guest unfollow LINE bot '.$event['mode']);
        Log::info('guest unfollow LINE bot '.$event['timestamp']);
        Log::info('guest unfollow LINE bot '.$event['source']['userId']);
        Log::info('guest unfollow LINE bot '.$event['message']['type']);
        Log::info('guest unfollow LINE bot '.$event['message']['text']);
        return 'guest message LINE bot';

        if (! $this->user) {
            $profile = $this->getProfile($event['source']['userId']);
            $this->replyUnauthorized($event['replyToken'], $profile['displayName']);
        }
        $messages = [[
            'type' => 'text',
            'text' => $event['message']['text'],
        ]];
        $this->replyMessage($event['replyToken'], $messages);
    }

    protected function replyMessage($replyToken, $messages)
    {
        $this->client->post($this->baseEndpoint.'message/reply', [
            'replyToken' => $replyToken,
            'messages' => $messages,
        ]);
    }

    protected function pushMessage($userId, $messages)
    {
        $this->client->post($this->baseEndpoint.'message/push', [
            'to' => $userId,
            'messages' => $messages,
        ]);
    }

    protected function getProfile($userId)
    {
        $response = $this->client->get($this->baseEndpoint.'profile/'.$userId);

        return $response->json();
    }

    protected function replyUnauthorized($token, $username)
    {
        $this->replyMessage($token, [[
            'type' => 'text',
            'text' => str_replace('PLACEHOLDER', $username, config('messages.bot_user_not_registred'))."\n\n เมื่อทำการลงทะเบียนแล้วอย่าลืม block และ unblock bot ด้วยน๊า 🤗",
            'text' => __('reply_messages.bot.user_not_registered', ['PLACEHOLDER' => $username, 'STOP' => 'block', 'RESTART' => 'unblock']),
        ]]);
    }
}
