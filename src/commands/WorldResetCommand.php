<?php

namespace supercrafter333\AsyncWorldReset\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use supercrafter333\AsyncWorldReset\AsyncWorldReset;

class WorldResetCommand extends Command implements PluginOwned
{

    public function __construct(string $name, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = [])
    {
        $this->setPermission("asnycworldreset.worldreset.cmd");
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    /**
     * @param CommandSender $s
     * @param string $commandLabel
     * @param array $args
     * @return void
     */
    public function execute(CommandSender $s, string $commandLabel, array $args): void
    {
        if (!$this->testPermission($s)) return;

        $cfg = $this->getOwningPlugin()->getConfig();

        if (AsyncWorldReset::resetWorlds())
            $s->sendMessage($cfg->get("msg-worldreset-success"));
        else
            $s->sendMessage($cfg->get("msg-worldreset-failed"));
    }

    /**
     * @return AsyncWorldReset
     */
    public function getOwningPlugin(): Plugin
    {
        return AsyncWorldReset::getInstance();
    }
}