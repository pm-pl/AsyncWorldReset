<?php

namespace supercrafter333\AsyncWorldReset\tasks\asynchronous;

use pocketmine\scheduler\AsyncTask;
use function closedir;
use function is_dir;
use function opendir;
use function readdir;
use function rmdir;
use function unlink;

class RemoveWorldTask extends AsyncTask
{

    /**
     * @param string $worldPath
     */
    public function __construct(private string $worldPath) {}

    /**
     * @return void
     */
    public function onRun(): void
    {
        $this->rmDir($this->worldPath);
    }

    /**
     * @param string $file
     * @return void
     */
    private function rmDir(string $file): void
    {
        if (is_dir($file)) {
            $resource = opendir($file);
            while ($filename = readdir($resource)) {
                if ($filename != "." && $filename != "..") {
                    $this->rmDir($file . "/" . $filename);
                }
            }
            closedir($resource);
            rmdir($file);
        } else {
            unlink($file);
        }
    }
}