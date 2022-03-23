<?php

return [
    'driver' => env('FCM_PROTOCOL', 'http'),
    'log_enabled' => false,

    'http' => [
        'server_key' => env('FCM_SERVER_KEY', 'AAAAbg5z4D0:APA91bE5sgjFqOd7f28AYHsF9BaqdEiGDmFoWa2sMIgCMXQurl0J1Dx1SvTyhwG_hbPBh7kMYDj_84ixLT7bMWLcD6cPzPUc35geBhLQsJlVLWd5Sjn7wzwQkHZB-_x482WwWAx9vnTW'),
        'sender_id' => env('FCM_SENDER_ID', '472688877629'),
        'server_send_url' => 'https://fcm.googleapis.com/fcm/send',
        'server_group_url' => 'https://android.googleapis.com/gcm/notification',
        'timeout' => 30.0, // in second
    ],
];
