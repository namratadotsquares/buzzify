<?php



namespace App\Console;



use Illuminate\Console\Scheduling\Schedule;
use App\Models\Blog;
use App\Models\BlogImages;
use App\Models\SiteContent;
use Carbon\Carbon;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;



class Kernel extends ConsoleKernel
{

    /**

     * The Artisan commands provided by your application.

     *

     * @var array

     */

    protected $commands = [

            //



        Commands\ExecuteCron::class,

    ];



    /**

     * Define the application's command schedule.

     *

     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule

     * @return void

     */

    protected function schedule(Schedule $schedule)
    {

        $schedule->call(function () {
            // Retrieve dynamic settings
            $featureSetting = SiteContent::where('key', 'feature_category_auto_remove')->first();
            $personalSetting = SiteContent::where('key', 'personal_category_auto_remove')->first();
            $otherSetting = SiteContent::where('key', 'news_deletion')->first();

            $featureDays = $featureSetting ? (int)$featureSetting->value : 1;
            $personalDays = $personalSetting ? (int)$personalSetting->value : 7;
            $otherHours = $otherSetting ? (int)$otherSetting->value : 48;

            // 1. Featured News: Un-feature older than $featureDays days
            if ($featureDays > 0) {
                $featureThreshold = Carbon::now()->subDays($featureDays)->toDateTimeString();
                Blog::where('schedule_date', '<', $featureThreshold)
                    ->where('status', 1)
                    ->where(function ($q) {
                        $q->where('is_featured', '1')->orWhere('is_slider', '1');
                    })
                    ->update(['is_featured' => '0', 'is_slider' => '0']);
            }

            // 2. YourBuzz (Personalization Category ID 12) News: Delete older than $personalDays days
            if ($personalDays > 0) {
                $personalThreshold = Carbon::now()->subDays($personalDays)->toDateTimeString();
                $personalBlogs = Blog::where('schedule_date', '<', $personalThreshold)
                    ->where('category_id', 12) // Personalization ID
                    ->get();

                foreach ($personalBlogs as $blog) {
                    $images = BlogImages::where('blog_id', $blog->id)->get();
                    foreach ($images as $img) {
                        $fullImagePath = public_path('upload/blog/banner/360/' . $img->image);
                        if (file_exists($fullImagePath)) {
                            unlink($fullImagePath);
                        }
                        $img->delete();
                    }
                    $blog->delete();
                }
            }

            // 3. Other News (All categories except 12): Delete older than $otherHours hours
            if ($otherHours > 0) {
                $otherThreshold = Carbon::now()->subHours($otherHours)->toDateTimeString();
                $otherBlogs = Blog::where('schedule_date', '<', $otherThreshold)
                    ->where('category_id', '!=', 12)
                    ->get();

                foreach ($otherBlogs as $blog) {
                    $images = BlogImages::where('blog_id', $blog->id)->get();
                    foreach ($images as $img) {
                        $fullImagePath = public_path('upload/blog/banner/360/' . $img->image);
                        if (file_exists($fullImagePath)) {
                            unlink($fullImagePath);
                        }
                        $img->delete();
                    }
                    $blog->delete();
                }
            }
        })->everyMinute();

       
    }



    /**

     * Register the commands for the application.

     *

     * @return void

     */

    protected function commands()
    {

        $this->load(__DIR__ . '/Commands');



        require base_path('routes/console.php');

    }

}

