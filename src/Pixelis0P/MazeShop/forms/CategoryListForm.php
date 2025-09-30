<?php

declare(strict_types=1);

namespace Pixelis0P\MazeShop\forms;

use pocketmine\form\Form;
use pocketmine\player\Player;
use Pixelis0P\MazeShop\MazeShop;

class CategoryListForm implements Form {
    
    private MazeShop $plugin;
    
    public function __construct(MazeShop $plugin) {
        $this->plugin = $plugin;
    }
    
    public function jsonSerialize(): array {
        $categories = $this->plugin->getCategories();
        $buttons = [];
        
        foreach ($categories as $category) {
            $button = ["text" => "§l§e" . $category["name"]];
            
            $image = $category["image"] ?? "";
            if (!empty($image)) {
                $button["image"] = [
                    "type" => "url",
                    "data" => $image
                ];
            }
            
            $buttons[] = $button;
        }
        
        return [
            "type" => "form",
            "title" => "§l§bShop Categories",
            "content" => "§7Select a category to browse items:",
            "buttons" => $buttons
        ];
    }
    
    public function handleResponse(Player $player, $data): void {
        if ($data === null) {
            return;
        }
        
        $categories = $this->plugin->getCategories();
        
        if (!isset($categories[$data])) {
            return;
        }
        
        $selectedCategory = $categories[$data];
        $player->sendForm(new ShopForm($this->plugin, $selectedCategory));
    }
}