<?php

declare(strict_types=1);

namespace PixelMCN\MazeShop\database;

use pocketmine\scheduler\Task;
use PixelMCN\MazeShop\Main;

class DatabaseSyncTask extends Task {

    private Main $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function onRun(): void {
        if ($this->plugin->getDatabaseManager()->isEnabled()) {
            $this->plugin->getDatabaseManager()->syncShopData();
            $this->plugin->getLogger()->debug("Shop data synced with database.");
        }
    }
}
