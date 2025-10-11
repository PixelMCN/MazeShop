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
use PixelMCN\MazeShop\shop\Category;
use PixelMCN\MazeShop\shop\SubCategory;
use PixelMCN\MazeShop\shop\ShopItem;
use PixelMCN\MazeShop\event\ItemPurchaseEvent;
use PixelMCN\MazeShop\event\ItemSellEvent;


class ShopChestGUI {

    private Main $plugin;
    private const BACK_SLOT = 49;
    private const CLOSE_SLOT = 53;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function sendMainMenu(Player $player): void {
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
        $menu->setName(TF::BOLD . TF::AQUA . "MazeShop");
        
        $inventory = $menu->getInventory();
        
        // Add decorative borders
        $this->addBorders($inventory);
        
        // Add categories
        $slot = 10;
        $categories = $this->plugin->getShopManager()->getCategories();
        
        foreach ($categories as $category) {
            if ($slot > 34) break;
            
            $item = $this->createCategoryItem($category);
            $inventory->setItem($slot, $item);
            
            $slot++;
            if (in_array($slot, [17, 18, 26, 27])) {
                $slot += 2;
            }
        }
        
        // Add close button
        $closeItem = VanillaItems::BARRIER();
        $closeItem->setCustomName(TF::RED . TF::BOLD . "Close");
        $closeItem->setLore([TF::GRAY . "Click to close"]);
        $inventory->setItem(self::CLOSE_SLOT, $closeItem);
        
        // Set click handler
        $menu->setListener(function(InvMenuTransaction $transaction) use ($categories): InvMenuTransactionResult {
            $player = $transaction->getPlayer();
            $slot = $transaction->getAction()->getSlot();
            $itemClicked = $transaction->getItemClicked();
            
            if ($slot === self::CLOSE_SLOT) {
                $player->removeCurrentWindow();
                return $transaction->discard();
            }
            
            // Find which category was clicked
            $categoryName = TF::clean($itemClicked->getCustomName());
            foreach ($categories as $category) {
                if (TF::clean($category->getDisplayName()) === $categoryName) {
                    $this->sendCategoryMenu($player, $category);
                    return $transaction->discard();
                }
            }
            
            return $transaction->discard();
        });
        
        $menu->send($player);
    }

    public function sendCategoryMenu(Player $player, Category $category): void {
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
        $menu->setName($category->getDisplayName());
        
        $inventory = $menu->getInventory();
        
        // Add decorative borders
        $this->addBorders($inventory);
        
        // Add sub-categories
        $slot = 10;
        $subCategories = $category->getSubCategories();
        
        foreach ($subCategories as $subCategory) {
            if ($slot > 34) break;
            
            $item = $this->createSubCategoryItem($subCategory);
            $inventory->setItem($slot, $item);
            
            $slot++;
            if (in_array($slot, [17, 18, 26, 27])) {
                $slot += 2;
            }
        }
        
        // Add back button
        $backItem = VanillaBlocks::OAK_DOOR()->asItem();
        $backItem->setCustomName(TF::YELLOW . TF::BOLD . "← Back");
        $backItem->setLore([TF::GRAY . "Return to main menu"]);
        $inventory->setItem(self::BACK_SLOT, $backItem);
        
        // Add close button
        $closeItem = VanillaItems::BARRIER();
        $closeItem->setCustomName(TF::RED . TF::BOLD . "Close");
        $closeItem->setLore([TF::GRAY . "Click to close"]);
        $inventory->setItem(self::CLOSE_SLOT, $closeItem);
        
        // Set click handler
        $menu->setListener(function(InvMenuTransaction $transaction) use ($category, $subCategories): InvMenuTransactionResult {
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
            
            // Find which sub-category was clicked
            $subCategoryName = TF::clean(explode("\n", $itemClicked->getCustomName())[0]);
            foreach ($subCategories as $subCategory) {
                if (TF::clean($subCategory->getDisplayName()) === $subCategoryName) {
                    $this->sendSubCategoryMenu($player, $category, $subCategory);
                    return $transaction->discard();
                }
            }
            
            return $transaction->discard();
        });
        
        $menu->send($player);
    }

    public function sendSubCategoryMenu(Player $player, Category $category, SubCategory $subCategory): void {
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
        $menu->setName($subCategory->getDisplayName());
        
        $inventory = $menu->getInventory();
        
        // Add decorative borders
        $this->addBorders($inventory);
        
        // Add items
        $slot = 10;
        $items = $subCategory->getItems();
        
        foreach ($items as $shopItem) {
            if ($slot > 34) break;
            
            $item = $this->createShopItemDisplay($shopItem);
            $inventory->setItem($slot, $item);
            
            $slot++;
            if (in_array($slot, [17, 18, 26, 27])) {
                $slot += 2;
            }
        }
        
        // Add back button
        $backItem = VanillaBlocks::OAK_DOOR()->asItem();
        $backItem->setCustomName(TF::YELLOW . TF::BOLD . "← Back");
        $backItem->setLore([TF::GRAY . "Return to categories"]);
        $inventory->setItem(self::BACK_SLOT, $backItem);
        
        // Add close button
        $closeItem = VanillaItems::BARRIER();
        $closeItem->setCustomName(TF::RED . TF::BOLD . "Close");
        $closeItem->setLore([TF::GRAY . "Click to close"]);
        $inventory->setItem(self::CLOSE_SLOT, $closeItem);
        
        // Set click handler
        $menu->setListener(function(InvMenuTransaction $transaction) use ($category, $subCategory, $items): InvMenuTransactionResult {
            $player = $transaction->getPlayer();
            $slot = $transaction->getAction()->getSlot();
            $itemClicked = $transaction->getItemClicked();
            
            if ($slot === self::CLOSE_SLOT) {
                $player->removeCurrentWindow();
                return $transaction->discard();
            }
            
            if ($slot === self::BACK_SLOT) {
                $this->sendCategoryMenu($player, $category);
                return $transaction->discard();
            }
            
            // Find which item was clicked
            $itemName = TF::clean(explode("\n", $itemClicked->getCustomName())[0]);
            foreach ($items as $shopItem) {
                if (TF::clean($shopItem->getName()) === $itemName) {
                    $this->sendItemActionMenu($player, $category, $subCategory, $shopItem);
                    return $transaction->discard();
                }
            }
            
            return $transaction->discard();
        });
        
        $menu->send($player);
    }

    public function sendItemActionMenu(Player $player, Category $category, SubCategory $subCategory, ShopItem $shopItem): void {
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST);
        $menu->setName($shopItem->getName() . TF::RESET . TF::GRAY . " - Actions");
        
        $inventory = $menu->getInventory();
        $currency = $this->plugin->getEconomyManager()->getCurrencySymbol();
        
        // Display item info
        $infoItem = $this->createShopItemDisplay($shopItem);
        $inventory->setItem(13, $infoItem);
        
        // Buy options - Emeralds for buy
        $buy1 = VanillaItems::EMERALD();
        $buy1->setCustomName(TF::GREEN . TF::BOLD . "Buy x1");
        $buy1->setLore([
            TF::GRAY . "Price: " . TF::WHITE . $currency . $shopItem->getBuyPrice(),
            "",
            TF::YELLOW . "Click to purchase"
        ]);
        $inventory->setItem(10, $buy1);
        
        $buy16 = VanillaItems::EMERALD();
        $buy16->setCustomName(TF::GREEN . TF::BOLD . "Buy x16");
        $buy16->setLore([
            TF::GRAY . "Price: " . TF::WHITE . $currency . ($shopItem->getBuyPrice() * 16),
            "",
            TF::YELLOW . "Click to purchase"
        ]);
        $inventory->setItem(11, $buy16);
        
        $buy64 = VanillaItems::EMERALD();
        $buy64->setCustomName(TF::GREEN . TF::BOLD . "Buy x64");
        $buy64->setLore([
            TF::GRAY . "Price: " . TF::WHITE . $currency . ($shopItem->getBuyPrice() * 64),
            "",
            TF::YELLOW . "Click to purchase"
        ]);
        $inventory->setItem(12, $buy64);
        
        // Sell options - Redstone for sell
        if ($shopItem->isSellable()) {
            $sell1 = VanillaItems::REDSTONE_DUST();
            $sell1->setCustomName(TF::RED . TF::BOLD . "Sell x1");
            $sell1->setLore([
                TF::GRAY . "Price: " . TF::WHITE . $currency . $shopItem->getSellPrice(),
                "",
                TF::YELLOW . "Click to sell"
            ]);
            $inventory->setItem(14, $sell1);
            
            $sell16 = VanillaItems::REDSTONE_DUST();
            $sell16->setCustomName(TF::RED . TF::BOLD . "Sell x16");
            $sell16->setLore([
                TF::GRAY . "Price: " . TF::WHITE . $currency . ($shopItem->getSellPrice() * 16),
                "",
                TF::YELLOW . "Click to sell"
            ]);
            $inventory->setItem(15, $sell16);
            
            $sell64 = VanillaItems::REDSTONE_DUST();
            $sell64->setCustomName(TF::RED . TF::BOLD . "Sell x64");
            $sell64->setLore([
                TF::GRAY . "Price: " . TF::WHITE . $currency . ($shopItem->getSellPrice() * 64),
                "",
                TF::YELLOW . "Click to sell"
            ]);
            $inventory->setItem(16, $sell64);
        }
        
        // Back button
        $backItem = VanillaBlocks::OAK_DOOR()->asItem();
        $backItem->setCustomName(TF::YELLOW . TF::BOLD . "← Back");
        $backItem->setLore([TF::GRAY . "Return to items"]);
        $inventory->setItem(22, $backItem);
        
        // Set click handler
        $menu->setListener(function(InvMenuTransaction $transaction) use ($category, $subCategory, $shopItem): InvMenuTransactionResult {
            $player = $transaction->getPlayer();
            $slot = $transaction->getAction()->getSlot();
            $itemClicked = $transaction->getItemClicked();
            $itemName = $itemClicked->getCustomName();
            
            if ($slot === 22) { // Back
                $this->sendSubCategoryMenu($player, $category, $subCategory);
                return $transaction->discard();
            }
            
            // Handle buy/sell
            if (str_contains($itemName, "Buy x")) {
                $amount = (int)str_replace([TF::GREEN . TF::BOLD . "Buy x"], "", $itemName);
                $this->handleBuy($player, $shopItem, $amount);
            } elseif (str_contains($itemName, "Sell x")) {
                $amount = (int)str_replace([TF::RED . TF::BOLD . "Sell x"], "", $itemName);
                $this->handleSell($player, $shopItem, $amount);
            }
            
            return $transaction->discard();
        });
        
        $menu->send($player);
    }

    private function handleBuy(Player $player, ShopItem $item, int $amount): void {
        $totalPrice = $item->getBuyPrice() * $amount;
        $economy = $this->plugin->getEconomyManager();

        if (!$economy->hasMoney($player, $totalPrice)) {
            $player->sendMessage($this->plugin->getMessage("general.insufficient-funds", [
                "price" => $totalPrice,
                "balance" => $economy->getBalance($player),
                "currency" => $economy->getCurrencySymbol()
            ]));
            $player->removeCurrentWindow();
            return;
        }

        $event = new ItemPurchaseEvent($player, $item, $amount, $totalPrice);
        $event->call();

        if ($event->isCancelled()) {
            return;
        }

        if (!$economy->reduceMoney($player, $totalPrice)) {
            $player->sendMessage($this->plugin->getMessage("shop.purchase-failed", [
                "item" => $item->getName()
            ]));
            return;
        }

        $itemInstance = StringToItemParser::getInstance()->parse($item->getId());
        if ($itemInstance === null) {
            $itemInstance = VanillaItems::AIR();
        }
        $itemInstance->setCount($amount);

        if (!$player->getInventory()->canAddItem($itemInstance)) {
            $economy->addMoney($player, $totalPrice);
            $player->sendMessage($this->plugin->getMessage("shop.inventory-full"));
            $player->removeCurrentWindow();
            return;
        }

        $player->getInventory()->addItem($itemInstance);
        $player->sendMessage($this->plugin->getMessage("shop.purchase-success", [
            "amount" => $amount,
            "item" => $item->getName(),
            "price" => $totalPrice,
            "currency" => $economy->getCurrencySymbol()
        ]));
        $player->removeCurrentWindow();
    }

    private function handleSell(Player $player, ShopItem $item, int $amount): void {
        if (!$item->isSellable()) {
            $player->sendMessage($this->plugin->getMessage("shop.item-not-sellable"));
            $player->removeCurrentWindow();
            return;
        }

        $totalPrice = $item->getSellPrice() * $amount;

        $itemInstance = StringToItemParser::getInstance()->parse($item->getId());
        if ($itemInstance === null) {
            $player->sendMessage($this->plugin->getMessage("shop.sell-failed", [
                "item" => $item->getName()
            ]));
            $player->removeCurrentWindow();
            return;
        }
        $itemInstance->setCount($amount);

        if (!$player->getInventory()->contains($itemInstance)) {
            $player->sendMessage($this->plugin->getMessage("shop.not-enough-items", [
                "item" => $item->getName()
            ]));
            $player->removeCurrentWindow();
            return;
        }

        $event = new ItemSellEvent($player, $item, $amount, $totalPrice);
        $event->call();

        if ($event->isCancelled()) {
            return;
        }

        $player->getInventory()->removeItem($itemInstance);
        $economy = $this->plugin->getEconomyManager();
        $economy->addMoney($player, $totalPrice);

        $player->sendMessage($this->plugin->getMessage("shop.sell-success", [
            "amount" => $amount,
            "item" => $item->getName(),
            "price" => $totalPrice,
            "currency" => $economy->getCurrencySymbol()
        ]));
        $player->removeCurrentWindow();
    }

    private function createCategoryItem(Category $category): Item {
        $item = StringToItemParser::getInstance()->parse($category->getIcon());
        if ($item === null) {
            $item = VanillaBlocks::CHEST()->asItem();
        }
        
        $item->setCustomName($category->getDisplayName());
        $item->setLore([
            "",
            TF::GRAY . "Sub-categories: " . TF::WHITE . count($category->getSubCategories()),
            "",
            TF::YELLOW . "Click to browse!"
        ]);
        
        return $item;
    }

    private function createSubCategoryItem(SubCategory $subCategory): Item {
        $item = StringToItemParser::getInstance()->parse($subCategory->getIcon());
        if ($item === null) {
            $item = VanillaBlocks::CHEST()->asItem();
        }
        
        $item->setCustomName($subCategory->getDisplayName());
        $item->setLore([
            "",
            TF::GRAY . "Items: " . TF::WHITE . count($subCategory->getItems()),
            "",
            TF::YELLOW . "Click to view items!"
        ]);
        
        return $item;
    }

    private function createShopItemDisplay(ShopItem $shopItem): Item {
        $item = StringToItemParser::getInstance()->parse($shopItem->getId());
        if ($item === null) {
            $item = VanillaItems::PAPER();
        }
        
        $currency = $this->plugin->getEconomyManager()->getCurrencySymbol();
        $item->setCustomName($shopItem->getName());
        
        $lore = [
            "",
            TF::GRAY . $shopItem->getDescription(),
            "",
            TF::GREEN . "Buy: " . TF::WHITE . $currency . $shopItem->getBuyPrice()
        ];
        
        if ($shopItem->isSellable()) {
            $lore[] = TF::RED . "Sell: " . TF::WHITE . $currency . $shopItem->getSellPrice();
        } else {
            $lore[] = TF::RED . "Cannot be sold";
        }
        
        $lore[] = "";
        $lore[] = TF::YELLOW . "Click to buy/sell!";
        
        $item->setLore($lore);
        return $item;
    }

    private function addBorders(Inventory $inventory): void {
        $borderItem = VanillaItems::GRAY_DYE();
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
