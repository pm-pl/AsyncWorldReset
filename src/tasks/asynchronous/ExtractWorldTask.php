<?php

namespace supercrafter333\AsyncWorldReset\tasks\asynchronous;

use pocketmine\scheduler\AsyncTask;
use ZipArchive;

class ExtractWorldTask extends AsyncTask
{

    /**
     * @param string $serverPath
     * @param string $worldArchPath
     */
    public function __construct(private string $serverPath, private string $worldArchPath) {}


    /**
     * @return void
     */
    public function onRun(): void
    {
        $zip = new ZipArchive;
        $zip->open($this->worldArchPath);
        $zip->extractTo($this->serverPath . "worlds");
        $zip->close();
    }
}