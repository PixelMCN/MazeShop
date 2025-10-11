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

use pocketmine\form\Form;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use PixelMCN\MazeShop\Main;
use PixelMCN\MazeShop\shop\Category;
use PixelMCN\MazeShop\shop\SubCategory;
use PixelMCN\MazeShop\shop\ShopItem;

class ShopAdminFormGUI {

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
                return [
                    "type" => "form",
                    "title" => "§l§4Shop Admin",
                    "content" => "§0Manage shop categories, subcategories, and items:",
                    "buttons" => [
                        ["text" => "§2Add Category\n§0Create a new shop category"],
                        ["text" => "§6Edit Category\n§0Modify existing category"],
                        ["text" => "§4Remove Category\n§0Delete a category"],
                        ["text" => "§3Add Item\n§0Add item to shop"],
                        ["text" => "§6Edit Item\n§0Modify item properties"],
                        ["text" => "§4Remove Item\n§0Delete an item"],
                        ["text" => "§5Reload Shop\n§0Refresh shop data"],
                    ]
                ];
            }

            public function handleResponse(Player $player, $data): void {
                if ($data === null) {
                    return;
                }

                $gui = new ShopAdminFormGUI($this->plugin);
                
                match($data) {
                    0 => $gui->sendAddCategoryForm($player),
                    1 => $gui->sendEditCategoryMenu($player),
                    2 => $gui->sendRemoveCategoryMenu($player),
                    3 => $gui->sendAddItemMenu($player),
                    4 => $gui->sendEditItemMenu($player),
                    5 => $gui->sendRemoveItemMenu($player),
                    6 => $this->handleReload($player),
                    default => null
                };
            }

            private function handleReload(Player $player): void {
                $this->plugin->getShopManager()->reload();
                $player->sendMessage("§aShop reloaded successfully!");
            }
        };

        $player->sendForm($form);
    }

    public function sendAddCategoryForm(Player $player): void {
        $form = new class($this->plugin, $player) implements Form {
            private Main $plugin;
            private Player $player;

            public function __construct(Main $plugin, Player $player) {
                $this->plugin = $plugin;
                $this->player = $player;
            }

            public function jsonSerialize(): array {
                return [
                    "type" => "custom_form",
                    "title" => "§2Add Category",
                    "content" => [
                        [
                            "type" => "input",
                            "text" => "Category Name (ID)",
                            "placeholder" => "e.g., Tools"
                        ],
                        [
                            "type" => "input",
                            "text" => "Display Name",
                            "placeholder" => "§6Tools & Equipment"
                        ],
                        [
                            "type" => "input",
                            "text" => "Icon Path",
                            "placeholder" => "textures/items/diamond_pickaxe"
                        ]
                    ]
                ];
            }

            public function handleResponse(Player $player, $data): void {
                if ($data === null) {
                    return;
                }

                $name = trim($data[0] ?? "");
                $displayName = trim($data[1] ?? $name);
                $icon = trim($data[2] ?? "textures/blocks/stone");

                if (empty($name)) {
                    $player->sendMessage("§cCategory name cannot be empty!");
                    return;
                }

                $shopFile = $this->plugin->getDataFolder() . "shop.yml";
                $config = new Config($shopFile, Config::YAML);
                $categories = $config->get("categories", []);

                if (isset($categories[$name])) {
                    $player->sendMessage("§cCategory '{$name}' already exists!");
                    return;
                }

                $categories[$name] = [
                    "display-name" => $displayName,
                    "icon" => $icon,
                    "subcategories" => []
                ];

                $config->set("categories", $categories);
                $config->save();

                $this->plugin->getShopManager()->reload();
                $player->sendMessage("§aCategory '{$name}' added successfully!");
            }
        };

        $player->sendForm($form);
    }

    public function sendEditCategoryMenu(Player $player): void {
        $categories = $this->plugin->getShopManager()->getCategories();
        
        if (empty($categories)) {
            $player->sendMessage("§cNo categories found!");
            return;
        }

        $form = new class($this->plugin, $player, $categories) implements Form {
            private Main $plugin;
            private Player $player;
            private array $categories;

            public function __construct(Main $plugin, Player $player, array $categories) {
                $this->plugin = $plugin;
                $this->player = $player;
                $this->categories = $categories;
            }

            public function jsonSerialize(): array {
                $buttons = [];
                foreach ($this->categories as $category) {
                    $buttons[] = ["text" => "§6" . $category->getDisplayName() . "\n§0Click to edit"];
                }
                $buttons[] = ["text" => "§4Back\n§0Return to menu"];

                return [
                    "type" => "form",
                    "title" => "§6Edit Category",
                    "content" => "§0Select a category to edit:",
                    "buttons" => $buttons
                ];
            }

            public function handleResponse(Player $player, $data): void {
                if ($data === null) {
                    return;
                }

                $categories = array_values($this->categories);
                if ($data === count($categories)) {
                    (new ShopAdminFormGUI($this->plugin))->sendMainMenu($player);
                    return;
                }

                if (isset($categories[$data])) {
                    (new ShopAdminFormGUI($this->plugin))->sendCategoryEditForm($player, $categories[$data]);
                }
            }
        };

        $player->sendForm($form);
    }

    public function sendCategoryEditForm(Player $player, Category $category): void {
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
                return [
                    "type" => "custom_form",
                    "title" => "§6Edit: " . $this->category->getName(),
                    "content" => [
                        [
                            "type" => "input",
                            "text" => "Display Name",
                            "default" => $this->category->getDisplayName()
                        ],
                        [
                            "type" => "input",
                            "text" => "Icon Path",
                            "default" => $this->category->getIcon()
                        ]
                    ]
                ];
            }

            public function handleResponse(Player $player, $data): void {
                if ($data === null) {
                    return;
                }

                $shopFile = $this->plugin->getDataFolder() . "shop.yml";
                $config = new Config($shopFile, Config::YAML);
                $categories = $config->get("categories", []);

                if (isset($categories[$this->category->getName()])) {
                    $categories[$this->category->getName()]["display-name"] = $data[0];
                    $categories[$this->category->getName()]["icon"] = $data[1];
                    
                    $config->set("categories", $categories);
                    $config->save();
                    
                    $this->plugin->getShopManager()->reload();
                    $player->sendMessage("§aCategory updated successfully!");
                }
            }
        };

        $player->sendForm($form);
    }

    public function sendRemoveCategoryMenu(Player $player): void {
        $categories = $this->plugin->getShopManager()->getCategories();
        
        if (empty($categories)) {
            $player->sendMessage("§cNo categories found!");
            return;
        }

        $form = new class($this->plugin, $player, $categories) implements Form {
            private Main $plugin;
            private Player $player;
            private array $categories;

            public function __construct(Main $plugin, Player $player, array $categories) {
                $this->plugin = $plugin;
                $this->player = $player;
                $this->categories = $categories;
            }

            public function jsonSerialize(): array {
                $buttons = [];
                foreach ($this->categories as $category) {
                    $buttons[] = ["text" => "§4" . $category->getDisplayName() . "\n§0Click to delete"];
                }
                $buttons[] = ["text" => "§8Back\n§0Return to menu"];

                return [
                    "type" => "form",
                    "title" => "§4Remove Category",
                    "content" => "§0Select a category to remove:",
                    "buttons" => $buttons
                ];
            }

            public function handleResponse(Player $player, $data): void {
                if ($data === null) {
                    return;
                }

                $categories = array_values($this->categories);
                if ($data === count($categories)) {
                    (new ShopAdminFormGUI($this->plugin))->sendMainMenu($player);
                    return;
                }

                if (isset($categories[$data])) {
                    $category = $categories[$data];
                    $shopFile = $this->plugin->getDataFolder() . "shop.yml";
                    $config = new Config($shopFile, Config::YAML);
                    $allCategories = $config->get("categories", []);
                    
                    unset($allCategories[$category->getName()]);
                    
                    $config->set("categories", $allCategories);
                    $config->save();
                    
                    $this->plugin->getShopManager()->reload();
                    $player->sendMessage("§aCategory '{$category->getName()}' removed successfully!");
                }
            }
        };

        $player->sendForm($form);
    }

    public function sendAddItemMenu(Player $player): void {
        $categories = $this->plugin->getShopManager()->getCategories();
        
        if (empty($categories)) {
            $player->sendMessage("§cNo categories found! Add a category first.");
            return;
        }

        $form = new class($this->plugin, $player, $categories) implements Form {
            private Main $plugin;
            private Player $player;
            private array $categories;

            public function __construct(Main $plugin, Player $player, array $categories) {
                $this->plugin = $plugin;
                $this->player = $player;
                $this->categories = $categories;
            }

            public function jsonSerialize(): array {
                $categoryOptions = [];
                foreach ($this->categories as $category) {
                    $categoryOptions[] = $category->getDisplayName();
                }

                return [
                    "type" => "custom_form",
                    "title" => "§3Add Item",
                    "content" => [
                        [
                            "type" => "dropdown",
                            "text" => "Select Category",
                            "options" => $categoryOptions
                        ],
                        [
                            "type" => "input",
                            "text" => "Subcategory Name",
                            "placeholder" => "e.g., Tools"
                        ],
                        [
                            "type" => "input",
                            "text" => "Item ID",
                            "placeholder" => "minecraft:diamond_pickaxe"
                        ],
                        [
                            "type" => "input",
                            "text" => "Item Name",
                            "placeholder" => "§bDiamond Pickaxe"
                        ],
                        [
                            "type" => "input",
                            "text" => "Buy Price",
                            "placeholder" => "1000"
                        ],
                        [
                            "type" => "input",
                            "text" => "Sell Price",
                            "placeholder" => "500"
                        ],
                        [
                            "type" => "input",
                            "text" => "Icon Path (optional)",
                            "placeholder" => "textures/items/diamond_pickaxe"
                        ]
                    ]
                ];
            }

            public function handleResponse(Player $player, $data): void {
                if ($data === null) {
                    return;
                }

                $categories = array_values($this->categories);
                $category = $categories[$data[0]];
                $subcategoryName = trim($data[1] ?? "");
                $itemId = trim($data[2] ?? "");
                $itemName = trim($data[3] ?? $itemId);
                $buyPrice = (float)($data[4] ?? 0);
                $sellPrice = (float)($data[5] ?? 0);
                $icon = trim($data[6] ?? "");

                if (empty($subcategoryName) || empty($itemId)) {
                    $player->sendMessage("§cSubcategory name and Item ID cannot be empty!");
                    return;
                }

                $shopFile = $this->plugin->getDataFolder() . "shop.yml";
                $config = new Config($shopFile, Config::YAML);
                $allCategories = $config->get("categories", []);
                
                $categoryName = $category->getName();
                if (!isset($allCategories[$categoryName]["subcategories"][$subcategoryName])) {
                    $allCategories[$categoryName]["subcategories"][$subcategoryName] = [
                        "display-name" => $subcategoryName,
                        "icon" => "textures/blocks/stone",
                        "items" => []
                    ];
                }

                $newItem = [
                    "id" => $itemId,
                    "meta" => 0,
                    "name" => $itemName,
                    "description" => "",
                    "buy-price" => $buyPrice,
                    "sell-price" => $sellPrice,
                    "amount" => 1
                ];

                if (!empty($icon)) {
                    $newItem["icon"] = $icon;
                }

                $allCategories[$categoryName]["subcategories"][$subcategoryName]["items"][] = $newItem;
                
                $config->set("categories", $allCategories);
                $config->save();
                
                $this->plugin->getShopManager()->reload();
                $player->sendMessage("§aItem '{$itemName}' added successfully!");
            }
        };

        $player->sendForm($form);
    }

    public function sendEditItemMenu(Player $player): void {
        $player->sendMessage("§eFeature coming soon! Use /shop.yml to edit items manually for now.");
    }

    public function sendRemoveItemMenu(Player $player): void {
        $player->sendMessage("§eFeature coming soon! Use /shop.yml to remove items manually for now.");
    }
}
