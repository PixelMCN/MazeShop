<?php

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
        
        $this->checkConfigVersions();
        
        $this->config = $this->getConfig();
        $this->shopConfig = new Config($this->getDataFolder() . "shop.yml", Config::YAML);
        
        $this->shopEnabled = $this->config->get("shop-enabled", true);
        
        // Check for MazePay
        $mazePayPlugin = $this->getServer()->getPluginManager()->getPlugin("MazePay");
        if ($mazePayPlugin instanceof MazePay) {
            $this->mazePay = $mazePayPlugin;
            $this->getLogger()->info("MazePay integration enabled!");
        } else {
            $this->getLogger()->warning("MazePay plugin not found! Shop will not function properly.");
        }
        
        $this->registerCommands();
        
        $this->getLogger()->info("MazeShop has been enabled!");
    }
    
    public function onDisable(): void {
        $this->getLogger()->info("MazeShop has been disabled!");
    }
    
    private function registerCommands(): void {
        $commandMap = $this->getServer()->getCommandMap();
        
        $commandMap->register("mazeshop", new ShopCommand($this));
        $commandMap->register("mazeshop", new SellCommand($this));
    }
    
    private function checkConfigVersions(): void {
        $currentConfigVersion = "1.0.0";
        $currentShopVersion = "1.0.0";
        
        // Check config.yml
        $config = $this->getConfig();
        if (!$config->exists("config-version") || $config->get("config-version") !== $currentConfigVersion) {
            $this->getLogger()->warning("Your config.yml is outdated. Creating a new one...");
            rename($this->getDataFolder() . "config.yml", $this->getDataFolder() . "config.old.yml");
            $this->saveDefaultConfig();
            $this->reloadConfig();
        }
        
        // Check shop.yml
        $shopConfig = new Config($this->getDataFolder() . "shop.yml", Config::YAML);
        if (!$shopConfig->exists("config-version") || $shopConfig->get("config-version") !== $currentShopVersion) {
            $this->getLogger()->warning("Your shop.yml is outdated. Creating a new one...");
            rename($this->getDataFolder() . "shop.yml", $this->getDataFolder() . "shop.old.yml");
            $this->saveResource("shop.yml", true);
        }
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
}