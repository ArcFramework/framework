<?php

namespace Arc\Filesystem;

use Illuminate\Support\Str;

class PluginFileParser
{
    public function getPluginVersion($filename)
    {
        return $this->getPluginAttribute($filename, 'Version');
    }

    public function getPluginName($filename)
    {
        return $this->getPluginAttribute($filename, 'Plugin Name');
    }

    public function getPluginAttribute($filename, $attribute)
    {
        $versionLine = $this->getPluginData($filename)->first(function ($line) use ($attribute) {
            return Str::contains($line, $attribute.':');
        });

        return trim(str_replace($attribute.':', '', $versionLine));
    }

    public function getPluginData($filename)
    {
        return collect(explode("\n", file_get_contents($filename)));
    }
}
