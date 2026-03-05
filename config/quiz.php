<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Image Storage Disk
    |--------------------------------------------------------------------------
    |
    | Configure which filesystem disk to use for storing question images.
    | Supported: any disk defined in config/filesystems.php (e.g. "public", "s3")
    |
    */

    'image_disk' => env('QUIZ_IMAGE_DISK', 's3'),

];
