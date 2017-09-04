<?php

namespace Arc\Console;

use Arc\Application;
use Arc\Filesystem\FileManager;
use Illuminate\Console\Command;

class LinkPluginCommand extends Command
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->fileManager = $this->app->make(FileManager::class);

        parent::__construct();
    }

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'link {wordpressPath} {--f}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the plugin into local wordpress instance by symlink';

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $pluginsDirectory = $this->argument('wordpressPath').'/wp-content/plugins';
        $pluginDirectory = $pluginsDirectory.'/'.basename($this->app->filename, '.php');

        if (!is_dir($pluginsDirectory)) {
            return $this->error('Unable to locate plugin directory for wordpress install at '.$this->argument('wordpressPath'));
        }

        if (file_exists($pluginDirectory)) {
            if (!$this->option('f')) {
                return $this->error('File or folder already exists at '.$pluginDirectory.'. Use -f option to overwrite it.');
            }
dd($pluginDirectory);
            // $this->fileManager->deleteDirectory($pluginDirectory);
        }

        symlink($this->app->basePath(), $pluginDirectory);

        $this->info('Plugin installed via symlink at '.$pluginDirectory);
    }
}

