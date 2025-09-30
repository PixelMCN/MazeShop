<?php

declare(strict_types=1);

namespace Pixelis0P\MazeShop\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\item\VanillaItems;
use Pixelis0P\MazeShop\MazeShop;
use Pixelis0P\MazeShop\forms\SellConfirmForm;
use Pixelis0P\MazeShop\utils\ItemUtils;

class SellCommand extends Command {
    
    private MazeShop $plugin;
    
    public function __construct(MazeShop $plugin) {
        parent::__construct("sell", "Sell items from your hand", "/sell <amount/invall>");
        $this->setPermission("mazeshop.command.sell");
        $this->plugin = $plugin;
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage($this->plugin->getPrefix() . $this->plugin->getMessage("player-only"));
            return false;
        }
        
        if (!$this->testPermission($sender)) {
            $sender->sendMessage($this->plugin->getPrefix() . $this->plugin->getMessage("no-permission"));
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
        
        $item = $sender->getInventory()->getItemInHand();
        
        if ($item->isNull() || $item->equals(VanillaItems::AIR(), true, false)) {
            $sender->sendMessage($this->plugin->getPrefix() . $this->plugin->getMessage("sell-hand-empty"));
            return false;
        }
        
        // Get sell price for this item
        $sellPrice = ItemUtils::getSellPrice($this->plugin, $item);
        
        if ($sellPrice <= 0) {
            $sender->sendMessage($this->plugin->getPrefix() . $this->plugin->getMessage("sell-not-sellable"));
            return false;
        }
        
        if (count($args) === 0) {
            $sender->sendMessage($this->plugin->getPrefix() . $this->plugin->getMessage("sell-usage"));
            return false;
        }
        
        $arg = strtolower($args[0]);
        
        if ($arg === "invall") {
            // Count all items of this type in inventory
            $totalCount = ItemUtils::countItemInInventory($sender->getInventory(), $item);
            
            if ($totalCount === 0) {
                $message = str_replace("{item}", $item->getName(), $this->plugin->getMessage("sell-no-item"));
                $sender->sendMessage($this->plugin->getPrefix() . $message);
                return false;
            }
            
            // Open confirmation form
            $sender->sendForm(new SellConfirmForm($this->plugin, $item, $totalCount, $sellPrice));
            return true;
        }
        
        // Sell specific amount
        if (!is_numeric($arg) || (int)$arg <= 0) {
            $sender->sendMessage($this->plugin->getPrefix() . $this->plugin->getMessage("sell-invalid-amount"));
            return false;
        }
        
        $amount = (int)$arg;
        $itemCount = $item->getCount();
        
        if ($amount > $itemCount) {
            $amount = $itemCount;
        }
        
        // Open confirmation form
        $sender->sendForm(new SellConfirmForm($this->plugin, $item, $amount, $sellPrice));
        
        return true;
    }
}