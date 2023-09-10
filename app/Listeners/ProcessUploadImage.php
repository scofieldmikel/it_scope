<?php

namespace App\Listeners;

use App\Models\Image;
use App\Events\UploadImage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessUploadImage
{

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        
    }

    /**
     * Handle the event.
     */
    public function handle(UploadImage $event): void
    {
        Image::Create([
            'product_id' => $event->id,
            'url' => $event->image_url,
            'extension' => pathinfo($event->image_url, PATHINFO_EXTENSION),
        ]);
    }
}
