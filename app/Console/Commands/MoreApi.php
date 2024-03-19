<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class MoreApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'more-api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'moreApi测试使用';

    private string $baseUrl = 'https://pro.moreapi.wouldmissyou.com';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $token = $this->init();

        $this->getCookie($token);
    }

    /**
     * @throws \Exception
     */
    public function init()
    {
        if (Cache::has('tokens')) {
            $tokens = Cache::get('tokens');
        } else {
            $mobiles = ['15639276608', '16537914257'];
            $tokens  = [];
            foreach ($mobiles as $mobile) {
                $info = Http::retry(3, 100)->withHeaders([])->post($this->baseUrl.'/api/auth/login', ['mobile' => $mobile, 'password' => '123456'])->json();
                if (Arr::get($info, 'code') == 200) {
                    $token    = Arr::get($info, 'data.authorization_token.token');
                    $tokens[] = $token;
                    Http::retry(3, 100)->withHeaders(['Authorization' => 'Bearer '.$token])->get($this->baseUrl.'/api/auth/daily_check_in')->json();
                }
            }
            Cache::put('tokens', $tokens, 60 * 30);
        }
        $num = random_int(0, count($tokens) - 1);
        return $tokens[$num];
    }
}