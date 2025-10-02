<?php

declare(strict_types=1);

namespace Pixelis0P\MazeShop\forms\admin;

use pocketmine\form\Form;
use pocketmine\player\Player;
use Pixelis0P\MazeShop\MazeShop;

class ItemEditForm implements Form {
    
    private MazeShop $plugin;
    private array $category;
    private array $item;
    
    public function __construct(MazeShop $plugin, array $category, array $item) {
        $this->plugin = $plugin;
        $this->category = $category;
        $this->item = $item;
    }
    
    public function jsonSerialize(): array {
        $buttons = [
            ["text" => "§l§eEdit Prices"],
            ["text" => "§l§cDelete Item"],
            ["text" => "§l§7Back to Items"]
        ];
        
        return [
            "type" => "form",
            "title" => "§l§e" . ucfirst(str_replace("_", " ", $this->item["item"])),
            "content" => "§eBuy Price: §a" . $this->item["buy_price"] . "\n§eSell Price: §c" . $this->item["sell_price"],
            "buttons" => $buttons
        ];
    }
    
    public function handleResponse(Player $player, $data): void {
        if ($data === null) {
            $player->sendForm(new ItemManageForm($this->plugin, $this->category));
            return;
        }
        
        switch ($data) {
            case 0: // Edit prices
                $player->sendForm(new ItemEditPriceForm($this->plugin, $this->category, $this->item));
                break;
            case 1: // Delete
                $player->sendForm(new ItemDeleteForm($this->plugin, $this->category, $this->item));
                break;
            case 2: // Back
                $player->sendForm(new ItemManageForm($this->plugin, $this->category));
                break;
        }
    }
}