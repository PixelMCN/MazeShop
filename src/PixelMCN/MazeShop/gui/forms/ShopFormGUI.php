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

namespace PixelMCN\MazeShop\gui\forms;

use pocketmine\player\Player;
use pocketmine\form\Form;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use PixelMCN\MazeShop\Main;
use PixelMCN\MazeShop\shop\Category;
use PixelMCN\MazeShop\shop\SubCategory;
use PixelMCN\MazeShop\shop\ShopItem;
use PixelMCN\MazeShop\event\ItemPurchaseEvent;
use PixelMCN\MazeShop\event\ItemSellEvent;

class ShopFormGUI {

    private Main $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function sendMainMenu(Player $player): void {
        $form = new class($this->plugin, $player) implements Form {
            private Main $plugin;
            private Player $player;

            public function __construct(Main $plugin, Player $player) {
                $this->plugin = $plugin;
                $this->player = $player;
            }

            public function jsonSerialize(): array {
                $buttons = [];
                foreach ($this->plugin->getShopManager()->getCategories() as $category) {
                    $buttons[] = [
                        "text" => $category->getDisplayName(),
                        "image" => [
                            "type" => "path",
                            "data" => $category->getIcon()
                        ]
                    ];
                }

                return [
                    "type" => "form",
                    "title" => $this->plugin->getMessage("shop-form.title"),
                    "content" => $this->plugin->getMessage("shop-form.content"),
                    "buttons" => $buttons
                ];
            }

            public function handleResponse(Player $player, $data): void {
                if ($data === null) {
                    return;
                }

                $categories = array_values($this->plugin->getShopManager()->getCategories());
                if (!isset($categories[$data])) {
                    return;
                }

                $gui = new ShopFormGUI($this->plugin);
                $gui->sendCategoryMenu($player, $categories[$data]);
            }
        };

        $player->sendForm($form);
    }

    public function sendCategoryMenu(Player $player, Category $category): void {
        $form = new class($this->plugin, $player, $category) implements Form {
            private Main $plugin;
            private Player $player;
            private Category $category;

            public function __construct(Main $plugin, Player $player, Category $category) {
                $this->plugin = $plugin;
                $this->player = $player;
                $this->category = $category;
            }

            public function jsonSerialize(): array {
                $buttons = [];
                foreach ($this->category->getSubCategories() as $subCategory) {
                    $buttons[] = [
                        "text" => $subCategory->getDisplayName(),
                        "image" => [
                            "type" => "path",
                            "data" => $subCategory->getIcon()
                        ]
                    ];
                }

                // Add back button
                $buttons[] = [
                    "text" => $this->plugin->getMessage("shop.back-button"),
                ];

                return [
                    "type" => "form",
                    "title" => $this->plugin->getMessage("shop-form.category-title", [
                        "category" => $this->category->getDisplayName()
                    ]),
                    "content" => $this->plugin->getMessage("shop-form.category-content"),
                    "buttons" => $buttons
                ];
            }

            public function handleResponse(Player $player, $data): void {
                if ($data === null) {
                    return;
                }

                $subCategories = array_values($this->category->getSubCategories());
                
                // Check if back button was clicked
                if ($data === count($subCategories)) {
                    $gui = new ShopFormGUI($this->plugin);
                    $gui->sendMainMenu($player);
                    return;
                }

                if (!isset($subCategories[$data])) {
                    return;
                }

                $gui = new ShopFormGUI($this->plugin);
                $gui->sendSubCategoryMenu($player, $this->category, $subCategories[$data]);
            }
        };

        $player->sendForm($form);
    }

    public function sendSubCategoryMenu(Player $player, Category $category, SubCategory $subCategory): void {
        $form = new class($this->plugin, $player, $category, $subCategory) implements Form {
            private Main $plugin;
            private Player $player;
            private Category $category;
            private SubCategory $subCategory;

            public function __construct(Main $plugin, Player $player, Category $category, SubCategory $subCategory) {
                $this->plugin = $plugin;
                $this->player = $player;
                $this->category = $category;
                $this->subCategory = $subCategory;
            }

            public function jsonSerialize(): array {
                $buttons = [];
                foreach ($this->subCategory->getItems() as $item) {
                    $currency = $this->plugin->getEconomyManager()->getCurrencySymbol();
                    $button = [
                        "text" => $item->getName() . "\n§aBuy: " . $currency . $item->getBuyPrice()
                    ];
                    
                    // Add icon if item has one
                    if ($item->getIcon() !== null && $item->getIcon() !== "") {
                        $button["image"] = [
                            "type" => "path",
                            "data" => $item->getIcon()
                        ];
                    }
                    
                    $buttons[] = $button;
                }

                // Add back button
                $buttons[] = [
                    "text" => $this->plugin->getMessage("shop.back-button"),
                ];

                return [
                    "type" => "form",
                    "title" => $this->subCategory->getDisplayName(),
                    "content" => "§7Select an item:",
                    "buttons" => $buttons
                ];
            }

            public function handleResponse(Player $player, $data): void {
                if ($data === null) {
                    return;
                }

                $items = array_values($this->subCategory->getItems());
                
                // Check if back button was clicked
                if ($data === count($items)) {
                    $gui = new ShopFormGUI($this->plugin);
                    $gui->sendCategoryMenu($player, $this->category);
                    return;
                }

                if (!isset($items[$data])) {
                    return;
                }

                $gui = new ShopFormGUI($this->plugin);
                $gui->sendItemMenu($player, $this->category, $this->subCategory, $items[$data]);
            }
        };

        $player->sendForm($form);
    }

    public function sendItemMenu(Player $player, Category $category, SubCategory $subCategory, ShopItem $item): void {
        $form = new class($this->plugin, $player, $category, $subCategory, $item) implements Form {
            private Main $plugin;
            private Player $player;
            private Category $category;
            private SubCategory $subCategory;
            private ShopItem $item;

            public function __construct(Main $plugin, Player $player, Category $category, SubCategory $subCategory, ShopItem $item) {
                $this->plugin = $plugin;
                $this->player = $player;
                $this->category = $category;
                $this->subCategory = $subCategory;
                $this->item = $item;
            }

            public function jsonSerialize(): array {
                $currency = $this->plugin->getEconomyManager()->getCurrencySymbol();
                $content = "§aBuy Price: §f" . $currency . $this->item->getBuyPrice() . "\n";
                $content .= "§cSell Price: §f" . $currency . $this->item->getSellPrice();

                return [
                    "type" => "custom_form",
                    "title" => $this->item->getName(),
                    "content" => [
                        [
                            "type" => "label",
                            "text" => $content
                        ],
                        [
                            "type" => "input",
                            "text" => $this->plugin->getMessage("shop-form.amount-input"),
                            "placeholder" => "1"
                        ],
                        [
                            "type" => "dropdown",
                            "text" => "Action",
                            "options" => ["Buy", "Sell"]
                        ]
                    ]
                ];
            }

            public function handleResponse(Player $player, $data): void {
                if ($data === null) {
                    return;
                }

                $amount = (int)($data[1] ?? 1);
                $action = (int)($data[2] ?? 0);

                if ($amount <= 0) {
                    $player->sendMessage($this->plugin->getMessage("general.invalid-amount"));
                    return;
                }

                if ($action === 0) {
                    // Buy
                    $this->handleBuy($player, $amount);
                } else {
                    // Sell
                    $this->handleSell($player, $amount);
                }
            }

            private function handleBuy(Player $player, int $amount): void {
                $totalPrice = $this->item->getBuyPrice() * $amount;
                $economy = $this->plugin->getEconomyManager();

                if (!$economy->hasMoney($player, $totalPrice)) {
                    $player->sendMessage($this->plugin->getMessage("general.insufficient-funds", [
                        "price" => $totalPrice,
                        "balance" => $economy->getBalance($player),
                        "currency" => $economy->getCurrencySymbol()
                    ]));
                    return;
                }

                $event = new ItemPurchaseEvent($player, $this->item, $amount, $totalPrice);
                $event->call();

                if ($event->isCancelled()) {
                    return;
                }

                if (!$economy->reduceMoney($player, $totalPrice)) {
                    $player->sendMessage($this->plugin->getMessage("shop.purchase-failed", [
                        "item" => $this->item->getName()
                    ]));
                    return;
                }

                // Give item to player
                $itemInstance = StringToItemParser::getInstance()->parse($this->item->getId());
                if ($itemInstance === null) {
                    $itemInstance = VanillaItems::AIR();
                }
                $itemInstance->setCount($amount);

                if (!$player->getInventory()->canAddItem($itemInstance)) {
                    $economy->addMoney($player, $totalPrice);
                    $player->sendMessage($this->plugin->getMessage("shop.inventory-full"));
                    return;
                }

                $player->getInventory()->addItem($itemInstance);
                $player->sendMessage($this->plugin->getMessage("shop.purchase-success", [
                    "amount" => $amount,
                    "item" => $this->item->getName(),
                    "price" => $totalPrice,
                    "currency" => $economy->getCurrencySymbol()
                ]));
            }

            private function handleSell(Player $player, int $amount): void {
                if (!$this->item->isSellable()) {
                    $player->sendMessage($this->plugin->getMessage("shop.item-not-sellable"));
                    return;
                }

                $totalPrice = $this->item->getSellPrice() * $amount;

                // Check if player has items
                $itemInstance = StringToItemParser::getInstance()->parse($this->item->getId());
                if ($itemInstance === null) {
                    $player->sendMessage($this->plugin->getMessage("shop.sell-failed", [
                        "item" => $this->item->getName()
                    ]));
                    return;
                }
                $itemInstance->setCount($amount);

                if (!$player->getInventory()->contains($itemInstance)) {
                    $player->sendMessage($this->plugin->getMessage("shop.not-enough-items", [
                        "item" => $this->item->getName()
                    ]));
                    return;
                }

                $event = new ItemSellEvent($player, $this->item, $amount, $totalPrice);
                $event->call();

                if ($event->isCancelled()) {
                    return;
                }

                $player->getInventory()->removeItem($itemInstance);
                $economy = $this->plugin->getEconomyManager();
                $economy->addMoney($player, $totalPrice);

                $player->sendMessage($this->plugin->getMessage("shop.sell-success", [
                    "amount" => $amount,
                    "item" => $this->item->getName(),
                    "price" => $totalPrice,
                    "currency" => $economy->getCurrencySymbol()
                ]));
            }
        };

        $player->sendForm($form);
    }
}
