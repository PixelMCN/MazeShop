<?php

declare(strict_types=1);

namespace PixelMCN\MazeShop\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use PixelMCN\MazeShop\Main;
use PixelMCN\MazeShop\shop\ShopItem;

class ShopAdminCommand extends Command {

    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("shopadmin", "Shop administration commands", "/shopadmin <addcategory|removecategory|addsubcategory|removesubcategory|additem|removeitem|edititem|reload|help>");
        $this->setPermission("mazeshop.admin");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$this->testPermission($sender)) {
            return false;
        }

        if (empty($args)) {
            $sender->sendMessage("§cUsage: /shopadmin <addcategory|removecategory|addsubcategory|removesubcategory|additem|removeitem|edititem|reload|help>");
            return false;
        }

        $subCommand = strtolower($args[0]);

        switch ($subCommand) {
            case "addcategory":
                if (!isset($args[1])) {
                    $sender->sendMessage("§cUsage: /shopadmin addcategory <name>");
                    return false;
                }
                $this->handleAddCategory($sender, $args[1]);
                break;

            case "removecategory":
                if (!isset($args[1])) {
                    $sender->sendMessage("§cUsage: /shopadmin removecategory <name>");
                    return false;
                }
                $this->handleRemoveCategory($sender, $args[1]);
                break;

            case "addsubcategory":
                if (!isset($args[1], $args[2])) {
                    $sender->sendMessage("§cUsage: /shopadmin addsubcategory <category> <name>");
                    return false;
                }
                $this->handleAddSubCategory($sender, $args[1], $args[2]);
                break;

            case "removesubcategory":
                if (!isset($args[1], $args[2])) {
                    $sender->sendMessage("§cUsage: /shopadmin removesubcategory <category> <name>");
                    return false;
                }
                $this->handleRemoveSubCategory($sender, $args[1], $args[2]);
                break;

            case "additem":
                if (!isset($args[1], $args[2], $args[3])) {
                    $sender->sendMessage("§cUsage: /shopadmin additem <category> <subcategory> <price> [sellprice]");
                    return false;
                }
                if (!$sender instanceof Player) {
                    $sender->sendMessage($this->plugin->getMessage("general.player-only"));
                    return false;
                }
                $this->handleAddItem($sender, $args[1], $args[2], (float)$args[3], (float)($args[4] ?? 0));
                break;

            case "removeitem":
                if (!isset($args[1], $args[2], $args[3])) {
                    $sender->sendMessage("§cUsage: /shopadmin removeitem <category> <subcategory> <item>");
                    return false;
                }
                $this->handleRemoveItem($sender, $args[1], $args[2], $args[3]);
                break;

            case "edititem":
                if (!isset($args[1], $args[2], $args[3])) {
                    $sender->sendMessage("§cUsage: /shopadmin edititem <category> <subcategory> <item>");
                    return false;
                }
                $sender->sendMessage("§eItem editing via GUI is not yet implemented. Please edit shop.yml manually.");
                break;

            case "reload":
                $this->handleReload($sender);
                break;

            case "help":
                $this->sendHelp($sender);
                break;

            default:
                $sender->sendMessage("§cUnknown sub-command. Use /shopadmin help for help.");
                break;
        }

        return true;
    }

    private function handleAddCategory(CommandSender $sender, string $name): void {
        if ($this->plugin->getShopManager()->addCategory($name, "§a" . $name, "minecraft:chest")) {
            $sender->sendMessage($this->plugin->getMessage("admin.category-added", [
                "category" => $name
            ]));
        } else {
            $sender->sendMessage($this->plugin->getMessage("admin.category-already-exists", [
                "category" => $name
            ]));
        }
    }

    private function handleRemoveCategory(CommandSender $sender, string $name): void {
        if ($this->plugin->getShopManager()->removeCategory($name)) {
            $sender->sendMessage($this->plugin->getMessage("admin.category-removed", [
                "category" => $name
            ]));
        } else {
            $sender->sendMessage($this->plugin->getMessage("shop.category-not-found", [
                "category" => $name
            ]));
        }
    }

    private function handleAddSubCategory(CommandSender $sender, string $categoryName, string $subName): void {
        if ($this->plugin->getShopManager()->addSubCategory($categoryName, $subName, "§e" . $subName, "minecraft:chest")) {
            $sender->sendMessage($this->plugin->getMessage("admin.subcategory-added", [
                "subcategory" => $subName,
                "category" => $categoryName
            ]));
        } else {
            $sender->sendMessage($this->plugin->getMessage("admin.subcategory-already-exists", [
                "subcategory" => $subName,
                "category" => $categoryName
            ]));
        }
    }

    private function handleRemoveSubCategory(CommandSender $sender, string $categoryName, string $subName): void {
        if ($this->plugin->getShopManager()->removeSubCategory($categoryName, $subName)) {
            $sender->sendMessage($this->plugin->getMessage("admin.subcategory-removed", [
                "subcategory" => $subName,
                "category" => $categoryName
            ]));
        } else {
            $sender->sendMessage($this->plugin->getMessage("shop.subcategory-not-found", [
                "subcategory" => $subName
            ]));
        }
    }

    private function handleAddItem(Player $sender, string $categoryName, string $subName, float $buyPrice, float $sellPrice): void {
        $heldItem = $sender->getInventory()->getItemInHand();
        
        if ($heldItem->isNull()) {
            $sender->sendMessage($this->plugin->getMessage("admin.hold-item"));
            return;
        }

        if ($buyPrice <= 0) {
            $sender->sendMessage($this->plugin->getMessage("admin.invalid-price"));
            return;
        }

        $shopItem = new ShopItem(
            $heldItem->getTypeId(),
            0,
            $heldItem->getName(),
            "Added via command",
            $buyPrice,
            $sellPrice,
            1
        );

        if ($this->plugin->getShopManager()->addItem($categoryName, $subName, $shopItem)) {
            $sender->sendMessage($this->plugin->getMessage("admin.item-added", [
                "item" => $heldItem->getName(),
                "category" => $categoryName,
                "subcategory" => $subName
            ]));
        } else {
            $sender->sendMessage("§cFailed to add item. Check if category and subcategory exist.");
        }
    }

    private function handleRemoveItem(CommandSender $sender, string $categoryName, string $subName, string $itemId): void {
        if ($this->plugin->getShopManager()->removeItem($categoryName, $subName, $itemId)) {
            $sender->sendMessage($this->plugin->getMessage("admin.item-removed", [
                "item" => $itemId,
                "category" => $categoryName,
                "subcategory" => $subName
            ]));
        } else {
            $sender->sendMessage($this->plugin->getMessage("shop.item-not-found", [
                "item" => $itemId
            ]));
        }
    }

    private function handleReload(CommandSender $sender): void {
        $this->plugin->reloadConfigs();
        $sender->sendMessage($this->plugin->getMessage("general.config-reloaded"));
    }

    private function sendHelp(CommandSender $sender): void {
        $sender->sendMessage("§8§m-----------§r " . $this->plugin->getMessage("help.admin-title") . " §8§m-----------");
        foreach ($this->plugin->getMessage("help.admin-commands") as $command) {
            $sender->sendMessage($command);
        }
    }
}
