<?php

declare(strict_types=1);

namespace PixelMCN\MazeShop\event;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\plugin\PluginEvent;
use pocketmine\player\Player;
use PixelMCN\MazeShop\Main;
use PixelMCN\MazeShop\shop\ShopItem;

class ItemPurchaseEvent extends PluginEvent implements Cancellable {
    use CancellableTrait;

    private Player $player;
    private ShopItem $item;
    private int $amount;
    private float $totalPrice;

    public function __construct(Player $player, ShopItem $item, int $amount, float $totalPrice) {
        parent::__construct(Main::getInstance());
        $this->player = $player;
        $this->item = $item;
        $this->amount = $amount;
        $this->totalPrice = $totalPrice;
    }

    public function getPlayer(): Player {
        return $this->player;
    }

    public function getItem(): ShopItem {
        return $this->item;
    }

    public function getAmount(): int {
        return $this->amount;
    }

    public function getTotalPrice(): float {
        return $this->totalPrice;
    }

    public function setTotalPrice(float $price): void {
        $this->totalPrice = $price;
    }
}
