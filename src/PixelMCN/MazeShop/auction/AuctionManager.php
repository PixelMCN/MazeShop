<?php

declare(strict_types=1);

namespace PixelMCN\MazeShop\auction;

use pocketmine\player\Player;
use pocketmine\utils\Config;
use PixelMCN\MazeShop\Main;
use PixelMCN\MazeShop\event\AuctionCreateEvent;
use PixelMCN\MazeShop\event\AuctionBidEvent;
use PixelMCN\MazeShop\event\AuctionEndEvent;

class AuctionManager {

    private Main $plugin;
    private array $auctions = [];
    private int $nextAuctionId = 1;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
        $this->loadAuctions();
        $this->startAuctionCheckTask();
    }

    private function loadAuctions(): void {
        $auctionFile = $this->plugin->getDataFolder() . "auctions.json";
        if (file_exists($auctionFile)) {
            $data = json_decode(file_get_contents($auctionFile), true);
            if (is_array($data)) {
                $this->nextAuctionId = $data["next_id"] ?? 1;
                foreach ($data["auctions"] ?? [] as $auctionData) {
                    $auction = Auction::fromArray($auctionData);
                    if ($auction !== null && !$auction->isExpired()) {
                        $this->auctions[$auction->getId()] = $auction;
                    }
                }
            }
        }
    }

    public function saveAllAuctions(): void {
        $data = [
            "next_id" => $this->nextAuctionId,
            "auctions" => []
        ];

        foreach ($this->auctions as $auction) {
            $data["auctions"][] = $auction->toArray();
        }

        $auctionFile = $this->plugin->getDataFolder() . "auctions.json";
        file_put_contents($auctionFile, json_encode($data, JSON_PRETTY_PRINT));
    }

    private function startAuctionCheckTask(): void {
        $this->plugin->getScheduler()->scheduleRepeatingTask(
            new AuctionCheckTask($this->plugin),
            20 * 10 // Check every 10 seconds
        );
    }

    public function createAuction(
        Player $seller,
        string $itemId,
        int $meta,
        string $itemName,
        int $amount,
        float $startingBid,
        int $duration
    ): ?Auction {
        $config = $this->plugin->getConfig();
        $maxAuctions = $config->get("auction")["max-auctions-per-player"] ?? 5;
        
        $playerAuctions = $this->getPlayerAuctions($seller->getName());
        if (count($playerAuctions) >= $maxAuctions) {
            return null;
        }

        $auction = new Auction(
            $this->nextAuctionId++,
            $seller->getName(),
            $itemId,
            $meta,
            $itemName,
            $amount,
            $startingBid,
            time() + $duration
        );

        $event = new AuctionCreateEvent($auction, $seller);
        $event->call();

        if (!$event->isCancelled()) {
            $this->auctions[$auction->getId()] = $auction;
            $this->saveAllAuctions();
            return $auction;
        }

        return null;
    }

    public function placeBid(Player $bidder, int $auctionId, float $amount): bool {
        $auction = $this->getAuction($auctionId);
        if ($auction === null || $auction->isExpired()) {
            return false;
        }

        if ($auction->getSeller() === $bidder->getName()) {
            return false;
        }

        if ($amount <= $auction->getCurrentBid()) {
            return false;
        }

        $economy = $this->plugin->getEconomyManager();
        if (!$economy->hasMoney($bidder, $amount)) {
            return false;
        }

        // Refund previous bidder
        $previousBidder = $auction->getCurrentBidder();
        if ($previousBidder !== null) {
            $player = $this->plugin->getServer()->getPlayerExact($previousBidder);
            if ($player !== null) {
                $economy->addMoney($player, $auction->getCurrentBid());
                $player->sendMessage($this->plugin->getMessage("auction.auction-outbid", [
                    "auction-id" => $auctionId
                ]));
            }
        }

        // Charge new bidder
        if (!$economy->reduceMoney($bidder, $amount)) {
            return false;
        }

        $event = new AuctionBidEvent($auction, $bidder, $amount);
        $event->call();

        if (!$event->isCancelled()) {
            $auction->placeBid($bidder->getName(), $amount);
            $this->saveAllAuctions();
            return true;
        }

        // Refund if event cancelled
        $economy->addMoney($bidder, $amount);
        return false;
    }

    public function endAuction(int $auctionId, bool $force = false): bool {
        $auction = $this->getAuction($auctionId);
        if ($auction === null) {
            return false;
        }

        if (!$force && !$auction->isExpired()) {
            return false;
        }

        $winner = $auction->getCurrentBidder();
        $seller = $auction->getSeller();

        $event = new AuctionEndEvent($auction, $winner);
        $event->call();

        if ($event->isCancelled() && !$force) {
            return false;
        }

        // Process auction end
        if ($winner !== null) {
            // Give money to seller
            $sellerPlayer = $this->plugin->getServer()->getPlayerExact($seller);
            if ($sellerPlayer !== null) {
                $fee = $auction->getCurrentBid() * ($this->plugin->getConfig()->get("auction")["auction-fee"] ?? 0) / 100;
                $finalAmount = $auction->getCurrentBid() - $fee;
                $this->plugin->getEconomyManager()->addMoney($sellerPlayer, $finalAmount);
            }

            // Notify winner
            $winnerPlayer = $this->plugin->getServer()->getPlayerExact($winner);
            if ($winnerPlayer !== null) {
                $winnerPlayer->sendMessage($this->plugin->getMessage("auction.auction-won", [
                    "auction-id" => $auctionId,
                    "amount" => $auction->getCurrentBid()
                ]));
                
                // Give item to winner (would need item storage system)
                // For now, we'll store it for manual collection
            }
        } else {
            // No bids, return item to seller
            $sellerPlayer = $this->plugin->getServer()->getPlayerExact($seller);
            if ($sellerPlayer !== null) {
                $sellerPlayer->sendMessage($this->plugin->getMessage("auction.auction-ended", [
                    "auction-id" => $auctionId,
                    "winner" => "No bids"
                ]));
            }
        }

        unset($this->auctions[$auctionId]);
        $this->saveAllAuctions();
        return true;
    }

    public function checkExpiredAuctions(): void {
        foreach ($this->auctions as $auctionId => $auction) {
            if ($auction->isExpired()) {
                $this->endAuction($auctionId);
            }
        }
    }

    public function getAuction(int $id): ?Auction {
        return $this->auctions[$id] ?? null;
    }

    public function getAllAuctions(): array {
        return array_filter($this->auctions, fn($auction) => !$auction->isExpired());
    }

    public function getPlayerAuctions(string $playerName): array {
        return array_filter($this->auctions, fn($auction) => 
            $auction->getSeller() === $playerName && !$auction->isExpired()
        );
    }

    public function removeAuction(int $auctionId): bool {
        $auction = $this->getAuction($auctionId);
        if ($auction === null) {
            return false;
        }

        // Refund current bidder if any
        $bidder = $auction->getCurrentBidder();
        if ($bidder !== null) {
            $player = $this->plugin->getServer()->getPlayerExact($bidder);
            if ($player !== null) {
                $this->plugin->getEconomyManager()->addMoney($player, $auction->getCurrentBid());
            }
        }

        unset($this->auctions[$auctionId]);
        $this->saveAllAuctions();
        return true;
    }
}
