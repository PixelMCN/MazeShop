<?php

declare(strict_types=1);

namespace Pixelis0P\MazeShop\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use Pixelis0P\MazeShop\MazeShop;
use Pixelis0P\MazeShop\forms\admin\CategoryManageForm;
use Pixelis0P\MazeShop\forms\admin\ItemManageForm;

class ShopAdminCommand extends Command {
    
    private MazeShop $plugin;
    
    public function __construct(MazeShop $plugin) {
        parent::__construct("shopadmin", "Manage shop categories and items", "/shopadmin", ["sadmin"]);
        $this->setPermission("mazeshop.command.admin");
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
        
        // Open main admin menu
        $sender->sendForm(new CategoryManageForm($this->plugin));
        
        return true;
    }
}