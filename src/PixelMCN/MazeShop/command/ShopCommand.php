<?php

declare(strict_types=1);

namespace PixelMCN\MazeShop\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use PixelMCN\MazeShop\Main;
use PixelMCN\MazeShop\gui\forms\ShopFormGUI;
use PixelMCN\MazeShop\gui\chest\ShopChestGUI;

class ShopCommand extends Command {

    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("shop", "Open shop or execute shop commands", "/shop [category <name>|buy <item> <amount>|sell <item> <amount>|help]");
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
            // Open main shop GUI
            $guiType = $this->plugin->getConfig()->get("gui")["shop-type"] ?? "form";
            if ($guiType === "form") {
                $gui = new ShopFormGUI($this->plugin);
                $gui->sendMainMenu($sender);
            } else {
                $gui = new ShopChestGUI($this->plugin);
                $gui->sendMainMenu($sender);
            }
            return true;
        }

        $subCommand = strtolower($args[0]);

        switch ($subCommand) {
            case "category":
                if (!isset($args[1])) {
                    $sender->sendMessage("§cUsage: /shop category <name>");
                    return false;
                }
                $this->handleCategory($sender, $args[1]);
                break;

            case "buy":
                if (!isset($args[1], $args[2])) {
                    $sender->sendMessage("§cUsage: /shop buy <item> <amount>");
                    return false;
                }
                $this->handleBuy($sender, $args[1], (int)$args[2]);
                break;

            case "sell":
                if (!isset($args[1], $args[2])) {
                    $sender->sendMessage("§cUsage: /shop sell <item> <amount>");
                    return false;
                }
                $this->handleSell($sender, $args[1], (int)$args[2]);
                break;

            case "help":
                $this->sendHelp($sender);
                break;

            default:
                $sender->sendMessage("§cUnknown sub-command. Use /shop help for help.");
                break;
        }

        return true;
    }

    private function handleCategory(Player $player, string $categoryName): void {
        $category = $this->plugin->getShopManager()->getCategory($categoryName);
        if ($category === null) {
            $player->sendMessage($this->plugin->getMessage("shop.category-not-found", [
                "category" => $categoryName
            ]));
            return;
        }

        $gui = new ShopFormGUI($this->plugin);
        $gui->sendCategoryMenu($player, $category);
    }

    private function handleBuy(Player $player, string $itemName, int $amount): void {
        if ($amount <= 0) {
            $player->sendMessage($this->plugin->getMessage("general.invalid-amount"));
            return;
        }

        $itemData = $this->plugin->getShopManager()->searchItem($itemName);
        if ($itemData === null) {
            $player->sendMessage($this->plugin->getMessage("shop.item-not-found", [
                "item" => $itemName
            ]));
            return;
        }

        $item = $itemData["item"];
        $totalPrice = $item->getBuyPrice() * $amount;
        $economy = $this->plugin->getEconomyManager();

        if (!$economy->hasMoney($player, $totalPrice)) {
            $player->sendMessage($this->plugin->getMessage("general.insufficient-funds", [
                "price" => $totalPrice,
                "balance" => $economy->getBalance($player),
                "currency" => $economy->getCurrencySymbol()
            ]));
            return;
        }

        if (!$economy->reduceMoney($player, $totalPrice)) {
            $player->sendMessage($this->plugin->getMessage("shop.purchase-failed", [
                "item" => $item->getName()
            ]));
            return;
        }

        $player->sendMessage($this->plugin->getMessage("shop.purchase-success", [
            "amount" => $amount,
            "item" => $item->getName(),
            "price" => $totalPrice,
            "currency" => $economy->getCurrencySymbol()
        ]));
    }

    private function handleSell(Player $player, string $itemName, int $amount): void {
        if ($amount <= 0) {
            $player->sendMessage($this->plugin->getMessage("general.invalid-amount"));
            return;
        }

        $itemData = $this->plugin->getShopManager()->searchItem($itemName);
        if ($itemData === null) {
            $player->sendMessage($this->plugin->getMessage("shop.item-not-found", [
                "item" => $itemName
            ]));
            return;
        }

        $item = $itemData["item"];
        
        if (!$item->isSellable()) {
            $player->sendMessage($this->plugin->getMessage("shop.item-not-sellable"));
            return;
        }

        $totalPrice = $item->getSellPrice() * $amount;
        $economy = $this->plugin->getEconomyManager();
        $economy->addMoney($player, $totalPrice);

        $player->sendMessage($this->plugin->getMessage("shop.sell-success", [
            "amount" => $amount,
            "item" => $item->getName(),
            "price" => $totalPrice,
            "currency" => $economy->getCurrencySymbol()
        ]));
    }

    private function sendHelp(Player $player): void {
        $player->sendMessage("§8§m-----------§r " . $this->plugin->getMessage("help.shop-title") . " §8§m-----------");
        foreach ($this->plugin->getMessage("help.shop-commands") as $command) {
            $player->sendMessage($command);
        }
    }
}
