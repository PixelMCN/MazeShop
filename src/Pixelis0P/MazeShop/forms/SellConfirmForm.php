<?php

declare(strict_types=1);

namespace Pixelis0P\MazeShop\forms;

use pocketmine\form\Form;
use pocketmine\player\Player;
use pocketmine\item\Item;
use Pixelis0P\MazeShop\MazeShop;
use Pixelis0P\MazeShop\utils\ItemUtils;

class SellConfirmForm implements Form {
    
    private MazeShop $plugin;
    private Item $item;
    private int $amount;
    private float $sellPricePerItem;
    
    public function __construct(MazeShop $plugin, Item $item, int $amount, float $sellPricePerItem) {
        $this->plugin = $plugin;
        $this->item = $item;
        $this->amount = $amount;
        $this->sellPricePerItem = $sellPricePerItem;
    }
    
    public function jsonSerialize(): array {
        $totalPrice = $this->sellPricePerItem * $this->amount;
        
        $content = str_replace(
            ["{amount}", "{item}", "{price}"],
            [$this->amount, $this->item->getName(), $this->plugin->formatMoney($totalPrice)],
            $this->plugin->getMessage("sell-confirm-content")
        );
        
        return [
            "type" => "modal",
            "title" => $this->plugin->getMessage("sell-confirm-title"),
            "content" => $content,
            "button1" => $this->plugin->getMessage("sell-confirm-yes"),
            "button2" => $this->plugin->getMessage("sell-confirm-no")
        ];
    }
    
    public function handleResponse(Player $player, $data): void {
        if ($data === null || $data === false) {
            $player->sendMessage($this->plugin->getPrefix() . $this->plugin->getMessage("sell-cancelled"));
            return;
        }
        
        // Player confirmed, proceed with sale
        $mazePay = $this->plugin->getMazePay();
        
        if ($mazePay === null) {
            $player->sendMessage($this->plugin->getPrefix() . $this->plugin->getMessage("mazepay-not-found"));
            return;
        }
        
        // Count items again to make sure player still has them
        $availableCount = ItemUtils::countItemInInventory($player->getInventory(), $this->item);
        
        if ($availableCount < $this->amount) {
            $message = str_replace("{item}", $this->item->getName(), $this->plugin->getMessage("sell-no-item"));
            $player->sendMessage($this->plugin->getPrefix() . $message);
            return;
        }
        
        // Remove items
        $itemToRemove = clone $this->item;
        $itemToRemove->setCount($this->amount);
        $player->getInventory()->removeItem($itemToRemove);
        
        // Add money
        $totalPrice = $this->sellPricePerItem * $this->amount;
        $uuid = $player->getUniqueId()->toString();
        $db = $mazePay->getDatabaseManager();
        $db->addWalletBalance($uuid, $totalPrice);
        
        $message = str_replace(
            ["{amount}", "{item}", "{price}"],
            [$this->amount, $this->item->getName(), $this->plugin->formatMoney($totalPrice)],
            $this->plugin->getMessage("sell-success")
        );
        $player->sendMessage($this->plugin->getPrefix() . $message);
    }
}