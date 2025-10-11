<?php

declare(strict_types=1);

namespace PixelMCN\MazeShop\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use PixelMCN\MazeShop\Main;

class AuctionAdminCommand extends Command {

    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("auctionadmin", "Auction administration commands", "/auctionadmin <remove|end> <auctionID>");
        $this->setPermission("mazeshop.auction.admin");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$this->testPermission($sender)) {
            return false;
        }

        if (!isset($args[0], $args[1])) {
            $sender->sendMessage("§cUsage: /auctionadmin <remove|end> <auctionID>");
            return false;
        }

        $subCommand = strtolower($args[0]);
        $auctionId = (int)$args[1];

        switch ($subCommand) {
            case "remove":
                $this->handleRemove($sender, $auctionId);
                break;

            case "end":
                $this->handleEnd($sender, $auctionId);
                break;

            default:
                $sender->sendMessage("§cUnknown sub-command. Use remove or end.");
                break;
        }

        return true;
    }

    private function handleRemove(CommandSender $sender, int $auctionId): void {
        if ($this->plugin->getAuctionManager()->removeAuction($auctionId)) {
            $sender->sendMessage($this->plugin->getMessage("auction.auction-removed", [
                "auction-id" => $auctionId
            ]));
        } else {
            $sender->sendMessage($this->plugin->getMessage("auction.auction-not-found", [
                "auction-id" => $auctionId
            ]));
        }
    }

    private function handleEnd(CommandSender $sender, int $auctionId): void {
        if ($this->plugin->getAuctionManager()->endAuction($auctionId, true)) {
            $sender->sendMessage($this->plugin->getMessage("auction.auction-ended-admin", [
                "auction-id" => $auctionId
            ]));
        } else {
            $sender->sendMessage($this->plugin->getMessage("auction.auction-not-found", [
                "auction-id" => $auctionId
            ]));
        }
    }
}
