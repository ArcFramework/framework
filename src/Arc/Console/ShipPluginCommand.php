<?php

namespace Arc\Console;

use Symfony\Component\Console\Input\InputArgument;

class ShipPluginCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'ship';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build the plugin and zip it up ready for deployment';

    /**
     * The location of the Arc config file on the local file system
     * @var string
     **/
    protected $configFilePath;

    /**
     * The path to the directory where all shipped plugins are stored
     * @var string
     **/
    protected $shippedPluginDirectory;

    /**
     * The path to the directory where the final zip will be save
     * @var string
     **/
    protected $releaseDirectory;

    /**
     * The path to the directory where shipped plugins for this plugin are stored
     * @var string
     **/
    protected $finalDestination;

    public function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->fire();
    }

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function fire()
    {
        // Get the arc config file path
        $this->configFilePath = $this->getHomeDirectory() . '/.arc/config.php';
        $this->shippedPluginDirectory = $this->getConfig()['shippedPluginDirectory'];
        $this->releaseDirectory = $this->shippedPluginDirectory . '/' . basename($this->app->slug);
        $this->finalDestination = $this->releaseDirectory . '/' . basename($this->app->slug);

        // Create the shipped plugin directory if it does not yet exist
        if (!file_exists($this->shippedPluginDirectory)) {
            $this->line('Creating shipped plugin directory at ' . $this->shippedPluginDirectory);
            mkdir($this->shippedPluginDirectory);
            $this->done();
        }

        // Create the plugin release directory if it does not yet exist
        if (!file_exists($this->releaseDirectory)) {
            $this->line('Creating directory for releases of this plugin at ' . $this->releaseDirectory);
            mkdir($this->releaseDirectory);
            $this->done();
        }

        // Copy the files to their shipped location
        $this->line('Copying files to the final destination');
        $this->xcopy($this->app->path, $this->finalDestination);
        $this->done();

        // Remove studio.json file if it exists
        if (file_exists($this->finalDestination . '/studio.json')) {
            $this->line("Removing studio.json so we don't include any symlinks");
            unlink($this->finalDestination . '/studio.json');
            $this->done();
        }

        // Run composer install
        $this->line("Removing vendor directory so we have a fresh install of all dependencies without symlinks");
        echo shell_exec("rm -R $this->finalDestination/vendor");
        $this->done();
        $this->line('Running composer install');
        echo shell_exec("composer update --prefer-dist --no-plugins --no-dev -d $this->finalDestination --ansi");
        $this->done();

        // Delete skipped files
        $this->line('Deleting files named in .shipignore');
        foreach($this->getSkippedFiles() as $filename) {
            if (empty($filename)) {
                continue;
            }
            $path = $this->finalDestination . '/' . $filename;
            echo shell_exec("rm -Rf $path");
        }
        $this->done();

        $this->line('Zip up resulting folder');
        $this->zipDir($this->finalDestination, $this->finalDestination . '-' . $this->app->version . '.zip');
        $this->done();

        // Delete the unzipped directory
        $this->line('Deleting the unzipped directory');
        shell_exec("rm -Rf $this->finalDestination");
        $this->done();

        $this->info('Plugin shipped!');
    }

    /**
     * Copy a file, or recursively copy a folder and its contents
     * @author      Aidan Lister <aidan@php.net>
     * @version     1.0.1
     * @link        http://aidanlister.com/2004/04/recursively-copying-directories-in-php/
     * @param       string   $source    Source path
     * @param       string   $dest      Destination path
     * @param       int      $permissions New folder creation permissions
     * @return      bool     Returns true on success, false on failure
     */
    protected function xcopy($source, $dest, $permissions = 0755)
    {
        // Check for symlinks
        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }

        // Simple copy for a file
        if (is_file($source)) {
            return copy($source, $dest);
        }

        // Make destination directory
        if (!is_dir($dest)) {
            mkdir($dest, $permissions);
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Deep copy directories
            $this->xcopy("$source/$entry", "$dest/$entry", $permissions);
        }

        // Clean up
        $dir->close();
        return true;
    }

    protected function getConfig()
    {
        if (!file_exists($this->configFilePath)) {
            return [
                'shippedPluginDirectory' => $this->getHomeDirectory()
            ];
        }

        return include($this->configFilePath);
    }

    protected function getSkippedFiles()
    {
        $skippedFiles = [];
        if ($file = fopen(".shipignore", "r")) {
            while (!feof($file)) {
                $skippedFiles[] = trim(fgets($file));
            }
            fclose($file);
        }
        return $skippedFiles;
    }

    protected function folderToZip($folder, &$zipFile, $exclusiveLength)
    {
        $handle = opendir($folder);
        while (false !== $f = readdir($handle)) {
            if ($f != '.' && $f != '..') {
                $filePath = "$folder/$f";

                // Remove prefix from file path before adding to zip
                $localPath = substr($filePath, $exclusiveLength);

                if (is_file($filePath)) {
                    $zipFile->addFile($filePath, $localPath);
                } elseif (is_dir($filePath)) {
                    // Add sub-directory.
                    $zipFile->addEmptyDir($localPath);
                    self::folderToZip($filePath, $zipFile, $exclusiveLength);
                }
            }
        }
        closedir($handle);
    }


    /**
     * Zip a folder (include itself).
     *
     * @param string $sourcePath Path of directory to be zip.
     * @param string $outZipPath Path of output zip file.
     */
    protected function zipDir($sourcePath, $outZipPath)
    {
        $pathInfo = pathInfo($sourcePath);
        $parentPath = $pathInfo['dirname'];
        $dirName = $pathInfo['basename'];

        $z = new \ZipArchive();
        $z->open($outZipPath, \ZipArchive::CREATE);
        $z->addEmptyDir($dirName);
        self::folderToZip($sourcePath, $z, strlen("$parentPath/"));
        $z->close();
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['command', InputArgument::REQUIRED, 'The name of the command'],
        ];
    }

    /**
     * Get the current user's home directory
     * @return string
     **/
    protected function getHomeDirectory()
    {
        if (isset($_SERVER['HOME'])) {
            return $_SERVER['HOME'];
        }

        return posix_getpwuid(posix_getuid())['dir'];
    }

    protected function done()
    {
        $this->info('-- Done --');
    }
}
