<?php

declare(strict_types=1);

namespace Pixelis0P\MazeShop\forms\admin;

use pocketmine\form\Form;
use pocketmine\player\Player;
use Pixelis0P\MazeShop\MazeShop;

class CategoryEditInfoForm implements Form {
    
    private MazeShop $plugin;
    private array $category;
    
    public function __construct(MazeShop $plugin, array $category) {
        $this->plugin = $plugin;
        $this->category = $category;
    }
    
    public function jsonSerialize(): array {
        return [
            "type" => "custom_form",
            "title" => "§l§eEdit Category",
            "content" => [
                [
                    "type" => "input",
                    "text" => "§l§eName",
                    "placeholder" => "Category name",
                    "default" => $this->category["name"]
                ],
                [
                    "type" => "input",
                    "text" => "§l§eIcon (Item ID)",
                    "placeholder" => "Item ID",
                    "default" => $this->category["icon"]
                ],
                [
                    "type" => "input",
                    "text" => "§l§eImage URL",
                    "placeholder" => "https://...",
                    "default" => $this->category["image"] ?? ""
                ]
            ]
        ];
    }
    
    public function handleResponse(Player $player, $data): void {
        if ($data === null) {
            $player->sendForm(new CategoryEditForm($this->plugin, $this->category));
            return;
        }
        
        $newName = trim($data[0] ?? "");
        $icon = trim($data[1] ?? "");
        $image = trim($data[2] ?? "");
        
        if (empty($newName) || empty($icon)) {
            $player->sendMessage($this->plugin->getPrefix() . "§cPlease fill in all required fields!");
            $player->sendForm(new CategoryEditInfoForm($this->plugin, $this->category));
            return;
        }
        
        if ($this->plugin->editCategory($this->category["name"], $newName, $icon, $image)) {
            $player->sendMessage($this->plugin->getPrefix() . "§aCategory updated successfully!");
            // Reload category data
            $updatedCategory = $this->plugin->getCategory($newName);
            if ($updatedCategory !== null) {
                $player->sendForm(new CategoryEditForm($this->plugin, $updatedCategory));
            } else {
                $player->sendForm(new CategoryManageForm($this->plugin));
            }
        } else {
            $player->sendMessage($this->plugin->getPrefix() . "§cFailed to update category!");
            $player->sendForm(new CategoryEditForm($this->plugin, $this->category));
        }
    }
}