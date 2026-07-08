<?php

namespace App\Jobs;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogImages;
use App\Models\CustomNotification;
use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBlogNotificationUnpublish implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var int */
    public $blogId;

    /**
     * Create a new job instance.
     *
     * @param  int  $blogId
     * @return void
     */
    public function __construct($blogId)
    {
        $this->blogId = (int) $blogId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $blog = Blog::find($this->blogId);
            if (!$blog) {
                return;
            }

            $blogCategories = BlogCategory::where('blog_id', $blog->id)->get();
            foreach ($blogCategories as $blogCategory) {
                if ((int) $blogCategory->category_id === 12) {
                    return;
                }
            }

            $image = url('upload/blog/banner/default.jpg');
            $blogImageInfo = BlogImages::where('blog_id', $blog->id)->first();
            if ($blogImageInfo) {
                $image = url('upload/blog/banner/original/' . $blogImageInfo->image);
            }

            $users = User::where('active', 1)
                ->where('is_notiifcation', 1)
                ->whereNotNull('fcm_token')
                ->get();

            $tokens = [];
            foreach ($users as $user) {
                if (!empty($user->fcm_token)) {
                    $tokens[] = $user->fcm_token;
                }
            }

            $nonLoggedIn = DeviceToken::whereNotIn('device_token', $tokens)->get();
            foreach ($nonLoggedIn as $nonLoggedInData) {
                if (!empty($nonLoggedInData->device_token)) {
                    $tokens[] = $nonLoggedInData->device_token;
                }
            }

            if (!empty($tokens)) {
                \Helpers::sendNotification($tokens, $blog->title, '', setting('firebase_msg_key'), $image, $blog->id);
                CustomNotification::create([
                    'title' => $blog->title,
                    'desc' => '',
                    'post_id' => $blog->id,
                    'type' => 'All',
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('SendBlogNotificationUnpublish failed: ' . $e->getMessage(), [
                'blog_id' => $this->blogId,
            ]);
        }
    }
}

