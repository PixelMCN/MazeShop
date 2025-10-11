<?php

declare(strict_types=1);

namespace PixelMCN\MazeShop\shop;

class SubCategory {

    private string $name;
    private string $displayName;
    private string $icon;
    private array $items = [];

    public function __construct(string $name, string $displayName, string $icon, array $itemsData) {
        $this->name = $name;
        $this->displayName = $displayName;
        $this->icon = $icon;

        foreach ($itemsData as $itemData) {
            $item = new ShopItem(
                $itemData["id"],
                $itemData["meta"] ?? 0,
                $itemData["name"] ?? $itemData["id"],
                $itemData["description"] ?? "",
                $itemData["buy-price"] ?? 0,
                $itemData["sell-price"] ?? 0,
                $itemData["amount"] ?? 1
            );
            $this->items[$item->getId()] = $item;
        }
    }

    public function getName(): string {
        return $this->name;
    }

    public function getDisplayName(): string {
        return $this->displayName;
    }

    public function getIcon(): string {
        return $this->icon;
    }

    public function getItems(): array {
        return $this->items;
    }

    public function getItem(string $id): ?ShopItem {
        return $this->items[$id] ?? null;
    }

    public function addItem(ShopItem $item): void {
        $this->items[$item->getId()] = $item;
    }

    public function removeItem(string $id): bool {
        if (isset($this->items[$id])) {
            unset($this->items[$id]);
            return true;
        }
        return false;
    }

    public function toArray(): array {
        $data = [
            "display-name" => $this->displayName,
            "icon" => $this->icon,
            "items" => []
        ];

        foreach ($this->items as $item) {
            $data["items"][] = $item->toArray();
        }

        return $data;
    }
}
