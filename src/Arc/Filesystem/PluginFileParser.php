<?php

namespace Arc\Filesystem;

use Illuminate\Support\Str;

class PluginFileParser
{
    public function getPluginVersion($filename)
    {
        $versionLine = collect(explode("\n", file_get_contents($filename)))->first(function ($line) {
            return Str::contains($line, 'Version:');
        });

        return trim(str_replace('Version:', '', $versionLine));
    }
}
