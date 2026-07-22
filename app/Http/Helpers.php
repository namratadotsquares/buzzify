<?php



use App\Models\User;
use App\Models\Blog;
use App\Models\Author;
use App\Models\Category;
use App\Models\SiteContent;
use Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use UploadImage as Image;
use Illuminate\Support\Facades\Http;
use anlutro\LaravelSettings\Facade as Setting;



class Helpers
{

    public static function isOpenAiQuotaExceededError($payloadOrText): bool
    {
        
        if (is_array($payloadOrText)) {
            $type = $payloadOrText['error']['type'] ?? null;
            $code = $payloadOrText['error']['code'] ?? null;
            $message = $payloadOrText['error']['message'] ?? null;

            if ($type === 'insufficient_quota' || $code === 'insufficient_quota') {
                return true;
            }

            if ($code === 'billing_hard_limit_reached' || $type === 'billing_hard_limit_reached') {
                return true;
            }

            if (is_string($message) && stripos($message, 'exceeded your current quota') !== false) {
                return true;
            }

            if (is_string($message) && stripos($message, 'billing hard limit') !== false) {
                return true;
            }

            return false;
        }

        if (!is_string($payloadOrText) || trim($payloadOrText) === '') {
            return false;
        }

        $decoded = json_decode($payloadOrText, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return self::isOpenAiQuotaExceededError($decoded);
        }

        $text = strtolower($payloadOrText);
        return (strpos($text, 'insufficient_quota') !== false)
            || (strpos($text, 'exceeded your current quota') !== false)
            || (strpos($text, 'billing_hard_limit_reached') !== false)
            || (strpos($text, 'billing hard limit') !== false)
            || (strpos($text, 'hard limit has been reached') !== false);
    }

    public static function setOpenAiQuotaExpired(bool $expired, ?string $message = null): void
    {
        try {
            Setting::set('openai_quota_expired', $expired ? 1 : 0);
            if ($message !== null) {
                Setting::set('openai_quota_expired_message', $message);
            }
            if ($expired) {
                Setting::set('openai_quota_expired_at', Carbon::now()->toDateTimeString());
            }
            Setting::save();
        } catch (\Throwable $e) {
            Log::warning('Failed to persist OpenAI quota status: ' . $e->getMessage());
        }
    }

    public static function markOpenAiQuotaExpiredIfNeeded($payloadOrText): void
    {
        if (!self::isOpenAiQuotaExceededError($payloadOrText)) {
            return;
        }

        $message = null;
        if (is_array($payloadOrText)) {
            $message = $payloadOrText['error']['message'] ?? null;
        } elseif (is_string($payloadOrText)) {
            $decoded = json_decode($payloadOrText, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $message = $decoded['error']['message'] ?? null;
            }
        }

        self::setOpenAiQuotaExpired(true, $message);
    }

    public static function clearOpenAiQuotaExpired(): void
    {
        self::setOpenAiQuotaExpired(false);
    }

    public static function getFirebaseAccessToken()
    {
        $keyFilePath = storage_path('app/firebase/firebase_credentials.json');

        $jsonKey = json_decode(file_get_contents($keyFilePath), true);
      
        $now = time();
        $expiration = $now + 3600;

        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $claimSet = [
            'iss' => $jsonKey['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $expiration,
        ];

        // Encode and sign JWT manually
        $jwtHeader = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $jwtClaim = rtrim(strtr(base64_encode(json_encode($claimSet)), '+/', '-_'), '=');
        $jwtToSign = "$jwtHeader.$jwtClaim";

        openssl_sign($jwtToSign, $signature, $jsonKey['private_key'], 'sha256');
        $jwtSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
        $jwt = "$jwtToSign.$jwtSignature";

        // Get access token
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);
        return $response['access_token'] ?? null;
    }

    public static function returnUserLangCode()
    {

        $language = setting('preferred_site_language');

        if (auth()->user() && auth()->user()->type == 'user') {

            if (auth()->user()->lang_code != '') {

                $language = auth()->user()->lang_code;

            }

        } else {

            if (isset($_COOKIE['lang_code']) && $_COOKIE['lang_code'] != '') {

                $language = $_COOKIE['lang_code'];

            }

        }

        return $language;

    }



    public static function get_file_extension($file_name)
    {

        return substr(strrchr($file_name, '.'), 1);

    }



    public static function generateWatermarkImage($oldimg, $image_name, $type)
    {

        $siteLogo = SiteContent::where('key', 'site_logo')->first();

        $watermark = public_path('upload/watermark.png');

        if ($siteLogo && $siteLogo->value != '') {

            $file = public_path('upload/logo/') . $siteLogo->value;

            if (is_file($file)) {

                $watermark = $file;

            }

        }

        $c = pathinfo($watermark, PATHINFO_EXTENSION);

        if (strtolower($c) == 'jpg') {

            $stamp = imagecreatefromjpeg($watermark);

        } else {

            $stamp = imagecreatefrompng($watermark);

        }



        $im = imagecreatefromjpeg($oldimg);

        $sx = imagesx($stamp);

        $sy = imagesy($stamp);



        header('Content-type: image/png');



        // for instagaram

        // ********************* use ths code for top right ******************/

        // imagecopy($im, $stamp, imagesx($im) - $sx, 0, 0, 0, imagesx($stamp), imagesy($stamp));

        // ********************* use ths code for top righ ******************/

        // $insta = public_path('upload/social-media-post/instagram/').$image_name;

        // imagepng($im,$insta);

        // imagedestroy($im);



        // for fb and twitter

        // ********************* use ths code for left bottom ******************/

        imagecopy($im, $stamp, 20, imagesy($im) - $sy - 20, 0, 0, imagesx($stamp), imagesy($stamp));

        // ********************* use ths code for top righ ******************/



        if ($type == 'facebook') {

            $fb = public_path('upload/social-media-post/facebook/') . $image_name;

            imagepng($im, $fb);

        } else if ($type == 'twitter') {

            $twitter = public_path('upload/social-media-post/twitter/') . $image_name;

            imagepng($im, $twitter);

        } else if ($type == 'instagram') {

            $instagram = public_path('upload/social-media-post/instagram/') . $image_name;

            imagepng($im, $instagram);

        } else {

            return false;

        }



        imagedestroy($im);

        return true;

    }





    public static function compress_image($source_url, $destination_url, $quality = 30, $name = '', $basePath = '', $type = false)
    {

        $info = getimagesize($source_url);

        if ($info['mime'] == 'image/jpeg')
            $image = imagecreatefromjpeg($source_url);
        elseif ($info['mime'] == 'image/gif')
            $image = imagecreatefromgif($source_url);
        elseif ($info['mime'] == 'image/png')
            $image = imagecreatefrompng($source_url);
        elseif ($info['mime'] == 'image/webp')
            $image = imagecreatefromwebp($source_url);

        $c = imagejpeg($image, $destination_url, $quality);

        if ($type) {

            $img = Image::make($destination_url);



            $img->resize(800, null, function ($constraint) {

                $constraint->aspectRatio();

            })->save($basePath . '800/' . $name);



            $img->resize(360, null, function ($constraint) {

                $constraint->aspectRatio();

            })->save($basePath . '360/' . $name);



        }

        return $c;

    }



    /*

check an create slug

*/

    public static function createSlug($title, $in = 'blog', $whr = 0, $alphaNum = false)
    {

        if ($alphaNum) {

            $slug = Str::slug($title, '-');

        } else {

            $slug = Str::slug($title, '-');

        }

        if ($in == 'blog') {

            $slugExist = Blog::where(DB::raw('LOWER(slug)'), strtolower($slug))->where('id', '!=', $whr)->get();

        } else if ($in == 'category') {

            $slugExist = Category::where(DB::raw('LOWER(slug)'), strtolower($slug))->where('id', '!=', $whr)->get();

        }

        if (count($slugExist)) {

            $slug = Str::slug($title . '-' . Str::random(5) . '-' . Str::random(5), '-');

            return $slug;

        } else {

            return $slug;

        }

    }



    /**

   * function for check empty value

   * @param $value

   */

    public static function checkEmpty($value = NULL)
    {

        if (isset($value) && !empty($value)) {

            $data = trim(strip_tags($value));

            return iconv('ISO-8859-1', 'ASCII//IGNORE', $data);

        } else {

            return NULL;

        }

    }





    /**

     * Send success ajax response

     *

     * @param string $message

     * @param array $result

     * @return array

     */

    public static function sendSuccessAjaxResponse($message = '', $result = [])
    {

        $response = [

            'status' => true,

            'message' => $message,

            'data' => $result

        ];



        return $response;

    }



    /**

     * Send failure ajax response

     *

     * @param string $message

     * @return array

     */

    public static function sendFailureAjaxResponse($message = '', $data = [])
    {

        $message = $message == '' ? config('app.message.default_error') : $message;



        $response = [

            'status' => false,

            'message' => $message,

            'data' => $data

        ];



        return $response;

    }



    /**

     * function for send email

     */

    public static function sendEmail($template, $data, $toEmail, $toName, $subject, $fromName = '', $fromEmail = '', $attachment = '')
    {
        if ($fromEmail == '') {
            $fromEmail = setting('from_email');
        }
        try {
          
            $fromName = setting('site_name');
            $data = \Mail::send($template, $data, function ($message) use ($toEmail, $toName, $subject, $data, $fromName, $fromEmail, $attachment) {
               
                $message->to($toEmail, $toName);
                $message->subject($subject);
                if ($fromEmail != '' && $fromName != '') {
                    $message->from($fromEmail, $fromName);
                }
                if ($attachment != '') {
                    $message->attach($attachment);
                }
            });
             
            return 1;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    /**

     * Format Date

     * @param $date

     * @return formatted date

     */

    public static function formatDate($date, $not_available = true)
    {

        if ($date) {

            return date(config('app.date_format_php'), strtotime($date));

        } else {

            if ($not_available == false) {

                return '';

            }

            return '';

        }

    }



    /**

     * Format Date

     * @param $date

     * @return formatted date

     */

    public static function formatDateTime($date, $not_available = true)
    {

        if ($date) {

            return date(config('app.date_time_format_php'), strtotime($date));

        } else {

            if ($not_available == false) {

                return NULL;

            }

            return NULL;

        }

    }



    /**

     * Show error page

     * @return \Illuminate\Http\Response

     */

    public static function showErrorPage()
    {

        return response()->view('errors.error', [], 500);

    }



    /**

     * function for send push notification

     * @param $id device token

     * @param $msg message tobe sent for push notification 

     * @param $title title 

     * @param $key key for API

     * @return Generated new file name

     */



     public static function sendNotification($ids, $msg, $title, $key, $image = false, $notificationId = null)
    {
        $accessToken = self::getFirebaseAccessToken();
        $projectId = 'buzzify-c3a6e'; // from Firebase Console
        // dd($ids);

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";
            // dd($url);
        $results = [];
        foreach (array_chunk($ids, 500) as $chunk) {
            foreach ($chunk as $token) {
                $payload = [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => $title,
                            'body' => $msg,
                            'image' => $image,
                        ],
                        'data' => [
                            'screen' => 'NotificationScreen', // your React Native screen name
                            'id' => (string) ($notificationId ?? ''),
                        ],
                        'android' => [
                            'notification' => [
                                'click_action' => 'NOTIFICATION_SCREEN',
                            ],
                        ],
                    ],
                ];

                 
                    
                    $response = Http::withToken($accessToken)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($url, $payload);
                    // dd($response->json());
                $results[] = [
                    'token' => $token,
                    'response' => $response->json(),
                    'status' => $response->successful(),
                ];
            }
        }
        
        return $results;
    }
    // public static function sendNotification($ids, $msg, $title, $key, $image = false, $notificationId = null)
    // {
    //      $url = "https://fcm.googleapis.com/fcm/send";
    //     $notification = array('title' => $title, 'body' => $msg, 'sound' => 'default', 'badge' => '1', 'image' => $image, 'notificationId' => $notificationId);
    //     // Split IDs into chunks of 1000
    //     $batchSize = 1000;
    //     $chunks = array_chunk($ids, $batchSize);
    //     foreach ($chunks as $chunk) {
    //         $payload = [
    //             "notification" => ["title" => $title, "body" => $msg, "image" => $image],
    //             "data" => [
    //                 // "click_action" => "FLUTTER_NOTIFICATION_CLICK",
    //                 "body" => json_encode($notification),
    //             ],
    //             "registration_ids" => $chunk
    //         ];
    
    //         $json = json_encode($payload);
    
    //         $headers = [
    //             'Content-Type: application/json',
    //             'Authorization: key=' . $key,
    //         ];
    
    //         $ch = curl_init();
    //         curl_setopt($ch, CURLOPT_URL, $url);
    //         curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    //         curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    //         curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //         $response = curl_exec($ch);
    //         curl_close($ch);
    //         // return dd($ids);
    //     }
    // }
   
    

//   public static function sendNotification($ids, $msg, $title, $key, $image = false, $notificationId = null)
//     {
//         $ONESIGNAL_APP_ID = '594e3b1c-8fde-4c45-9679-db27b75c25a6';
//         $ONESIGNAL_REST_API_KEY = 'YWI5MDA2NTktNmJkMS00MWRjLWJkMzctMzZmYjA5Y2NkMzFm';
//         $batchSize = 1000;
//         $chunks = array_chunk($ids, $batchSize);
    
//         foreach ($chunks as $chunk) {
//             $data = [
//                 'app_id' => $ONESIGNAL_APP_ID, // Your OneSignal App ID
//                 'contents' => [
//                     'en' => $msg,
//                 ],
//                 'headings' => [
//                     'en' => $title,
//                 ],
//                 'sound' => 'default', // Default notification sound
//                 'badge' => '1', // Badge count for iOS
//                 'data' => [
//                     'notificationId' => $notificationId, // Custom parameter
//                      'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
//                     // 'extra_data_key' => 'extra_data_value' // Any additional custom data
//                 ],
//                 'big_picture' => $image, 
//                 // 'include_android_reg_ids' => $chunk, // Use the FCM device token here
//                 'include_player_ids' => $chunk, 
//             ];
            
//             $response = Http::withHeaders([
//                 'Authorization' => 'Basic ' . $ONESIGNAL_REST_API_KEY, 
//                 'Content-Type' => 'application/json',
//             ])->post('https://onesignal.com/api/v1/notifications', $data);
//         }
        
//         return;
//     }
// public static function sendNotification($ids, $msg, $title, $key, $image = false, $notificationId = null)
// {
//     $ONESIGNAL_APP_ID = '594e3b1c-8fde-4c45-9679-db27b75c25a6';
//     $ONESIGNAL_REST_API_KEY = 'YWI5MDA2NTktNmJkMS00MWRjLWJkMzctMzZmYjA5Y2NkMzFm';
//     $batchSize = 1000;
//     $chunks = array_chunk($ids, $batchSize);

//     foreach ($chunks as $chunk) {
//         // Filter out invalid player IDs
//         $validIds = array_filter($chunk, function($id) {
//             // Validate UUID format (simple version, you might need a more robust check)
//             return preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $id);
//         });

//         if (empty($validIds)) {
//             continue; // Skip empty valid IDs
//         }

//         $data = [
//             'app_id' => $ONESIGNAL_APP_ID, // Your OneSignal App ID
//             'contents' => [
//                 'en' => $msg,
//             ],
//             'headings' => [
//                 'en' => $title,
//             ],
//             'sound' => 'default', // Default notification sound
//             'badge' => '1', // Badge count for iOS
//             'data' => [
//                 'notificationId' => $notificationId, // Custom parameter
//                 'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
//                 // 'extra_data_key' => 'extra_data_value' // Any additional custom data
//             ],
//             'big_picture' => $image,
//             'include_player_ids' => $validIds, // Use only valid player IDs
//         ];

//         $response = Http::withHeaders([
//             'Authorization' => 'Basic ' . $ONESIGNAL_REST_API_KEY,
//             'Content-Type' => 'application/json',
//         ])->post('https://onesignal.com/api/v1/notifications', $data);

//         // Optionally handle the response here
//     }

//     return;
// }


// public static function sendNotification($ids, $msg, $title, $key, $image = false, $notificationId = null)
// {
//     $ONESIGNAL_APP_ID = '594e3b1c-8fde-4c45-9679-db27b75c25a6';
//     $ONESIGNAL_REST_API_KEY = 'YWI5MDA2NTktNmJkMS00MWRjLWJkMzctMzZmYjA5Y2NkMzFm';
//     $batchSize = 1000;
//     $chunks = array_chunk($ids, $batchSize);
//     //  $serviceAccountPath = storage_path('app/service_file.json');
//     //     $accessToken = self::getAccessToken($serviceAccountPath);
        
//     //     return dd($accessToken);

//     foreach ($chunks as $chunk) {
//         $validIds = array_filter($chunk, function($id) {
//             return preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $id);
//         });

//         if (empty($validIds)) {
//             continue;
//         }

//         $data = [
//             'app_id' => $ONESIGNAL_APP_ID,
//             'contents' => [
//                 'en' => $msg,
//             ],
//             'headings' => [
//                 'en' => $title,
//             ],
//             'sound' => 'default',
//             'ios_sound' => 'default', // iOS-specific sound
//             'badge' => '1',
//             'ios_badgeType' => 'Increase', // Badge increment type for iOS
//             'ios_badgeCount' => 1, // Badge count for iOS
//             'data' => [
//                 'notificationId' => $notificationId,
//                 'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
//             ],
//             'large_icon' => $image, // For Android
//             'ios_attachments' => [ // This is for rich media like images, videos, etc.
//                 'id' => $image, // Image attachment
//             ],
//             'ios_badgeType' => 'Increase',  // Optional: controls how badge count updates
//             'ios_badgeCount' => 1,  // Optional: badge number on the app icon
//             'ios_sound' => 'default',
//                     'priority' => 10,
//                     'include_player_ids' => $validIds,
//                 ];

//         $response = Http::withHeaders([
//             'Authorization' => 'Basic ' . $ONESIGNAL_REST_API_KEY,
//             'Content-Type' => 'application/json',
//         ])->post('https://onesignal.com/api/v1/notifications', $data);

//         // Log the response for debugging
//         Log::info($response->json());
//     }

//     return;
// }
    
    public static function getAccessToken($serviceAccountPath)
    {
        
        $serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);
   
        $now = time();
        $expirationTime = $now + 3600; // Token expiration time (1 hour)
        $jwtHeader = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $jwtPayload = base64_encode(json_encode([
            'iss' => $serviceAccount['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $expirationTime,
        ]));
    
        $message = $jwtHeader . '.' . $jwtPayload;
        $signature = '';
        openssl_sign($message, $signature, $serviceAccount['private_key'], 'SHA256');
    
        $jwtAssertion = $message . '.' . base64_encode($signature);
    
        // Step 4: Make the request to get the access token
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwtAssertion
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
    
        $tokenData = json_decode($response, true);
        return $tokenData['access_token'];
          
    }


    public static function sendTestNotification($id, $msg, $title, $key, $image = false, $notificationId = null, $topic = 'global')
    {
       
        $url = "https://fcm.googleapis.com/fcm/send";
        $notification = array('title' => $title, 'body' => $msg, 'sound' => 'default', 'badge' => '1', "image" => $image, "notificationId" => $notificationId);

        $payload = [
            "notification" => ["title" => $title, "body" => $msg],
            "data" => [
                "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                "body" => $notification,
                // "topic" => $topic // Ensure the topic name is set correctly
                "to" => '/topics/'.$topic,
            ]
        ];
       
        $json = json_encode($payload);
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: key=' . $key;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }


    public static function getYoutubeEmbedUrl($url)
    {



        if (strpos($url, 'youtube') > 0) {

            $shortUrlRegex = '/youtu.be\/([a-zA-Z0-9_]+)\??/i';

            $longUrlRegex = '/youtube.com\/((?:embed)|(?:watch))((?:\?v\=)|(?:\/))(\w+)/i';

            if (preg_match($longUrlRegex, $url, $matches)) {

                $youtube_id = $matches[count($matches) - 1];

            }

            if (preg_match($shortUrlRegex, $url, $matches)) {

                $youtube_id = $matches[count($matches) - 1];

            }

            return 'https://www.youtube.com/embed/' . $youtube_id;

        } else {

            return false;

        }

    }

    // --- Blog TTS helpers ---

    public static function getBlogSpeechVoiceByAccent(): array
    {
        $map = config('constant.blog_speech_voice_by_accent', []);
        if (!is_array($map) || empty($map)) {
            $map = config('constant.speech_voice_by_accent', []);
        }

        if (!is_array($map) || empty($map)) {
            $const = @include(config_path('constant.php'));
            if (is_array($const)) {
                $map = $const['blog_speech_voice_by_accent'] ?? ($const['speech_voice_by_accent'] ?? []);
            }
        }

        return is_array($map) ? $map : [];
    }

    public static function resolveBlogSpeechVoiceForAccent(?string $accent): ?string
    {
        $accent = is_string($accent) ? trim($accent) : '';
        if ($accent === '') {
            return null;
        }

        $voiceByAccent = self::getBlogSpeechVoiceByAccent();
        $voices = $voiceByAccent[$accent] ?? null;

        if (is_string($voices) && trim($voices) !== '') {
            return trim($voices);
        }

        if (is_array($voices)) {
            foreach ($voices as $voiceKey) {
                if (is_string($voiceKey) && trim($voiceKey) !== '') {
                    return trim($voiceKey);
                }
            }
        }

        return null;
    }

    public static function resolveBlogSpeechAccentForVoice(?string $voice): ?string
    {
        $voice = is_string($voice) ? trim($voice) : '';
        if ($voice === '') {
            return null;
        }

        $map = config('constant.blog_speech_accent_by_voice', []);
        if (!is_array($map) || empty($map)) {
            $const = @include(config_path('constant.php'));
            if (is_array($const)) {
                $map = $const['blog_speech_accent_by_voice'] ?? [];
            }
        }

        $accent = is_array($map) ? ($map[$voice] ?? null) : null;
        return (is_string($accent) && trim($accent) !== '') ? trim($accent) : null;
    }

    public static function getBlogSpeechAccentVoiceOptions(?string $includeAccent = null): array
    {
        $voiceByAccent = self::getBlogSpeechVoiceByAccent();

        $voiceLabels = config('constant.speech_voice', []);
        if (!is_array($voiceLabels) || empty($voiceLabels)) {
            $const = @include(config_path('constant.php'));
            if (is_array($const)) {
                $voiceLabels = $const['speech_voice'] ?? [];
            }
        }
        if (!is_array($voiceLabels)) {
            $voiceLabels = [];
        }

        $options = [];
        foreach ($voiceByAccent as $accent => $voices) {
            if (!is_string($accent) || trim($accent) === '') {
                continue;
            }

            $voiceKey = null;
            if (is_string($voices) && trim($voices) !== '') {
                $voiceKey = trim($voices);
            } elseif (is_array($voices) && !empty($voices)) {
                foreach ($voices as $candidate) {
                    if (is_string($candidate) && trim($candidate) !== '') {
                        $voiceKey = trim($candidate);
                        break;
                    }
                }
            }

            if ($voiceKey === null) {
                continue;
            }

            $voiceLabel = $voiceLabels[$voiceKey] ?? $voiceKey;
            $options[$accent] = $accent . ' - ' . $voiceLabel;
        }

        $includeAccent = is_string($includeAccent) ? trim($includeAccent) : '';
        if ($includeAccent !== '' && !array_key_exists($includeAccent, $options)) {
            $options[$includeAccent] = $includeAccent;
        }

        ksort($options);
        return $options;
    }

    public static function resolveOpenAiModel(?string $model): string
    {
        $model = trim((string)$model);
        if ($model === '') {
            return 'gpt-5-mini';
        }
        $normalized = strtolower($model);
        $aliases = [
            'gpt4o mini' => 'gpt-4o-mini',
            'gpt4o-mini' => 'gpt-4o-mini',
            'gpt-4o mini' => 'gpt-4o-mini',
            '4o-mini' => 'gpt-4o-mini',
            'chatgpt5 mini' => 'gpt-5-mini',
            'chatgpt-5-mini' => 'gpt-5-mini',
            'chatgpt5-mini' => 'gpt-5-mini',
            'gpt5 mini' => 'gpt-5-mini',
            'gpt5-mini' => 'gpt-5-mini',
            'gpt-5 mini' => 'gpt-5-mini',
            'gpt-5-mini' => 'gpt-5-mini',
            'gpt5' => 'gpt-5-mini',
            'gpt-5' => 'gpt-5-mini',
            'o3-mini' => 'o3-mini',
            'openai 5' => 'gpt-5-mini',
            'openai-5' => 'gpt-5-mini',
            'open-ai-5' => 'gpt-5-mini',
            'openai5' => 'gpt-5-mini',
            'open-ai 5' => 'gpt-5-mini',
            'openai 5 mini' => 'gpt-5-mini',
            'openai-5-mini' => 'gpt-5-mini',
            'open-ai-5-mini' => 'gpt-5-mini',
            'open-ai 5-mini' => 'gpt-5-mini',
            'openai5-mini' => 'gpt-5-mini',
            'openai5mini' => 'gpt-5-mini',
            'gpt mini 5' => 'gpt-5-mini',
            'gpt-mini-5' => 'gpt-5-mini',
        ];
        return $aliases[$normalized] ?? $model;
    }

    public static function isOpenAiReasoningModel(?string $model): bool
    {
        $resolved = self::resolveOpenAiModel($model);
        return (bool) preg_match('/^o\d/i', $resolved);
    }

}

