<?php

namespace supercrafter333\AsyncWorldReset;

use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use supercrafter333\AsyncWorldReset\commands\WorldResetCommand;
use supercrafter333\AsyncWorldReset\tasks\asynchronous\ExtractWorldTask;
use supercrafter333\AsyncWorldReset\tasks\asynchronous\RemoveWorldTask;
use supercrafter333\AsyncWorldReset\tasks\TimeCheckTask;
use supercrafter333\DiscordWebhooksX\Embed;
use supercrafter333\DiscordWebhooksX\Message;
use supercrafter333\DiscordWebhooksX\Webhook;
use ZipArchive;
use function count;
use function file_exists;
use function microtime;
use function mkdir;
use function str_replace;

/**
 *
 */
class AsyncWorldReset extends PluginBase
{
    use SingletonTrait;

    protected static bool $useDiscordWebhooks = false;

    /**
     * @var string[]
     */
    public static array $worlds;

    /**
     * @return void
     */
    protected function onLoad(): void
    {
        self::setInstance($this);
    }

    /**
     * @return void
     */
    protected function onEnable(): void
    {
        $this->saveResource("config.yml");
        if (!file_exists($this->getDataFolder() . "worlds/")) {
            @mkdir($this->getDataFolder() . "worlds");
        }

        if (!class_exists(ZipArchive::class)) {
            $this->getLogger()->error("ZIP extension not found!! Disabling AsyncWorldReset...");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }

        self::$useDiscordWebhooks = (bool)$this->getConfig()->get("use-discord-webhooks", false);

        self::$worlds = $this->getConfig()->get("worlds", []);

        if (count(self::$worlds) > 0) $this->getScheduler()->scheduleRepeatingTask(new TimeCheckTask(), 20);

        $this->getServer()->getCommandMap()->register("AsyncWorldReset", new WorldResetCommand(
            "worldreset",
            "Will reset the chosen worlds to reset.",
            null,
            ["asyncworldreset", "resetworlds"]
        ));
    }

    /**
     * @return bool
     */
    public static function resetWorlds(): bool
    {
        if (count(self::$worlds) <= 0) return false;

        foreach (self::$worlds as $world) {
            self::resetWorld($world);
        }
        return true;
    }

    /**
     * @param string $worldName
     * @return void
     */
    public static function resetWorld(string $worldName): void
    {
        $logger = self::getInstance()->getLogger();
        $server = self::getInstance()->getServer();
        $worldMgr = $server->getWorldManager();
        $worldPath = $server->getDataPath() . "worlds/" . $worldName;
        $mt = microtime(true);

        $logger->warning("Resetting world " . $worldName . " ...");

        if (!$worldMgr->isWorldGenerated($worldName)) {
            $logger->error("Cannot reset world " . $worldName . "! World doesn't exist!");
            return;
        }

        if ($worldMgr->isWorldLoaded($worldName))
            $worldMgr->unloadWorld($worldMgr->getWorldByName($worldName));

        $server->getAsyncPool()->submitTask(new RemoveWorldTask($worldPath));

        $server->getAsyncPool()->submitTask(new ExtractWorldTask($server->getDataPath(), self::getInstance()->getDataFolder() . "worlds/" . $worldName . ".zip"));

        $worldMgr->loadWorld($worldName);
        $finalMt = (string)round(microtime(true) - $mt, 3);
        if (self::$useDiscordWebhooks) self::getInstance()->sendResetWebhook($worldName, $finalMt);
        $logger->warning("Successfully resetted world " . $worldName . " in " . $finalMt . "s!");
    }

    /**
     * @param string $worldName
     * @param string $seconds
     * @return bool
     */
    public function sendResetWebhook(string $worldName, string $seconds): bool
    {
        $cfg = $this->getConfig();

        $webhook = new Webhook($cfg->get("discord-webhook-url"));
        if (!$webhook->isValid()) return false;

        $options = $cfg->get("discord-webhook", []);

        $webhook->send(Message::create([
            Embed::create()
                ->setTitle($options["title"])
                ->setDescription(str_replace(['{world}', '{seconds}'], [$worldName, $seconds], $options["message"]))
                ->setColor($options["color"])
        ]));
        return true;
    }
}