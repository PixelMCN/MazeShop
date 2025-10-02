<?php

declare(strict_types=1);

namespace Pixelis0P\MazeShop\forms\admin;

use pocketmine\form\Form;
use pocketmine\player\Player;
use Pixelis0P\MazeShop\MazeShop;

class ItemManageForm implements Form {
    
    private MazeShop $plugin;
    private array $category;
    
    public function __construct(MazeShop $plugin, array $category) {
        $this->plugin = $plugin;
        $this->category = $category;
    }
    
    public function jsonSerialize(): array {
        $buttons = [];
        
        // Add "Create Item" button
        $buttons[] = ["text" => "§l§a+ Add Item"];
        
        // Add existing items
        foreach ($this->category["items"] as $item) {
            $buttons[] = [
                "text" => "§l§e" . ucfirst(str_replace("_", " ", $item["item"])) . 
                         "\n§r§7Buy: §a" . $item["buy_price"] . " §7| Sell: §c" . $item["sell_price"]
            ];
        }
        
        // Back button
        $buttons[] = ["text" => "§l§7« Back to Category"];
        
        return [
            "type" => "form",
            "title" => "§l§e" . $this->category["name"] . " - Items",
            "content" => "§7Manage items in this category:",
            "buttons" => $buttons
        ];
    }
    
    public function handleResponse(Player $player, $data): void {
        if ($data === null) {
            $player->sendForm(new CategoryEditForm($this->plugin, $this->category));
            return;
        }
        
        $items = $this->category["items"];
        
        if ($data === 0) {
            // Add item
            $player->sendForm(new ItemCreateForm($this->plugin, $this->category));
            return;
        }
        
        if ($data === count($items) + 1) {
            // Back button
            $player->sendForm(new CategoryEditForm($this->plugin, $this->category));
            return;
        }
        
        $itemIndex = $data - 1;
        if (isset($items[$itemIndex])) {
            $player->sendForm(new ItemEditForm($this->plugin, $this->category, $items[$itemIndex]));
        }
    }
}