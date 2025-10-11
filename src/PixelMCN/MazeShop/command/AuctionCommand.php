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
use PixelMCN\MazeShop\Main;
use PixelMCN\MazeShop\gui\forms\AuctionFormGUI;

class AuctionCommand extends Command {

    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("auction", "Manage auctions", "/auction <list|bid|create|view> [args]");
        $this->setPermission("mazeshop.auction.use");
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
            // Open auction GUI
            $gui = new AuctionFormGUI($this->plugin);
            $gui->sendMainMenu($sender);
            return true;
        }

        $subCommand = strtolower($args[0]);

        switch ($subCommand) {
            case "list":
                $this->handleList($sender);
                break;

            case "bid":
                if (!isset($args[1], $args[2])) {
                    $sender->sendMessage("§cUsage: /auction bid <auctionID> <amount>");
                    return false;
                }
                $this->handleBid($sender, (int)$args[1], (float)$args[2]);
                break;

            case "create":
                if (!isset($args[1], $args[2])) {
                    $sender->sendMessage("§cUsage: /auction create <startingBid> <duration>");
                    return false;
                }
                $this->handleCreate($sender, (float)$args[1], (int)$args[2]);
                break;

            case "view":
                if (!isset($args[1])) {
                    $sender->sendMessage("§cUsage: /auction view <auctionID>");
                    return false;
                }
                $this->handleView($sender, (int)$args[1]);
                break;

            case "help":
                $this->sendHelp($sender);
                break;

            default:
                $sender->sendMessage("§cUnknown sub-command. Use /auction help for help.");
                break;
        }

        return true;
    }

    private function handleList(Player $player): void {
        $auctions = $this->plugin->getAuctionManager()->getAllAuctions();
        
        if (empty($auctions)) {
            $player->sendMessage($this->plugin->getMessage("auction.no-active-auctions"));
            return;
        }

        $player->sendMessage("§8§m-----------§r §6Active Auctions §8§m-----------");
        foreach ($auctions as $auction) {
            $currency = $this->plugin->getEconomyManager()->getCurrencySymbol();
            $player->sendMessage(
                "§e#{$auction->getId()} §7- §f{$auction->getItemName()} §7x{$auction->getAmount()} " .
                "§7- §a{$currency}{$auction->getCurrentBid()} §7- §e{$auction->getTimeRemainingFormatted()}"
            );
        }
    }

    private function handleBid(Player $player, int $auctionId, float $amount): void {
        $auction = $this->plugin->getAuctionManager()->getAuction($auctionId);
        
        if ($auction === null) {
            $player->sendMessage($this->plugin->getMessage("auction.auction-not-found", [
                "auction-id" => $auctionId
            ]));
            return;
        }

        if ($amount <= $auction->getCurrentBid()) {
            $player->sendMessage($this->plugin->getMessage("auction.bid-too-low", [
                "current-bid" => $auction->getCurrentBid()
            ]));
            return;
        }

        if ($this->plugin->getAuctionManager()->placeBid($player, $auctionId, $amount)) {
            $player->sendMessage($this->plugin->getMessage("auction.bid-success", [
                "amount" => $amount,
                "auction-id" => $auctionId,
                "currency" => $this->plugin->getEconomyManager()->getCurrencySymbol()
            ]));
        } else {
            $player->sendMessage($this->plugin->getMessage("auction.bid-failed"));
        }
    }

    private function handleCreate(Player $player, float $startingBid, int $duration): void {
        $config = $this->plugin->getConfig();
        $auctionConfig = $config->get("auction");
        
        if (!is_array($auctionConfig)) {
            $player->sendMessage("§cAuction configuration error!");
            return;
        }
        
        if ($duration < ($auctionConfig["min-duration"] ?? 300)) {
            $player->sendMessage("§cDuration must be at least " . ($auctionConfig["min-duration"] ?? 300) . " seconds!");
            return;
        }

        if ($duration > ($auctionConfig["max-duration"] ?? 86400)) {
            $player->sendMessage("§cDuration cannot exceed " . ($auctionConfig["max-duration"] ?? 86400) . " seconds!");
            return;
        }

        if ($startingBid < ($auctionConfig["min-starting-bid"] ?? 1)) {
            $player->sendMessage("§cStarting bid must be at least " . ($auctionConfig["min-starting-bid"] ?? 1) . "!");
            return;
        }

        $heldItem = $player->getInventory()->getItemInHand();
        
        if ($heldItem->isNull()) {
            $player->sendMessage($this->plugin->getMessage("admin.hold-item"));
            return;
        }

        $auction = $this->plugin->getAuctionManager()->createAuction(
            $player,
            (string)$heldItem->getTypeId(),
            0,
            $heldItem->getName(),
            $heldItem->getCount(),
            $startingBid,
            $duration
        );

        if ($auction !== null) {
            $player->getInventory()->setItemInHand($heldItem->setCount(0));
            $player->sendMessage($this->plugin->getMessage("auction.create-success", [
                "auction-id" => $auction->getId()
            ]));
        } else {
            $player->sendMessage($this->plugin->getMessage("auction.max-auctions-reached"));
        }
    }

    private function handleView(Player $player, int $auctionId): void {
        $auction = $this->plugin->getAuctionManager()->getAuction($auctionId);
        
        if ($auction === null) {
            $player->sendMessage($this->plugin->getMessage("auction.auction-not-found", [
                "auction-id" => $auctionId
            ]));
            return;
        }

        $currency = $this->plugin->getEconomyManager()->getCurrencySymbol();
        $player->sendMessage("§8§m-----------§r §6Auction #{$auctionId} §8§m-----------");
        $player->sendMessage("§7Item: §f{$auction->getItemName()} §7x{$auction->getAmount()}");
        $player->sendMessage("§7Seller: §f{$auction->getSeller()}");
        $player->sendMessage("§7Current Bid: §a{$currency}{$auction->getCurrentBid()}");
        $player->sendMessage("§7Current Bidder: §f" . ($auction->getCurrentBidder() ?? "None"));
        $player->sendMessage("§7Time Left: §e{$auction->getTimeRemainingFormatted()}");
    }

    private function sendHelp(Player $player): void {
        $player->sendMessage("§8§m-----------§r " . $this->plugin->getMessage("help.auction-title") . " §8§m-----------");
        foreach ($this->plugin->getMessage("help.auction-commands") as $command) {
            $player->sendMessage($command);
        }
    }
}
