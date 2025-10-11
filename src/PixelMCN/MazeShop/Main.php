<?php
/*

███╗░░░███╗░█████╗░███████╗███████╗░██████╗██╗░░██╗░█████╗░██████╗░
████╗░████║██╔══██╗╚════██║██╔════╝██╔════╝██║░░██║██╔══██╗██╔══██╗
██╔████╔██║███████║░░███╔═╝█████╗░░╚█████╗░███████║██║░░██║██████╔╝
██║╚██╔╝██║██╔══██║██╔══╝░░██╔══╝░░░╚═══██╗██╔══██║██║░░██║██╔═══╝░
██║░╚═╝░██║██║░░██║███████╗███████╗██████╔╝██║░░██║╚█████╔╝██║░░░░░
╚═╝░░░░░╚═╝╚═╝░░╚═╝╚══════╝╚══════╝╚═════╝░╚═╝░░╚═╝░╚════╝░╚═╝░░░░░


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

namespace PixelMCN\MazeShop;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use PixelMCN\MazeShop\command\AuctionAdminCommand;
use PixelMCN\MazeShop\command\AuctionCommand;
use PixelMCN\MazeShop\command\ShopAdminCommand;
use PixelMCN\MazeShop\command\ShopCommand;
use PixelMCN\MazeShop\command\SellCommand;
use PixelMCN\MazeShop\database\DatabaseManager;
use PixelMCN\MazeShop\economy\EconomyManager;
use PixelMCN\MazeShop\shop\ShopManager;
use PixelMCN\MazeShop\auction\AuctionManager;

class Main extends PluginBase {
    use SingletonTrait;

    private DatabaseManager $databaseManager;
    private EconomyManager $economyManager;
    private ShopManager $shopManager;
    private AuctionManager $auctionManager;
    private array $messages = [];

    protected function onLoad(): void {
        self::setInstance($this);
    }

    protected function onEnable(): void {
        $this->saveDefaultConfig();
        $this->saveResource("messages.yml");
        $this->saveResource("shop.yml");
        
        $this->loadMessages();
        
        // Initialize economy manager first
        $this->economyManager = new EconomyManager($this);
        
        // Check if economy provider is available
        if (!$this->economyManager->hasProvider()) {
            $this->getLogger()->error("§cNo economy plugin found! Please install MazePay or BedrockEconomy.");
            $this->getLogger()->error("§cDisabling MazeShop...");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }
        
        // Initialize other managers
        $this->databaseManager = new DatabaseManager($this);
        $this->shopManager = new ShopManager($this);
        $this->auctionManager = new AuctionManager($this);
        
        // Register commands and listeners
        $this->registerCommands();
        $this->registerListeners();
        
        $this->getLogger()->info("MazeShop by PixelMCN has been enabled!");
    }

    protected function onDisable(): void {
        if (isset($this->databaseManager)) {
            $this->databaseManager->close();
        }
        if (isset($this->auctionManager)) {
            $this->auctionManager->saveAllAuctions();
        }
        $this->getLogger()->info("MazeShop has been disabled!");
    }

    private function registerCommands(): void {
        $commandMap = $this->getServer()->getCommandMap();
        $commandMap->register("mazeshop", new ShopCommand($this));
        $commandMap->register("mazeshop", new ShopAdminCommand($this));
        $commandMap->register("mazeshop", new AuctionCommand($this));
        $commandMap->register("mazeshop", new AuctionAdminCommand($this));
        $commandMap->register("mazeshop", new SellCommand($this));
    }

    private function registerListeners(): void {
        // No custom listeners needed - InvMenu handles all inventory interactions
    }

    public function loadMessages(): void {
        $messagesFile = $this->getDataFolder() . "messages.yml";
        if (!file_exists($messagesFile)) {
            $this->saveResource("messages.yml");
        }
        $this->messages = yaml_parse_file($messagesFile);
    }

    public function getMessage(string $key, array $replacements = []): string {
        $keys = explode(".", $key);
        $message = $this->messages;
        
        foreach ($keys as $k) {
            if (!isset($message[$k])) {
                return $key;
            }
            $message = $message[$k];
        }
        
        if (!is_string($message)) {
            return $key;
        }
        
        foreach ($replacements as $search => $replace) {
            $message = str_replace("{" . $search . "}", (string)$replace, $message);
        }
        
        return $message;
    }

    public function getDatabaseManager(): DatabaseManager {
        return $this->databaseManager;
    }

    public function getEconomyManager(): EconomyManager {
        return $this->economyManager;
    }

    public function getShopManager(): ShopManager {
        return $this->shopManager;
    }

    public function getAuctionManager(): AuctionManager {
        return $this->auctionManager;
    }

    public function reloadConfigs(): void {
        $this->reloadConfig();
        $this->loadMessages();
        $this->shopManager->reload();
        $this->getLogger()->info("All configurations reloaded!");
    }
}
