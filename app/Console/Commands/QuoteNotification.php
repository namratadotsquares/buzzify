<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Quote;
use App\Models\DeviceToken;

class QuoteNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quote:cron';

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

        if (setting('enable_notifications')) {
            $Quote = Quote::where('schedule_date','>=',date("Y-m-d H:i"))->where('schedule_date','<',date('Y-m-d H:i',strtotime("+ 15 minutes")))->get();
            if(file_exists(public_path()."/upload/logo/".setting('site_logo'))) { 
                $image = url('upload/logo').'/'.setting('site_logo');
            }else{
                $image = url('upload/no-image.png');
            }
            foreach ($Quote as $row) {
                $user = User::where('active',1)->get();
                foreach($user as $detail){
                    if($detail->device_token!=null){
                        \Helpers::sendNotification($detail->device_token,$row->quote,'',setting('firebase_msg_key'),$image);
                    }
                }
                $non_logged_in = DeviceToken::get();
                if(count($non_logged_in)){
                    foreach($non_logged_in as $non_logged_in_data){
                        if($non_logged_in_data->device_token!=null){
                            \Helpers::sendNotification($non_logged_in_data->device_token,$row->quote,'',setting('firebase_msg_key'),$image);
                        }
                    }
                }
            }
        }
        
        return 1;
    }
}
