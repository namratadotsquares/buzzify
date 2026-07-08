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
        $newsdelet = SiteContent::find(64);
        $deleted_days_limit = (int)$newsdelet->value;
        $dateBeforeSixDays = Carbon::now()->subDays($deleted_days_limit)->toDateString();

       
       $blog = Blog::where('schedule_date', '<', $dateBeforeSixDays)->where('status',1)->get();
       foreach($blog as $blogs)
       {
           $blogImage = BlogImages::where('blog_id',$blogs->id)->get();
           foreach($blogImage as $blogImages)
           {
                $imageUrl = 'upload/blog/banner/360/' . $blogImages->image;
                $fullImagePath = public_path($imageUrl);
                
                if (file_exists($fullImagePath)) {
                    unlink($fullImagePath);
                }
                $deleteImage = BlogImages::find($blogImages->id);
                $deleteImage->delete();
           }
        $deleteblog = Blog::find($blogs->id);
        $deleteblog->delete();
       }
            // Log::info('cron is deleted');
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

