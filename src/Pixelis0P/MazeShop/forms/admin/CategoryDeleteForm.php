<?php

declare(strict_types=1);

namespace Pixelis0P\MazeShop\forms\admin;

use pocketmine\form\Form;
use pocketmine\player\Player;
use Pixelis0P\MazeShop\MazeShop;

class CategoryDeleteForm implements Form {
    
    private MazeShop $plugin;
    private array $category;
    
    public function __construct(MazeShop $plugin, array $category) {
        $this->plugin = $plugin;
        $this->category = $category;
    }
    
    public function jsonSerialize(): array {
        return [
            "type" => "modal",
            "title" => "§l§cDelete Category",
            "content" => "§cAre you sure you want to delete the category:\n\n§e" . $this->category["name"] . "\n\n§cThis will also delete all " . count($this->category["items"]) . " items in this category!\n\n§7This action cannot be undone!",
            "button1" => "§l§cYes, Delete",
            "button2" => "§l§7No, Cancel"
        ];
    }
    
    public function handleResponse(Player $player, $data): void {
        if ($data === null || $data === false) {
            $player->sendForm(new CategoryEditForm($this->plugin, $this->category));
            return;
        }
        
        if ($this->plugin->deleteCategory($this->category["name"])) {
            $player->sendMessage($this->plugin->getPrefix() . "§aCategory '§e" . $this->category["name"] . "§a' deleted successfully!");
            $player->sendForm(new CategoryManageForm($this->plugin));
        } else {
            $player->sendMessage($this->plugin->getPrefix() . "§cFailed to delete category!");
            $player->sendForm(new CategoryEditForm($this->plugin, $this->category));
        }
    }
}