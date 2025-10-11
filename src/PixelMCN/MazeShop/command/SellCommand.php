<?php

# ███╗░░░███╗░█████╗░███████╗███████╗░██████╗██╗░░██╗░█████╗░██████╗░
# ████╗░████║██╔══██╗╚════██║██╔════╝██╔════╝██║░░██║██╔══██╗██╔══██╗
# ██╔████╔██║███████║░░███╔═╝█████╗░░╚█████╗░███████║██║░░██║██████╔╝
# ██║╚██╔╝██║██╔══██║██╔══╝░░██╔══╝░░░╚═══██╗██╔══██║██║░░██║██╔═══╝░
# ██║░╚═╝░██║██║░░██║███████╗███████╗██████╔╝██║░░██║╚█████╔╝██║░░░░░
# ╚═╝░░░░░╚═╝╚═╝░░╚═╝╚══════╝╚══════╝╚═════╝░╚═╝░░╚═╝░╚════╝░╚═╝░░░░░

/*
MIT License

Copyright (c) 2025 Pixelis0P & MazecraftMCN Team

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

declare(strict_types=1);

namespace PixelMCN\MazeShop\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\item\StringToItemParser;
use PixelMCN\MazeShop\Main;
use PixelMCN\MazeShop\event\ItemSellEvent;

class SellCommand extends Command {

    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("sell", "Sell items from your inventory", "/sell <amount|all>");
        $this->setPermission("mazeshop.use");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage($this->plugin->getMessage("general.player-only"));
            return false;
        }

        if (!$this->testPermission($sender)) {
            return false;
        }

        if (empty($args)) {
            $sender->sendMessage("§cUsage: /sell <amount|all>");
            return false;
        }

        $heldItem = $sender->getInventory()->getItemInHand();
        
        if ($heldItem->isNull()) {
            $sender->sendMessage($this->plugin->getMessage("admin.hold-item"));
            return false;
        }

        // Find item in shop
        $shopItem = $this->findItemInShop($heldItem);
        
        if ($shopItem === null) {
            $sender->sendMessage($this->plugin->getMessage("shop.item-not-sellable"));
            return false;
        }

        if ($shopItem->getSellPrice() <= 0) {
            $sender->sendMessage($this->plugin->getMessage("shop.item-not-sellable"));
            return false;
        }

        // Determine amount to sell
        $amount = strtolower($args[0]) === "all" ? $heldItem->getCount() : (int)$args[0];
        
        if ($amount <= 0) {
            $sender->sendMessage($this->plugin->getMessage("general.invalid-amount"));
            return false;
        }

        if ($amount > $heldItem->getCount()) {
            $amount = $heldItem->getCount();
        }

        // Calculate total price
        $totalPrice = $shopItem->getSellPrice() * $amount;
        
        // Safety check - verify item before transaction
        $inventory = $sender->getInventory();
        $actualCount = 0;
        foreach ($inventory->getContents() as $item) {
            if ($item->getTypeId() === $heldItem->getTypeId()) {
                $actualCount += $item->getCount();
            }
        }
        
        if ($actualCount < $amount) {
            $sender->sendMessage("§cYou don't have enough {$shopItem->getName()} to sell! (Have: {$actualCount}, Need: {$amount})");
            return false;
        }
        
        // Fire event
        $event = new ItemSellEvent($sender, $shopItem, $amount, $totalPrice);
        $event->call();

        if ($event->isCancelled()) {
            $sender->sendMessage($this->plugin->getMessage("shop.sell-failed"));
            return true;
        }

        // Remove items from inventory (properly handle stacks)
        $remainingToRemove = $amount;
        foreach ($inventory->getContents() as $slot => $item) {
            if ($remainingToRemove <= 0) break;
            
            if ($item->getTypeId() === $heldItem->getTypeId()) {
                $removeCount = min($remainingToRemove, $item->getCount());
                $item->setCount($item->getCount() - $removeCount);
                
                if ($item->getCount() <= 0) {
                    $inventory->clear($slot);
                } else {
                    $inventory->setItem($slot, $item);
                }
                
                $remainingToRemove -= $removeCount;
            }
        }

        // Verify items were removed
        if ($remainingToRemove > 0) {
            $sender->sendMessage("§cError: Could not remove all items from inventory. Transaction cancelled.");
            return false;
        }

        // Add money
        $this->plugin->getEconomyManager()->addMoney($sender, $totalPrice);
        
        $currency = $this->plugin->getEconomyManager()->getCurrencySymbol();
        $sender->sendMessage($this->plugin->getMessage("shop.sell-success", [
            "amount" => $amount,
            "item" => $shopItem->getName(),
            "price" => $totalPrice,
            "currency" => $currency
        ]));

        return true;
    }

    private function findItemInShop(\pocketmine\item\Item $heldItem): ?\PixelMCN\MazeShop\shop\ShopItem {
        foreach ($this->plugin->getShopManager()->getCategories() as $category) {
            foreach ($category->getSubCategories() as $subCategory) {
                foreach ($subCategory->getItems() as $shopItem) {
                    // Parse shop item ID and compare with held item
                    $parsedItem = StringToItemParser::getInstance()->parse($shopItem->getId());
                    
                    if ($parsedItem !== null) {
                        // Match by type ID only (PM5 doesn't use meta)
                        if ($parsedItem->getTypeId() === $heldItem->getTypeId()) {
                            return $shopItem;
                        }
                    }
                }
            }
        }
        return null;
    }
}
