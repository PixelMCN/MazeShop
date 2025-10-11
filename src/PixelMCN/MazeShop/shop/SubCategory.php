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
                $itemData["amount"] ?? 1,
                $itemData["icon"] ?? null
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
