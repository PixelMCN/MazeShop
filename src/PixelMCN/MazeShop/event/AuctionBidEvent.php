<?php

declare(strict_types=1);

namespace PixelMCN\MazeShop\event;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\plugin\PluginEvent;
use pocketmine\player\Player;
use PixelMCN\MazeShop\Main;
use PixelMCN\MazeShop\auction\Auction;

class AuctionBidEvent extends PluginEvent implements Cancellable {
    use CancellableTrait;

    private Auction $auction;
    private Player $bidder;
    private float $bidAmount;

    public function __construct(Auction $auction, Player $bidder, float $bidAmount) {
        parent::__construct(Main::getInstance());
        $this->auction = $auction;
        $this->bidder = $bidder;
        $this->bidAmount = $bidAmount;
    }

    public function getAuction(): Auction {
        return $this->auction;
    }

    public function getBidder(): Player {
        return $this->bidder;
    }

    public function getBidAmount(): float {
        return $this->bidAmount;
    }

    public function setBidAmount(float $amount): void {
        $this->bidAmount = $amount;
    }
}
