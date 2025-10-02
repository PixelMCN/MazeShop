<?php

declare(strict_types=1);

namespace Pixelis0P\MazeShop\forms\admin;

use pocketmine\form\Form;
use pocketmine\player\Player;
use Pixelis0P\MazeShop\MazeShop;

class CategoryEditForm implements Form {
    
    private MazeShop $plugin;
    private array $category;
    
    public function __construct(MazeShop $plugin, array $category) {
        $this->plugin = $plugin;
        $this->category = $category;
    }
    
    public function jsonSerialize(): array {
        $buttons = [
            ["text" => "§l§eEdit Category Info"],
            ["text" => "§l§aManage Items"],
            ["text" => "§l§cDelete Category"],
            ["text" => "§l§7Back to Categories"]
        ];
        
        return [
            "type" => "form",
            "title" => "§l§e" . $this->category["name"],
            "content" => "§7Items: §e" . count($this->category["items"]) . "\n§7Icon: §e" . $this->category["icon"],
            "buttons" => $buttons
        ];
    }
    
    public function handleResponse(Player $player, $data): void {
        if ($data === null) {
            $player->sendForm(new CategoryManageForm($this->plugin));
            return;
        }
        
        switch ($data) {
            case 0: // Edit info
                $player->sendForm(new CategoryEditInfoForm($this->plugin, $this->category));
                break;
            case 1: // Manage items
                $player->sendForm(new ItemManageForm($this->plugin, $this->category));
                break;
            case 2: // Delete
                $player->sendForm(new CategoryDeleteForm($this->plugin, $this->category));
                break;
            case 3: // Back
                $player->sendForm(new CategoryManageForm($this->plugin));
                break;
        }
    }
}