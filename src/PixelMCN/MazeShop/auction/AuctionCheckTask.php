<?php

declare(strict_types=1);

namespace PixelMCN\MazeShop\auction;

use pocketmine\scheduler\Task;
use PixelMCN\MazeShop\Main;

class AuctionCheckTask extends Task {

    private Main $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function onRun(): void {
        $this->plugin->getAuctionManager()->checkExpiredAuctions();
    }
}
