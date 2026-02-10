<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Schedule::command('media-library:delete-old-temporary-uploads')->daily();
