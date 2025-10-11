<?php

declare(strict_types=1);

namespace PixelMCN\MazeShop\event;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\plugin\PluginEvent;
use PixelMCN\MazeShop\Main;
use PixelMCN\MazeShop\auction\Auction;

class AuctionEndEvent extends PluginEvent implements Cancellable {
    use CancellableTrait;

    private Auction $auction;
    private ?string $winner;

    public function __construct(Auction $auction, ?string $winner) {
        parent::__construct(Main::getInstance());
        $this->auction = $auction;
        $this->winner = $winner;
    }

    public function getAuction(): Auction {
        return $this->auction;
    }

    public function getWinner(): ?string {
        return $this->winner;
    }
}
