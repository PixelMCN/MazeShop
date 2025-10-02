<?php

declare(strict_types=1);

namespace Pixelis0P\MazeShop\forms\admin;

use pocketmine\form\Form;
use pocketmine\player\Player;
use Pixelis0P\MazeShop\MazeShop;

class ItemEditPriceForm implements Form {
    
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
            "type" => "custom_form",
            "title" => "§l§eEdit Item Prices",
            "content" => [
                [
                    "type" => "label",
                    "text" => "§eEditing: §a" . ucfirst(str_replace("_", " ", $this->item["item"]))
                ],
                [
                    "type" => "input",
                    "text" => "§l§eBuy Price",
                    "placeholder" => "Enter buy price",
                    "default" => (string)$this->item["buy_price"]
                ],
                [
                    "type" => "input",
                    "text" => "§l§eSell Price",
                    "placeholder" => "Enter sell price",
                    "default" => (string)$this->item["sell_price"]
                ],
                [
                    "type" => "input",
                    "text" => "§l§eImage URL",
                    "placeholder" => "https://...",
                    "default" => $this->item["image"] ?? ""
                ]
            ]
        ];
    }
    
    public function handleResponse(Player $player, $data): void {
        if ($data === null) {
            $player->sendForm(new ItemEditForm($this->plugin, $this->category, $this->item));
            return;
        }
        
        $buyPrice = trim($data[1] ?? "");
        $sellPrice = trim($data[2] ?? "");
        $image = trim($data[3] ?? "");
        
        if (empty($buyPrice) || empty($sellPrice)) {
            $player->sendMessage($this->plugin->getPrefix() . "§cPlease fill in all required fields!");
            $player->sendForm(new ItemEditPriceForm($this->plugin, $this->category, $this->item));
            return;
        }
        
        if (!is_numeric($buyPrice) || !is_numeric($sellPrice)) {
            $player->sendMessage($this->plugin->getPrefix() . "§cPrices must be numbers!");
            $player->sendForm(new ItemEditPriceForm($this->plugin, $this->category, $this->item));
            return;
        }
        
        if ($this->plugin->editItem($this->category["name"], $this->item["item"], (float)$buyPrice, (float)$sellPrice, $image)) {
            $player->sendMessage($this->plugin->getPrefix() . "§aItem prices updated successfully!");
            // Reload category
            $updatedCategory = $this->plugin->getCategory($this->category["name"]);
            if ($updatedCategory !== null) {
                $player->sendForm(new ItemManageForm($this->plugin, $updatedCategory));
            }
        } else {
            $player->sendMessage($this->plugin->getPrefix() . "§cFailed to update item!");
            $player->sendForm(new ItemEditForm($this->plugin, $this->category, $this->item));
        }
    }
}