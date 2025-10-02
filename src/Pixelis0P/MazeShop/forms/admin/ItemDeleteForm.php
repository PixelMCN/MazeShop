<?php

declare(strict_types=1);

namespace Pixelis0P\MazeShop\forms\admin;

use pocketmine\form\Form;
use pocketmine\player\Player;
use Pixelis0P\MazeShop\MazeShop;

class ItemDeleteForm implements Form {
    
    private MazeShop $plugin;
    private array $category;
    private array $item;
    
    public function __construct(MazeShop $plugin, array $category, array $item) {
        $this->plugin = $plugin;
        $this->category = $category;
        $this->item = $item;
    }
    
    public function jsonSerialize(): array {
        return [
            "type" => "modal",
            "title" => "§l§cDelete Item",
            "content" => "§cAre you sure you want to delete:\n\n§e" . ucfirst(str_replace("_", " ", $this->item["item"])) . "\n\n§7This action cannot be undone!",
            "button1" => "§l§cYes, Delete",
            "button2" => "§l§7No, Cancel"
        ];
    }
    
    public function handleResponse(Player $player, $data): void {
        if ($data === null || $data === false) {
            $player->sendForm(new ItemEditForm($this->plugin, $this->category, $this->item));
            return;
        }
        
        if ($this->plugin->deleteItem($this->category["name"], $this->item["item"])) {
            $player->sendMessage($this->plugin->getPrefix() . "§aItem '§e" . $this->item["item"] . "§a' deleted successfully!");
            // Reload category
            $updatedCategory = $this->plugin->getCategory($this->category["name"]);
            if ($updatedCategory !== null) {
                $player->sendForm(new ItemManageForm($this->plugin, $updatedCategory));
            }
        } else {
            $player->sendMessage($this->plugin->getPrefix() . "§cFailed to delete item!");
            $player->sendForm(new ItemEditForm($this->plugin, $this->category, $this->item));
        }
    }
}