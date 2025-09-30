<?php

declare(strict_types=1);

namespace Pixelis0P\MazeShop\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use Pixelis0P\MazeShop\MazeShop;
use Pixelis0P\MazeShop\forms\CategoryListForm;
use Pixelis0P\MazeShop\forms\ShopForm;

class ShopCommand extends Command {
    
    private MazeShop $plugin;
    
    public function __construct(MazeShop $plugin) {
        parent::__construct("shop", "Open the shop", "/shop <category> or /shop <disable/enable>");
        $this->setPermission("mazeshop.command.shop");
        $this->plugin = $plugin;
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$this->testPermission($sender)) {
            $sender->sendMessage($this->plugin->getPrefix() . $this->plugin->getMessage("no-permission"));
            return false;
        }
        
        // Admin commands: /shop disable or /shop enable
        if (count($args) > 0 && ($args[0] === "disable" || $args[0] === "enable")) {
            if (!$sender->hasPermission("mazeshop.command.shop.admin")) {
                $sender->sendMessage($this->plugin->getPrefix() . $this->plugin->getMessage("no-permission"));
                return false;
            }
            
            if ($args[0] === "disable") {
                $this->plugin->setShopEnabled(false);
                $sender->sendMessage($this->plugin->getPrefix() . $this->plugin->getMessage("shop-disabled-admin"));
            } else {
                $this->plugin->setShopEnabled(true);
                $sender->sendMessage($this->plugin->getPrefix() . $this->plugin->getMessage("shop-enabled-admin"));
            }
            return true;
        }
        
        if (!$sender instanceof Player) {
            $sender->sendMessage($this->plugin->getPrefix() . $this->plugin->getMessage("player-only"));
            return false;
        }
        
        // Check if shop is enabled
        if (!$this->plugin->isShopEnabled() && !$sender->hasPermission("mazeshop.command.shop.admin")) {
            $sender->sendMessage($this->plugin->getPrefix() . $this->plugin->getMessage("shop-disabled"));
            return false;
        }
        
        // Check for MazePay
        if ($this->plugin->getMazePay() === null) {
            $sender->sendMessage($this->plugin->getPrefix() . $this->plugin->getMessage("mazepay-not-found"));
            return false;
        }
        
        // If no category specified, show category list
        if (count($args) === 0) {
            $sender->sendForm(new CategoryListForm($this->plugin));
            return true;
        }
        
        // Open specific category
        $categoryName = $args[0];
        $category = $this->plugin->getCategory($categoryName);
        
        if ($category === null) {
            $categories = $this->plugin->getCategories();
            $categoryNames = array_map(fn($cat) => $cat["name"], $categories);
            $message = str_replace("{category}", $categoryName, $this->plugin->getMessage("category-not-found"));
            $sender->sendMessage($this->plugin->getPrefix() . $message);
            
            $listMessage = str_replace("{categories}", implode(", ", $categoryNames), $this->plugin->getMessage("category-list"));
            $sender->sendMessage($this->plugin->getPrefix() . $listMessage);
            return false;
        }
        
        $sender->sendForm(new ShopForm($this->plugin, $category));
        
        return true;
    }
}