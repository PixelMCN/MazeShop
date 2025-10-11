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
