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

namespace PixelMCN\MazeShop\shop;

use pocketmine\utils\Config;
use PixelMCN\MazeShop\Main;

class ShopManager {

    private Main $plugin;
    private Config $shopConfig;
    private array $categories = [];

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
        $this->loadShop();
    }

    public function loadShop(): void {
        $shopFile = $this->plugin->getDataFolder() . "shop.yml";
        
        // Check if shop.yml exists, if not create it
        if (!file_exists($shopFile)) {
            $this->plugin->saveResource("shop.yml");
        }
        
        $this->shopConfig = new Config($shopFile, Config::YAML);
        $this->categories = [];

        $categoriesData = $this->shopConfig->get("categories", []);
        
        // If categories is not an array or is empty, try to regenerate the file
        if (!is_array($categoriesData) || empty($categoriesData)) {
            $this->plugin->getLogger()->warning("Invalid or empty shop.yml detected. Regenerating...");
            
            // Backup old file
            if (file_exists($shopFile)) {
                rename($shopFile, $shopFile . ".backup." . time());
            }
            
            // Create new file
            $this->plugin->saveResource("shop.yml", true);
            $this->shopConfig = new Config($shopFile, Config::YAML);
            $categoriesData = $this->shopConfig->get("categories", []);
            
            if (!is_array($categoriesData)) {
                $this->plugin->getLogger()->error("Failed to load shop.yml even after regeneration!");
                return;
            }
        }
        
        foreach ($categoriesData as $categoryName => $categoryData) {
            // Ensure category name is a string
            if (!is_string($categoryName)) {
                $this->plugin->getLogger()->warning("Invalid category name (not a string): " . var_export($categoryName, true));
                continue;
            }
            
            // Ensure category data is an array
            if (!is_array($categoryData)) {
                $this->plugin->getLogger()->warning("Category data for '$categoryName' is not an array");
                continue;
            }
            
            $this->categories[$categoryName] = new Category(
                $categoryName,
                $categoryData["display-name"] ?? $categoryName,
                $categoryData["icon"] ?? "minecraft:chest",
                $categoryData["subcategories"] ?? []
            );
        }
        
        $this->plugin->getLogger()->info("Loaded " . count($this->categories) . " shop categories");
    }

    public function reload(): void {
        $this->loadShop();
    }

    public function getCategories(): array {
        return $this->categories;
    }

    public function getCategory(string $name): ?Category {
        return $this->categories[$name] ?? null;
    }

    public function addCategory(string $name, string $displayName, string $icon): bool {
        if (isset($this->categories[$name])) {
            return false;
        }

        $this->categories[$name] = new Category($name, $displayName, $icon, []);
        $this->saveShop();
        return true;
    }

    public function removeCategory(string $name): bool {
        if (!isset($this->categories[$name])) {
            return false;
        }

        unset($this->categories[$name]);
        $this->saveShop();
        return true;
    }

    public function addSubCategory(string $categoryName, string $subName, string $displayName, string $icon): bool {
        $category = $this->getCategory($categoryName);
        if ($category === null) {
            return false;
        }

        if ($category->hasSubCategory($subName)) {
            return false;
        }

        $category->addSubCategory(new SubCategory($subName, $displayName, $icon, []));
        $this->saveShop();
        return true;
    }

    public function removeSubCategory(string $categoryName, string $subName): bool {
        $category = $this->getCategory($categoryName);
        if ($category === null) {
            return false;
        }

        $result = $category->removeSubCategory($subName);
        if ($result) {
            $this->saveShop();
        }
        return $result;
    }

    public function addItem(string $categoryName, string $subName, ShopItem $item): bool {
        $category = $this->getCategory($categoryName);
        if ($category === null) {
            return false;
        }

        $subCategory = $category->getSubCategory($subName);
        if ($subCategory === null) {
            return false;
        }

        $subCategory->addItem($item);
        $this->saveShop();
        return true;
    }

    public function removeItem(string $categoryName, string $subName, string $itemId): bool {
        $category = $this->getCategory($categoryName);
        if ($category === null) {
            return false;
        }

        $subCategory = $category->getSubCategory($subName);
        if ($subCategory === null) {
            return false;
        }

        $result = $subCategory->removeItem($itemId);
        if ($result) {
            $this->saveShop();
        }
        return $result;
    }

    public function getItem(string $categoryName, string $subName, string $itemId): ?ShopItem {
        $category = $this->getCategory($categoryName);
        if ($category === null) {
            return null;
        }

        $subCategory = $category->getSubCategory($subName);
        if ($subCategory === null) {
            return null;
        }

        return $subCategory->getItem($itemId);
    }

    public function searchItem(string $itemName): ?array {
        foreach ($this->categories as $categoryName => $category) {
            foreach ($category->getSubCategories() as $subName => $subCategory) {
                foreach ($subCategory->getItems() as $item) {
                    if (stripos($item->getName(), $itemName) !== false || 
                        stripos($item->getId(), $itemName) !== false) {
                        return [
                            "category" => $categoryName,
                            "subcategory" => $subName,
                            "item" => $item
                        ];
                    }
                }
            }
        }
        return null;
    }

    private function saveShop(): void {
        $data = ["categories" => []];

        foreach ($this->categories as $categoryName => $category) {
            $data["categories"][$categoryName] = $category->toArray();
        }

        $this->shopConfig->setAll($data);
        $this->shopConfig->save();

        // Sync to database if enabled
        if ($this->plugin->getDatabaseManager()->isEnabled()) {
            $this->plugin->getDatabaseManager()->saveShopDataToDatabase($data["categories"]);
        }
    }
}
