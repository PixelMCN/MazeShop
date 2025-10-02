<?php

#      ___           ___           ___           ___           ___           ___           ___           ___   
#     /\  \         /\  \         /\__\         /\__\         /\__\         /\  \         /\  \         /\  \  
#    |::\  \       /::\  \       /::|  |       /:/ _/_       /:/ _/_        \:\  \       /::\  \       /::\  \ 
#    |:|:\  \     /:/\:\  \     /:/:|  |      /:/ /\__\     /:/ /\  \        \:\  \     /:/\:\  \     /:/\:\__\
#  __|:|\:\  \   /:/ /::\  \   /:/|:|  |__   /:/ /:/ _/_   /:/ /::\  \   ___ /::\  \   /:/  \:\  \   /:/ /:/  /
# /::::|_\:\__\ /:/_/:/\:\__\ /:/ |:| /\__\ /:/_/:/ /\__\ /:/_/:/\:\__\ /\  /:/\:\__\ /:/__/ \:\__\ /:/_/:/  / 
# \:\~~\  \/__/ \:\/:/  \/__/ \/__|:|/:/  / \:\/:/ /:/  / \:\/:/ /:/  / \:\/:/  \/__/ \:\  \ /:/  / \:\/:/  /  
#  \:\  \        \::/__/          |:/:/  /   \::/_/:/  /   \::/ /:/  /   \::/__/       \:\  /:/  /   \::/__/   
#   \:\  \        \:\  \          |::/  /     \:\/:/  /     \/_/:/  /     \:\  \        \:\/:/  /     \:\  \   
#    \:\__\        \:\__\         |:/  /       \::/  /        /:/  /       \:\__\        \::/  /       \:\__\  
#     \/__/         \/__/         |/__/         \/__/         \/__/         \/__/         \/__/         \/__/   

declare(strict_types=1);

namespace Pixelis0P\MazeShop;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use Pixelis0P\MazeShop\commands\ShopCommand;
use Pixelis0P\MazeShop\commands\SellCommand;
use Pixelis0P\MazePay\MazePay;

class MazeShop extends PluginBase {
    
    private static MazeShop $instance;
    private Config $config;
    private Config $shopConfig;
    private bool $shopEnabled = true;
    private ?MazePay $mazePay = null;
    
    public function onEnable(): void {
        self::$instance = $this;
        
        $this->saveDefaultConfig();
        $this->saveResource("shop.yml");
        
        $this->config = $this->getConfig();
        $this->shopConfig = new Config($this->getDataFolder() . "shop.yml", Config::YAML);
        
        $this->shopEnabled = $this->config->get("shop-enabled", true);
        
        // Check for MazePay
        $mazePayPlugin = $this->getServer()->getPluginManager()->getPlugin("MazePay");
        if ($mazePayPlugin instanceof MazePay) {
            $this->mazePay = $mazePayPlugin;
            $this->getLogger()->info("§b§l[MazePay] §aintegration enabled!");
        } else {
            $this->getLogger()->warning("MazePay plugin not found! Shop will not function properly.");
        }
        
        $this->registerCommands();
        
        $this->getLogger()->info("§b§l[MazePay] §aplugin enabled!");
    }
    
    public function onDisable(): void {
        $this->getLogger()->info("§b§l[MazePay] §aplugin disabled!");
    }
    
    private function registerCommands(): void {
        $commandMap = $this->getServer()->getCommandMap();
        
        $commandMap->register("mazeshop", new ShopCommand($this));
        $commandMap->register("mazeshop", new SellCommand($this));
        $commandMap->register("mazeshop", new \Pixelis0P\MazeShop\commands\ShopAdminCommand($this));
    }
    
    public static function getInstance(): MazeShop {
        return self::$instance;
    }
    
    public function getShopConfig(): Config {
        return $this->shopConfig;
    }
    
    public function getMazePay(): ?MazePay {
        return $this->mazePay;
    }
    
    public function isShopEnabled(): bool {
        return $this->shopEnabled;
    }
    
    public function setShopEnabled(bool $enabled): void {
        $this->shopEnabled = $enabled;
        $this->config->set("shop-enabled", $enabled);
        $this->config->save();
    }
    
    public function getPrefix(): string {
        return $this->config->get("prefix", "§b[MazeShop]§r ");
    }
    
    public function getMessage(string $key): string {
        $messages = $this->config->get("messages", []);
        return $messages[$key] ?? "Message not found: $key";
    }
    
    public function getCategories(): array {
        return $this->shopConfig->get("categories", []);
    }
    
    public function getCategory(string $categoryName): ?array {
        $categories = $this->getCategories();
        foreach ($categories as $category) {
            if (strtolower($category["name"]) === strtolower($categoryName)) {
                return $category;
            }
        }
        return null;
    }
    
    public function formatMoney(float $amount): string {
        if ($this->mazePay !== null) {
            return $this->mazePay->formatMoney($amount);
        }
        return "$" . number_format($amount, 2);
    }
    
    public function saveShopConfig(): void {
        $this->shopConfig->save();
    }
    
    public function reloadShopConfig(): void {
        $this->shopConfig->reload();
    }
    
    public function addCategory(string $name, string $icon, string $image = ""): bool {
        $categories = $this->shopConfig->get("categories", []);
        
        // Check if category already exists
        foreach ($categories as $category) {
            if (strtolower($category["name"]) === strtolower($name)) {
                return false;
            }
        }
        
        $categories[] = [
            "name" => $name,
            "icon" => $icon,
            "image" => $image,
            "items" => []
        ];
        
        $this->shopConfig->set("categories", $categories);
        $this->shopConfig->save();
        return true;
    }
    
    public function deleteCategory(string $name): bool {
        $categories = $this->shopConfig->get("categories", []);
        $found = false;
        
        foreach ($categories as $key => $category) {
            if (strtolower($category["name"]) === strtolower($name)) {
                unset($categories[$key]);
                $found = true;
                break;
            }
        }
        
        if ($found) {
            $this->shopConfig->set("categories", array_values($categories));
            $this->shopConfig->save();
        }
        
        return $found;
    }
    
    public function editCategory(string $oldName, string $newName, string $icon, string $image): bool {
        $categories = $this->shopConfig->get("categories", []);
        $found = false;
        
        foreach ($categories as $key => $category) {
            if (strtolower($category["name"]) === strtolower($oldName)) {
                $categories[$key]["name"] = $newName;
                $categories[$key]["icon"] = $icon;
                $categories[$key]["image"] = $image;
                $found = true;
                break;
            }
        }
        
        if ($found) {
            $this->shopConfig->set("categories", $categories);
            $this->shopConfig->save();
        }
        
        return $found;
    }
    
    public function addItem(string $categoryName, string $itemName, float $buyPrice, float $sellPrice, string $image = ""): bool {
        $categories = $this->shopConfig->get("categories", []);
        
        foreach ($categories as $key => $category) {
            if (strtolower($category["name"]) === strtolower($categoryName)) {
                // Check if item already exists in this category
                foreach ($category["items"] as $item) {
                    if (strtolower($item["item"]) === strtolower($itemName)) {
                        return false;
                    }
                }
                
                $categories[$key]["items"][] = [
                    "item" => $itemName,
                    "buy_price" => $buyPrice,
                    "sell_price" => $sellPrice,
                    "image" => $image
                ];
                
                $this->shopConfig->set("categories", $categories);
                $this->shopConfig->save();
                return true;
            }
        }
        
        return false;
    }
    
    public function deleteItem(string $categoryName, string $itemName): bool {
        $categories = $this->shopConfig->get("categories", []);
        
        foreach ($categories as $catKey => $category) {
            if (strtolower($category["name"]) === strtolower($categoryName)) {
                foreach ($category["items"] as $itemKey => $item) {
                    if (strtolower($item["item"]) === strtolower($itemName)) {
                        unset($categories[$catKey]["items"][$itemKey]);
                        $categories[$catKey]["items"] = array_values($categories[$catKey]["items"]);
                        $this->shopConfig->set("categories", $categories);
                        $this->shopConfig->save();
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    public function editItem(string $categoryName, string $itemName, float $buyPrice, float $sellPrice, string $image): bool {
        $categories = $this->shopConfig->get("categories", []);
        
        foreach ($categories as $catKey => $category) {
            if (strtolower($category["name"]) === strtolower($categoryName)) {
                foreach ($category["items"] as $itemKey => $item) {
                    if (strtolower($item["item"]) === strtolower($itemName)) {
                        $categories[$catKey]["items"][$itemKey]["buy_price"] = $buyPrice;
                        $categories[$catKey]["items"][$itemKey]["sell_price"] = $sellPrice;
                        $categories[$catKey]["items"][$itemKey]["image"] = $image;
                        $this->shopConfig->set("categories", $categories);
                        $this->shopConfig->save();
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
}