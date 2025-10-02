<?php

declare(strict_types=1);

namespace Pixelis0P\MazeShop\forms\admin;

use pocketmine\form\Form;
use pocketmine\player\Player;
use Pixelis0P\MazeShop\MazeShop;

class ItemCreateForm implements Form {
    
    private MazeShop $plugin;
    private array $category;
    
    public function __construct(MazeShop $plugin, array $category) {
        $this->plugin = $plugin;
        $this->category = $category;
    }
    
    public function jsonSerialize(): array {
        return [
            "type" => "custom_form",
            "title" => "§l§aAdd Item",
            "content" => [
                [
                    "type" => "input",
                    "text" => "§l§eItem ID",
                    "placeholder" => "e.g., diamond_sword"
                ],
                [
                    "type" => "input",
                    "text" => "§l§eBuy Price",
                    "placeholder" => "e.g., 100.0"
                ],
                [
                    "type" => "input",
                    "text" => "§l§eSell Price",
                    "placeholder" => "e.g., 50.0"
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
            $player->sendForm(new ItemManageForm($this->plugin, $this->category));
            return;
        }
        
        $itemName = trim($data[0] ?? "");
        $buyPrice = trim($data[1] ?? "");
        $sellPrice = trim($data[2] ?? "");
        $image = trim($data[3] ?? "");
        
        if (empty($itemName) || empty($buyPrice) || empty($sellPrice)) {
            $player->sendMessage($this->plugin->getPrefix() . "§cPlease fill in all required fields!");
            $player->sendForm(new ItemCreateForm($this->plugin, $this->category));
            return;
        }
        
        if (!is_numeric($buyPrice) || !is_numeric($sellPrice)) {
            $player->sendMessage($this->plugin->getPrefix() . "§cPrices must be numbers!");
            $player->sendForm(new ItemCreateForm($this->plugin, $this->category));
            return;
        }
        
        if ($this->plugin->addItem($this->category["name"], $itemName, (float)$buyPrice, (float)$sellPrice, $image)) {
            $player->sendMessage($this->plugin->getPrefix() . "§aItem '§e" . $itemName . "§a' added successfully!");
            // Reload category
            $updatedCategory = $this->plugin->getCategory($this->category["name"]);
            if ($updatedCategory !== null) {
                $player->sendForm(new ItemManageForm($this->plugin, $updatedCategory));
            }
        } else {
            $player->sendMessage($this->plugin->getPrefix() . "§cItem already exists in this category!");
            $player->sendForm(new ItemCreateForm($this->plugin, $this->category));
        }
    }
}