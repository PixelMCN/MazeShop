<?php

declare(strict_types=1);

namespace Pixelis0P\MazeShop\forms;

use pocketmine\form\Form;
use pocketmine\player\Player;
use Pixelis0P\MazeShop\MazeShop;

class ShopForm implements Form {
    
    private MazeShop $plugin;
    private array $category;
    
    public function __construct(MazeShop $plugin, array $category) {
        $this->plugin = $plugin;
        $this->category = $category;
    }
    
    public function jsonSerialize(): array {
        $items = $this->category["items"] ?? [];
        $buttons = [];
        
        foreach ($items as $itemData) {
            $buyPrice = $this->plugin->formatMoney($itemData["buy_price"]);
            $sellPrice = $this->plugin->formatMoney($itemData["sell_price"]);
            
            $button = [
                "text" => "§l§e" . ucfirst(str_replace("_", " ", $itemData["item"])) . "\n§r§7Buy: §a" . $buyPrice . " §7| Sell: §c" . $sellPrice
            ];
            
            $image = $itemData["image"] ?? "";
            if (!empty($image)) {
                $button["image"] = [
                    "type" => "url",
                    "data" => $image
                ];
            }
            
            $buttons[] = $button;
        }
        
        $title = str_replace("{category}", $this->category["name"], $this->plugin->getMessage("shop-title"));
        
        return [
            "type" => "form",
            "title" => $title,
            "content" => "§7Select an item to buy or sell:",
            "buttons" => $buttons
        ];
    }
    
    public function handleResponse(Player $player, $data): void {
        if ($data === null) {
            return;
        }
        
        $items = $this->category["items"] ?? [];
        
        if (!isset($items[$data])) {
            return;
        }
        
        $selectedItem = $items[$data];
        $player->sendForm(new BuySellForm($this->plugin, $selectedItem, $this->category));
    }
}