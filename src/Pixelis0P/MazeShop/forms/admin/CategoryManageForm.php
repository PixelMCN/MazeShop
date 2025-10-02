<?php

declare(strict_types=1);

namespace Pixelis0P\MazeShop\forms\admin;

use pocketmine\form\Form;
use pocketmine\player\Player;
use Pixelis0P\MazeShop\MazeShop;

class CategoryManageForm implements Form {
    
    private MazeShop $plugin;
    
    public function __construct(MazeShop $plugin) {
        $this->plugin = $plugin;
    }
    
    public function jsonSerialize(): array {
        $categories = $this->plugin->getCategories();
        $buttons = [];
        
        // Add "Create Category" button
        $buttons[] = ["text" => "§l§a+ Create Category"];
        
        // Add existing categories
        foreach ($categories as $category) {
            $buttons[] = ["text" => "§l§e" . $category["name"] . "\n§r§7Click to manage"];
        }
        
        return [
            "type" => "form",
            "title" => "§l§cShop Admin - Categories",
            "content" => "§7Select a category to manage or create a new one:",
            "buttons" => $buttons
        ];
    }
    
    public function handleResponse(Player $player, $data): void {
        if ($data === null) {
            return;
        }
        
        if ($data === 0) {
            // Create new category
            $player->sendForm(new CategoryCreateForm($this->plugin));
            return;
        }
        
        $categories = $this->plugin->getCategories();
        $categoryIndex = $data - 1;
        
        if (isset($categories[$categoryIndex])) {
            $player->sendForm(new CategoryEditForm($this->plugin, $categories[$categoryIndex]));
        }
    }
}