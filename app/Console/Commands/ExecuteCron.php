<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Blog;
use App\Models\BlogImages;
use App\Models\User;
use App\Models\DeviceToken;

class ExecuteCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blog:cron';

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

            $blog = Blog::where('schedule_date', '>=', date("Y-m-d H:i"))->where('schedule_date', '<', date('Y-m-d H:i', strtotime("+ 15 minutes")))->get();

            $image = url('upload/blog/banner/default.jpg');

            foreach ($blog as $row) {

                $blogImageInfo = BlogImages::where('blog_id', $row->id)->first();

                if ($blogImageInfo) {

                    $image = url('upload/blog/banner/original/' . $blogImageInfo->image);

                }

                $user = User::where('active', 1)->get();

                $setting = SiteContent::where('key', 'firebase_msg_key')->first();

                foreach ($user as $detail) {

                    if ($detail->device_token != null) {

                        \Helpers::sendNotification($detail->device_token, $row->title, '', $setting->value, $image, $row->id);

                    }

                }

                $non_logged_in = DeviceToken::get();
                return dd($non_logged_in);

                if (count($non_logged_in)) {

                    foreach ($non_logged_in as $non_logged_in_data) {

                        if ($non_logged_in_data->device_token != null) {

                            \Helpers::sendNotification($non_logged_in_data->device_token, $row->title, '', $setting->value, $image, $row->id);

                        }

                    }

                }

            }

        }
        
        return 1;
    }
}
