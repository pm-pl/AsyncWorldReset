<?php

namespace supercrafter333\AsyncWorldReset\tasks;

use DateTime;
use DateTimeZone;
use Exception;
use pocketmine\scheduler\Task;
use supercrafter333\AsyncWorldReset\AsyncWorldReset;
use function in_array;

class TimeCheckTask extends Task
{

    /**
     * @throws Exception
     */
    public function onRun(): void
    {
        $awr = AsyncWorldReset::getInstance();
        $cfg = $awr->getConfig();
        $time = new DateTime('now', new DateTimeZone($cfg->get("timezone")));
        $resetDates = $cfg->get("reset-dates", []);
        $timeString = $time->format("H:i:s");

        if (in_array($timeString, $resetDates))
            AsyncWorldReset::resetWorlds();
    }
}