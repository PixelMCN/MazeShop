<?php

declare(strict_types=1);

namespace Pixelis0P\MazeShop\forms;

use pocketmine\form\Form;
use pocketmine\player\Player;
use pocketmine\item\StringToItemParser;
use Pixelis0P\MazeShop\MazeShop;
use Pixelis0P\MazeShop\utils\ItemUtils;

class BuySellForm implements Form {
    
    private MazeShop $plugin;
    private array $itemData;
    private array $category;
    
    public function __construct(MazeShop $plugin, array $itemData, array $category) {
        $this->plugin = $plugin;
        $this->itemData = $itemData;
        $this->category = $category;
    }
    
    public function jsonSerialize(): array {
        $itemName = ucfirst(str_replace("_", " ", $this->itemData["item"]));
        $buyPrice = $this->plugin->formatMoney($this->itemData["buy_price"]);
        $sellPrice = $this->plugin->formatMoney($this->itemData["sell_price"]);
        
        return [
            "type" => "custom_form",
            "title" => "§l§e" . $itemName,
            "content" => [
                [
                    "type" => "label",
                    "text" => "§eBuy Price: §a" . $buyPrice . "\n§eSell Price: §c" . $sellPrice . "\n\n§7Select an action and enter the amount:"
                ],
                [
                    "type" => "dropdown",
                    "text" => "§l§eAction",
                    "options" => ["Buy", "Sell"]
                ],
                [
                    "type" => "input",
                    "text" => "§l§eAmount",
                    "placeholder" => "Enter amount...",
                    "default" => "1"
                ]
            ]
        ];
    }
    
    public function handleResponse(Player $player, $data): void {
        if ($data === null) {
            // Return to shop
            $player->sendForm(new ShopForm($this->plugin, $this->category));
            return;
        }
        
        $action = $data[1]; // 0 = Buy, 1 = Sell
        $amount = $data[2] ?? "1";
        
        if (!is_numeric($amount) || (int)$amount <= 0) {
            $player->sendMessage($this->plugin->getPrefix() . $this->plugin->getMessage("sell-invalid-amount"));
            $player->sendForm(new BuySellForm($this->plugin, $this->itemData, $this->category));
            return;
        }
        
        $amount = (int)$amount;
        
        if ($action === 0) {
            // Buy
            $this->handleBuy($player, $amount);
        } else {
            // Sell
            $this->handleSell($player, $amount);
        }
    }
    
    private function handleBuy(Player $player, int $amount): void {
        $item = StringToItemParser::getInstance()->parse($this->itemData["item"]);
        
        if ($item === null) {
            $player->sendMessage($this->plugin->getPrefix() . "§cInvalid item!");
            return;
        }
        
        $totalPrice = $this->itemData["buy_price"] * $amount;
        $mazePay = $this->plugin->getMazePay();
        
        if ($mazePay === null) {
            $player->sendMessage($this->plugin->getPrefix() . $this->plugin->getMessage("mazepay-not-found"));
            return;
        }
        
        $uuid = $player->getUniqueId()->toString();
        $db = $mazePay->getDatabaseManager();
        $walletBalance = $db->getWalletBalance($uuid);
        
        if ($walletBalance < $totalPrice) {
            $message = str_replace("{price}", $this->plugin->formatMoney($totalPrice), $this->plugin->getMessage("buy-insufficient-money"));
            $player->sendMessage($this->plugin->getPrefix() . $message);
            return;
        }
        
        // Check inventory space
        $item->setCount($amount);
        if (!$player->getInventory()->canAddItem($item)) {
            $player->sendMessage($this->plugin->getPrefix() . $this->plugin->getMessage("buy-inventory-full"));
            return;
        }
        
        // Process transaction
        $db->deductWalletBalance($uuid, $totalPrice);
        $player->getInventory()->addItem($item);
        
        $message = str_replace(
            ["{amount}", "{item}", "{price}"],
            [$amount, $item->getName(), $this->plugin->formatMoney($totalPrice)],
            $this->plugin->getMessage("buy-success")
        );
        $player->sendMessage($this->plugin->getPrefix() . $message);
    }
    
    private function handleSell(Player $player, int $amount): void {
        $item = StringToItemParser::getInstance()->parse($this->itemData["item"]);
        
        if ($item === null) {
            $player->sendMessage($this->plugin->getPrefix() . "§cInvalid item!");
            return;
        }
        
        // Count items in inventory
        $availableCount = ItemUtils::countItemInInventory($player->getInventory(), $item);
        
        if ($availableCount === 0) {
            $message = str_replace("{item}", $item->getName(), $this->plugin->getMessage("sell-no-item"));
            $player->sendMessage($this->plugin->getPrefix() . $message);
            return;
        }
        
        if ($amount > $availableCount) {
            $amount = $availableCount;
        }
        
        $totalPrice = $this->itemData["sell_price"] * $amount;
        $mazePay = $this->plugin->getMazePay();
        
        if ($mazePay === null) {
            $player->sendMessage($this->plugin->getPrefix() . $this->plugin->getMessage("mazepay-not-found"));
            return;
        }
        
        // Remove items from inventory
        $item->setCount($amount);
        $player->getInventory()->removeItem($item);
        
        // Add money
        $uuid = $player->getUniqueId()->toString();
        $db = $mazePay->getDatabaseManager();
        $db->addWalletBalance($uuid, $totalPrice);
        
        $message = str_replace(
            ["{amount}", "{item}", "{price}"],
            [$amount, $item->getName(), $this->plugin->formatMoney($totalPrice)],
            $this->plugin->getMessage("sell-success")
        );
        $player->sendMessage($this->plugin->getPrefix() . $message);
    }
}