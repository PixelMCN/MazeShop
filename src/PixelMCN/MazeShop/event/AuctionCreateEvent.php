<?php

declare(strict_types=1);

namespace PixelMCN\MazeShop\event;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\plugin\PluginEvent;
use pocketmine\player\Player;
use PixelMCN\MazeShop\Main;
use PixelMCN\MazeShop\auction\Auction;

class AuctionCreateEvent extends PluginEvent implements Cancellable {
    use CancellableTrait;

    private Auction $auction;
    private Player $seller;

    public function __construct(Auction $auction, Player $seller) {
        parent::__construct(Main::getInstance());
        $this->auction = $auction;
        $this->seller = $seller;
    }

    public function getAuction(): Auction {
        return $this->auction;
    }

    public function getSeller(): Player {
        return $this->seller;
    }
}
