<?php

declare(strict_types=1);

namespace Pixelis0P\MazeShop\forms\admin;

use pocketmine\form\Form;
use pocketmine\player\Player;
use Pixelis0P\MazeShop\MazeShop;

class CategoryCreateForm implements Form {
    
    private MazeShop $plugin;
    
    public function __construct(MazeShop $plugin) {
        $this->plugin = $plugin;
    }
    
    public function jsonSerialize(): array {
        return [
            "type" => "custom_form",
            "title" => "§l§aCreate Category",
            "content" => [
                [
                    "type" => "input",
                    "text" => "§l§eName",
                    "placeholder" => "e.g., Weapons"
                ],
                [
                    "type" => "input",
                    "text" => "§l§eIcon (Item ID)",
                    "placeholder" => "e.g., diamond_sword"
                ],
                [
                    "type" => "input",
                    "text" => "§l§eImage URL (Optional)",
                    "placeholder" => "https://..."
                ]
            ]
        ];
    }
    
    public function handleResponse(Player $player, $data): void {
        if ($data === null) {
            $player->sendForm(new CategoryManageForm($this->plugin));
            return;
        }
        
        $name = trim($data[0] ?? "");
        $icon = trim($data[1] ?? "");
        $image = trim($data[2] ?? "");
        
        if (empty($name) || empty($icon)) {
            $player->sendMessage($this->plugin->getPrefix() . "§cPlease fill in all required fields!");
            $player->sendForm(new CategoryCreateForm($this->plugin));
            return;
        }
        
        if ($this->plugin->addCategory($name, $icon, $image)) {
            $player->sendMessage($this->plugin->getPrefix() . "§aCategory '§e" . $name . "§a' created successfully!");
            $player->sendForm(new CategoryManageForm($this->plugin));
        } else {
            $player->sendMessage($this->plugin->getPrefix() . "§cCategory already exists!");
            $player->sendForm(new CategoryCreateForm($this->plugin));
        }
    }
}