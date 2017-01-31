<?php

namespace Arc\Facades;

use Arc\Media\Media as MediaManager;

class Media
{
    public static function attachFile($filePath)
    {
        return app(MediaManager::class)->attachFile($filePath);
    }
}
