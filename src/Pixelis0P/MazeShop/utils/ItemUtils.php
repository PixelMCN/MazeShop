<?php

declare(strict_types=1);

namespace Pixelis0P\MazeShop\utils;

use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\player\PlayerInventory;
use Pixelis0P\MazeShop\MazeShop;

class ItemUtils {
    
    /**
     * Get the sell price for an item
     */
    public static function getSellPrice(MazeShop $plugin, Item $item): float {
        $categories = $plugin->getCategories();
        
        foreach ($categories as $category) {
            $items = $category["items"] ?? [];
            
            foreach ($items as $itemData) {
                $shopItem = StringToItemParser::getInstance()->parse($itemData["item"]);
                
                if ($shopItem !== null && $shopItem->equals($item, true, false)) {
                    return (float)($itemData["sell_price"] ?? 0.0);
                }
            }
        }
        
        return 0.0;
    }
    
    /**
     * Get the buy price for an item
     */
    public static function getBuyPrice(MazeShop $plugin, Item $item): float {
        $categories = $plugin->getCategories();
        
        foreach ($categories as $category) {
            $items = $category["items"] ?? [];
            
            foreach ($items as $itemData) {
                $shopItem = StringToItemParser::getInstance()->parse($itemData["item"]);
                
                if ($shopItem !== null && $shopItem->equals($item, true, false)) {
                    return (float)($itemData["buy_price"] ?? 0.0);
                }
            }
        }
        
        return 0.0;
    }
    
    /**
     * Count how many of a specific item are in the inventory
     */
    public static function countItemInInventory(\pocketmine\inventory\PlayerInventory $inventory, Item $item): int {
        $count = 0;
        
        foreach ($inventory->getContents() as $slot) {
            if ($slot->equals($item)) {
                $count += $slot->getCount();
            }
        }
        
        return $count;
    }
    
    /**
     * Check if an item is sellable
     */
    public static function isSellable(MazeShop $plugin, Item $item): bool {
        return self::getSellPrice($plugin, $item) > 0;
    }
    
    /**
     * Check if an item is buyable
     */
    public static function isBuyable(MazeShop $plugin, Item $item): bool {
        return self::getBuyPrice($plugin, $item) > 0;
    }
}