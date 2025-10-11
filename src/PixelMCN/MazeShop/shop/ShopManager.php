<?php

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
        $this->shopConfig = new Config($shopFile, Config::YAML);
        $this->categories = [];

        $categoriesData = $this->shopConfig->get("categories", []);
        foreach ($categoriesData as $categoryName => $categoryData) {
            $this->categories[$categoryName] = new Category(
                $categoryName,
                $categoryData["display-name"] ?? $categoryName,
                $categoryData["icon"] ?? "minecraft:chest",
                $categoryData["subcategories"] ?? []
            );
        }
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
