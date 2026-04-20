<?php

return [
    'default_filesystem_disk' => env('FILAMENT_FILESYSTEM_DISK', env('FILESYSTEM_DISK', 'local')),
];
