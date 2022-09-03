<?php

namespace supercrafter333\AsyncWorldReset\tasks\asynchronous;

use Closure;
use pocketmine\scheduler\AsyncTask;
use ZipArchive;

class ExtractWorldTask extends AsyncTask
{

    /**
     * @param string $serverPath
     * @param string $worldArchPath
     * @param Closure $onSubmit
     */
    public function __construct(private string $serverPath, private string $worldArchPath, private Closure $onSubmit) {}


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

    public function onCompletion(): void
    {
        $this->onSubmit->call($this);
    }
}