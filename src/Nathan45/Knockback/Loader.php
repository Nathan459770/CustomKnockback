<?php

namespace Nathan45\Knockback;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Loader extends PluginBase implements Listener
{
    private static self $instance;

    protected function onEnable(): void
    {
        self::$instance = $this;
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCreation(PlayerCreationEvent $event): void{
        $event->setPlayerClass(KnockBackPlayer::class);
    }

    public static function getInstance(): self
    {
        return self::$instance;
    }
}

class KnockBackPlayer extends Player
{
    public function knockBack(float $x, float $z, float $force = 0.4, ?float $verticalLimit = 0.4): void
    {
        $f = sqrt($x * $x + $z * $z);
        if($f <= 0) return;

        if(mt_rand() / mt_getrandmax() > $this->knockbackResistanceAttr->getValue()){

            // config
            $config = (new Config(Loader::getInstance()->getDataFolder() . "config.yml", Config::YAML))->getAll();
            $worlds = [];
            foreach ($config as $key => $value)if(is_array($value)) $worlds[$key] = $value;

            $kbs = [$config["default.x"], $config["default.y"], $config["default.z"]];
            foreach ($worlds as $world => $array) if($world === $this->getWorld()->getFolderName()) $kbs = [$array["x"], $array["y"], $array["z"]];

            //kb
            $f = 1 / $f;

            $motionX = $this->motion->x / 2;
            $motionY = $this->motion->y / 2;
            $motionZ = $this->motion->z / 2;
            $motionX += $x * $f * $kbs[0];
            $motionY += $kbs[1];
            $motionZ += $z * $f * $kbs[2];

            if($motionY > $kbs[1]) $motionY = $kbs[1];

            $this->setMotion(new Vector3($motionX, $motionY, $motionZ));
        }
    }
}