<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RefreshFBuserTokne extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refresh:fbtoken';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->accessToken = config('services.facebook.user_token');
        $this->client_id = config('services.facebook.client_id');
        $this->client_secret = config('services.facebook.client_secret');
        $url = "https://graph.facebook.com/oauth/access_token?grant_type=fb_exchange_token&client_id=".$this->client_id."&client_secret=".$this->client_secret."&fb_exchange_token=".$this->accessToken;

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $headers = array(
            "Content-Type: application/json",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $resp = curl_exec($curl);
        curl_close($curl);
        $jsonResponse = json_decode($resp);
        if(isset($jsonResponse->access_token)){
            \Artisan::call('env:set facebook_user_token '.$jsonResponse->access_token);
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('view:clear');
        }
    }
}
