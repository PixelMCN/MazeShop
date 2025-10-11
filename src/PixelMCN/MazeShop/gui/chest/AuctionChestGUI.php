<?php

declare(strict_types=1);

namespace PixelMCN\MazeShop\gui\chest;

use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use muqsit\invmenu\type\InvMenuTypeIds;
use PixelMCN\MazeShop\Main;
use PixelMCN\MazeShop\auction\Auction;


class AuctionChestGUI {

    private Main $plugin;
    private const BACK_SLOT = 49;
    private const CLOSE_SLOT = 53;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function sendMainMenu(Player $player): void {
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST);
        $menu->setName(TF::BOLD . TF::GOLD . "Auction House");
        
        $inventory = $menu->getInventory();
        
        // View All Auctions button
        $listItem = VanillaBlocks::GOLD()->asItem();
        $listItem->setCustomName(TF::YELLOW . TF::BOLD . "View All Auctions");
        $listItem->setLore([
            "",
            TF::GRAY . "Browse all active auctions",
            TF::GRAY . "and place your bids!",
            "",
            TF::YELLOW . "Click to view"
        ]);
        $inventory->setItem(11, $listItem);
        
        // Create Auction button
        $createItem = VanillaBlocks::EMERALD()->asItem();
        $createItem->setCustomName(TF::GREEN . TF::BOLD . "Create Auction");
        $createItem->setLore([
            "",
            TF::GRAY . "Sell your items to",
            TF::GRAY . "the highest bidder!",
            "",
            TF::YELLOW . "Click to create"
        ]);
        $inventory->setItem(13, $createItem);
        
        // My Auctions button
        $myAuctionsItem = VanillaBlocks::DIAMOND()->asItem();
        $myAuctionsItem->setCustomName(TF::AQUA . TF::BOLD . "My Auctions");
        $myAuctionsItem->setLore([
            "",
            TF::GRAY . "View your active auctions",
            TF::GRAY . "and current bids",
            "",
            TF::YELLOW . "Click to view"
        ]);
        $inventory->setItem(15, $myAuctionsItem);
        
        // Close button
        $closeItem = VanillaItems::BARRIER();
        $closeItem->setCustomName(TF::RED . TF::BOLD . "Close");
        $closeItem->setLore([TF::GRAY . "Click to close"]);
        $inventory->setItem(26, $closeItem);
        
        // Set click handler
        $menu->setListener(function(InvMenuTransaction $transaction): InvMenuTransactionResult {
            $player = $transaction->getPlayer();
            $slot = $transaction->getAction()->getSlot();
            
            switch ($slot) {
                case 11: // View auctions
                    $this->sendAuctionList($player);
                    break;
                case 13: // Create auction
                    $this->sendCreateAuctionMenu($player);
                    break;
                case 15: // My auctions
                    $this->sendMyAuctions($player);
                    break;
                case 26: // Close
                    $player->removeCurrentWindow();
                    break;
            }
            
            return $transaction->discard();
        });
        
        $menu->send($player);
    }

    public function sendAuctionList(Player $player, int $page = 0): void {
        $auctions = array_values($this->plugin->getAuctionManager()->getAllAuctions());
        
        if (empty($auctions)) {
            $player->sendMessage($this->plugin->getMessage("auction.no-active-auctions"));
            $this->sendMainMenu($player);
            return;
        }
        
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
        $menu->setName(TF::BOLD . TF::GOLD . "Active Auctions");
        
        $inventory = $menu->getInventory();
        
        // Add decorative borders
        $this->addBorders($inventory);
        
        // Pagination
        $perPage = 21;
        $maxPage = (int)ceil(count($auctions) / $perPage);
        $startIndex = $page * $perPage;
        $endIndex = min($startIndex + $perPage, count($auctions));
        
        // Add auctions
        $slot = 10;
        for ($i = $startIndex; $i < $endIndex; $i++) {
            if ($slot > 34) break;
            
            $auction = $auctions[$i];
            $item = $this->createAuctionItem($auction);
            $inventory->setItem($slot, $item);
            
            $slot++;
            if (in_array($slot, [17, 18, 26, 27])) {
                $slot += 2;
            }
        }
        
        // Navigation buttons
        if ($page > 0) {
            $prevItem = VanillaItems::ARROW();
            $prevItem->setCustomName(TF::YELLOW . "← Previous Page");
            $inventory->setItem(48, $prevItem);
        }
        
        if ($page < $maxPage - 1) {
            $nextItem = VanillaItems::ARROW();
            $nextItem->setCustomName(TF::YELLOW . "Next Page →");
            $inventory->setItem(50, $nextItem);
        }
        
        // Back button
        $backItem = VanillaBlocks::OAK_DOOR()->asItem();
        $backItem->setCustomName(TF::YELLOW . TF::BOLD . "← Back");
        $backItem->setLore([TF::GRAY . "Return to main menu"]);
        $inventory->setItem(self::BACK_SLOT, $backItem);
        
        // Close button
        $closeItem = VanillaItems::BARRIER();
        $closeItem->setCustomName(TF::RED . TF::BOLD . "Close");
        $inventory->setItem(self::CLOSE_SLOT, $closeItem);
        
        // Set click handler
        $menu->setListener(function(InvMenuTransaction $transaction) use ($auctions, $page, $maxPage): InvMenuTransactionResult {
            $player = $transaction->getPlayer();
            $slot = $transaction->getAction()->getSlot();
            $itemClicked = $transaction->getItemClicked();
            
            if ($slot === self::CLOSE_SLOT) {
                $player->removeCurrentWindow();
                return $transaction->discard();
            }
            
            if ($slot === self::BACK_SLOT) {
                $this->sendMainMenu($player);
                return $transaction->discard();
            }
            
            if ($slot === 48 && $page > 0) {
                $this->sendAuctionList($player, $page - 1);
                return $transaction->discard();
            }
            
            if ($slot === 50 && $page < $maxPage - 1) {
                $this->sendAuctionList($player, $page + 1);
                return $transaction->discard();
            }
            
            // Find clicked auction
            $auctionId = TF::clean(explode(" ", $itemClicked->getCustomName())[0]);
            $auctionId = (int)str_replace("#", "", $auctionId);
            
            $auction = $this->plugin->getAuctionManager()->getAuction($auctionId);
            if ($auction !== null) {
                $this->sendAuctionView($player, $auction);
            }
            
            return $transaction->discard();
        });
        
        $menu->send($player);
    }

    public function sendAuctionView(Player $player, Auction $auction): void {
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST);
        $menu->setName(TF::GOLD . "Auction #" . $auction->getId());
        
        $inventory = $menu->getInventory();
        $currency = $this->plugin->getEconomyManager()->getCurrencySymbol();
        
        // Display auction item
        $auctionItem = $this->createDetailedAuctionItem($auction);
        $inventory->setItem(13, $auctionItem);
        
        // Bid options
        $currentBid = $auction->getCurrentBid();
        
        $bid1 = VanillaItems::EMERALD();
        $bid1->setCustomName(TF::GREEN . "Bid +" . $currency . "10");
        $bid1->setLore([
            TF::GRAY . "Total: " . TF::WHITE . $currency . ($currentBid + 10),
            "",
            TF::YELLOW . "Click to bid"
        ]);
        $inventory->setItem(10, $bid1);
        
        $bid2 = VanillaItems::EMERALD();
        $bid2->setCustomName(TF::GREEN . "Bid +" . $currency . "50");
        $bid2->setLore([
            TF::GRAY . "Total: " . TF::WHITE . $currency . ($currentBid + 50),
            "",
            TF::YELLOW . "Click to bid"
        ]);
        $inventory->setItem(11, $bid2);
        
        $bid3 = VanillaItems::EMERALD();
        $bid3->setCustomName(TF::GREEN . "Bid +" . $currency . "100");
        $bid3->setLore([
            TF::GRAY . "Total: " . TF::WHITE . $currency . ($currentBid + 100),
            "",
            TF::YELLOW . "Click to bid"
        ]);
        $inventory->setItem(12, $bid3);
        
        $bid4 = VanillaItems::EMERALD();
        $bid4->setCustomName(TF::GREEN . "Bid +" . $currency . "500");
        $bid4->setLore([
            TF::GRAY . "Total: " . TF::WHITE . $currency . ($currentBid + 500),
            "",
            TF::YELLOW . "Click to bid"
        ]);
        $inventory->setItem(14, $bid4);
        
        // Back button
        $backItem = VanillaBlocks::OAK_DOOR()->asItem();
        $backItem->setCustomName(TF::YELLOW . TF::BOLD . "← Back");
        $inventory->setItem(22, $backItem);
        
        // Set click handler
        $menu->setListener(function(InvMenuTransaction $transaction) use ($auction, $currentBid): InvMenuTransactionResult {
            $player = $transaction->getPlayer();
            $slot = $transaction->getAction()->getSlot();
            $itemClicked = $transaction->getItemClicked();
            
            if ($slot === 22) {
                $this->sendAuctionList($player);
                return $transaction->discard();
            }
            
            $itemName = $itemClicked->getCustomName();
            if (str_contains($itemName, "Bid +")) {
                $bidAmount = (float)str_replace([TF::GREEN . "Bid +", $this->plugin->getEconomyManager()->getCurrencySymbol()], "", $itemName);
                $totalBid = $currentBid + $bidAmount;
                
                if ($this->plugin->getAuctionManager()->placeBid($player, $auction->getId(), $totalBid)) {
                    $player->sendMessage($this->plugin->getMessage("auction.bid-success", [
                        "amount" => $totalBid,
                        "auction-id" => $auction->getId(),
                        "currency" => $this->plugin->getEconomyManager()->getCurrencySymbol()
                    ]));
                    $player->removeCurrentWindow();
                } else {
                    $player->sendMessage($this->plugin->getMessage("auction.bid-failed"));
                }
            }
            
            return $transaction->discard();
        });
        
        $menu->send($player);
    }

    public function sendCreateAuctionMenu(Player $player): void {
        $player->removeCurrentWindow();
        $player->sendMessage(TF::YELLOW . "To create an auction:");
        $player->sendMessage(TF::GRAY . "1. Hold the item you want to auction");
        $player->sendMessage(TF::GRAY . "2. Use: " . TF::WHITE . "/auction create <startingBid> <duration>");
        $player->sendMessage(TF::GRAY . "Example: " . TF::WHITE . "/auction create 100 3600");
    }

    public function sendMyAuctions(Player $player): void {
        $auctions = array_values($this->plugin->getAuctionManager()->getPlayerAuctions($player->getName()));
        
        if (empty($auctions)) {
            $player->sendMessage(TF::RED . "You have no active auctions!");
            $this->sendMainMenu($player);
            return;
        }
        
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
        $menu->setName(TF::BOLD . TF::AQUA . "My Auctions");
        
        $inventory = $menu->getInventory();
        
        // Add decorative borders
        $this->addBorders($inventory);
        
        // Add auctions
        $slot = 10;
        foreach ($auctions as $auction) {
            if ($slot > 34) break;
            
            $item = $this->createAuctionItem($auction);
            $inventory->setItem($slot, $item);
            
            $slot++;
            if (in_array($slot, [17, 18, 26, 27])) {
                $slot += 2;
            }
        }
        
        // Back button
        $backItem = VanillaBlocks::OAK_DOOR()->asItem();
        $backItem->setCustomName(TF::YELLOW . TF::BOLD . "← Back");
        $inventory->setItem(self::BACK_SLOT, $backItem);
        
        // Close button
        $closeItem = VanillaItems::BARRIER();
        $closeItem->setCustomName(TF::RED . TF::BOLD . "Close");
        $inventory->setItem(self::CLOSE_SLOT, $closeItem);
        
        // Set click handler
        $menu->setListener(function(InvMenuTransaction $transaction) use ($auctions): InvMenuTransactionResult {
            $player = $transaction->getPlayer();
            $slot = $transaction->getAction()->getSlot();
            $itemClicked = $transaction->getItemClicked();
            
            if ($slot === self::CLOSE_SLOT) {
                $player->removeCurrentWindow();
                return $transaction->discard();
            }
            
            if ($slot === self::BACK_SLOT) {
                $this->sendMainMenu($player);
                return $transaction->discard();
            }
            
            // Find clicked auction
            $auctionId = TF::clean(explode(" ", $itemClicked->getCustomName())[0]);
            $auctionId = (int)str_replace("#", "", $auctionId);
            
            $auction = $this->plugin->getAuctionManager()->getAuction($auctionId);
            if ($auction !== null) {
                $this->sendAuctionView($player, $auction);
            }
            
            return $transaction->discard();
        });
        
        $menu->send($player);
    }

    private function createAuctionItem(Auction $auction): Item {
        $item = StringToItemParser::getInstance()->parse($auction->getItemId());
        if ($item === null) {
            $item = VanillaItems::PAPER();
        }
        
        $currency = $this->plugin->getEconomyManager()->getCurrencySymbol();
        $item->setCustomName(TF::GOLD . "#" . $auction->getId() . " " . TF::YELLOW . $auction->getItemName());
        $item->setLore([
            "",
            TF::GRAY . "Seller: " . TF::WHITE . $auction->getSeller(),
            TF::GRAY . "Amount: " . TF::WHITE . "x" . $auction->getAmount(),
            TF::GRAY . "Current Bid: " . TF::GREEN . $currency . $auction->getCurrentBid(),
            TF::GRAY . "Time Left: " . TF::YELLOW . $auction->getTimeRemainingFormatted(),
            "",
            TF::YELLOW . "Click to view and bid!"
        ]);
        
        return $item;
    }

    private function createDetailedAuctionItem(Auction $auction): Item {
        $item = StringToItemParser::getInstance()->parse($auction->getItemId());
        if ($item === null) {
            $item = VanillaItems::PAPER();
        }
        
        $currency = $this->plugin->getEconomyManager()->getCurrencySymbol();
        $item->setCustomName(TF::GOLD . TF::BOLD . $auction->getItemName());
        $item->setLore([
            "",
            TF::GRAY . "Auction ID: " . TF::WHITE . "#" . $auction->getId(),
            TF::GRAY . "Seller: " . TF::WHITE . $auction->getSeller(),
            TF::GRAY . "Amount: " . TF::WHITE . "x" . $auction->getAmount(),
            "",
            TF::GREEN . TF::BOLD . "Current Bid:",
            TF::GRAY . "  Amount: " . TF::WHITE . $currency . $auction->getCurrentBid(),
            TF::GRAY . "  Bidder: " . TF::WHITE . ($auction->getCurrentBidder() ?? "None"),
            "",
            TF::YELLOW . "Time Remaining: " . TF::WHITE . $auction->getTimeRemainingFormatted(),
            "",
            TF::AQUA . "Choose a bid amount below!"
        ]);
        
        return $item;
    }

    private function addBorders(Inventory $inventory): void {
        $borderItem = VanillaItems::ORANGE_DYE();
        $borderItem->setCustomName(TF::RESET);
        
        // Top and bottom rows
        for ($i = 0; $i < 9; $i++) {
            $inventory->setItem($i, $borderItem);
            $inventory->setItem($i + 45, $borderItem);
        }
        
        // Left and right columns
        for ($i = 1; $i < 5; $i++) {
            $inventory->setItem($i * 9, $borderItem);
            $inventory->setItem($i * 9 + 8, $borderItem);
        }
    }
}
