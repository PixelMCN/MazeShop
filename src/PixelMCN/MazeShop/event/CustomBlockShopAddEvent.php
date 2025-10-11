<?php

declare(strict_types=1);

namespace PixelMCN\MazeShop\event;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\plugin\PluginEvent;
use PixelMCN\MazeShop\Main;
use PixelMCN\MazeShop\shop\ShopItem;

class CustomBlockShopAddEvent extends PluginEvent implements Cancellable {
    use CancellableTrait;

    private ShopItem $item;
    private string $category;
    private string $subCategory;

    public function __construct(ShopItem $item, string $category, string $subCategory) {
        parent::__construct(Main::getInstance());
        $this->item = $item;
        $this->category = $category;
        $this->subCategory = $subCategory;
    }

    public function getItem(): ShopItem {
        return $this->item;
    }

    public function getCategory(): string {
        return $this->category;
    }

    public function getSubCategory(): string {
        return $this->subCategory;
    }
}
