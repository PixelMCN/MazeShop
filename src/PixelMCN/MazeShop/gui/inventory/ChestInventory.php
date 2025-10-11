<?php

declare(strict_types=1);

namespace PixelMCN\MazeShop\gui\inventory;

use pocketmine\inventory\SimpleInventory;
use pocketmine\player\Player;

class ChestInventory extends SimpleInventory {

    private string $title;
    /** @var callable|null */
    private $clickHandler = null;
    /** @var callable|null */
    private $closeHandler = null;
    
    public function __construct(string $title = "Chest", int $size = 27) {
        parent::__construct($size);
        $this->title = $title;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function setTitle(string $title): void {
        $this->title = $title;
    }

    public function setClickHandler(?callable $handler): void {
        $this->clickHandler = $handler;
    }

    public function setCloseHandler(?callable $handler): void {
        $this->closeHandler = $handler;
    }

    public function getClickHandler(): ?callable {
        return $this->clickHandler;
    }

    public function onClose(Player $who): void {
        parent::onClose($who);
        if ($this->closeHandler !== null) {
            ($this->closeHandler)($who);
        }
    }
}
