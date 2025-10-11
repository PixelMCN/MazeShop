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

class ShopItem {

    private string $id;
    private int $meta;
    private string $name;
    private string $description;
    private float $buyPrice;
    private float $sellPrice;
    private int $amount;
    private ?string $icon;

    public function __construct(
        string $id,
        int $meta,
        string $name,
        string $description,
        float $buyPrice,
        float $sellPrice,
        int $amount,
        ?string $icon = null
    ) {
        $this->id = $id;
        $this->meta = $meta;
        $this->name = $name;
        $this->description = $description;
        $this->buyPrice = $buyPrice;
        $this->sellPrice = $sellPrice;
        $this->amount = $amount;
        $this->icon = $icon;
    }

    public function getId(): string {
        return $this->id;
    }

    public function getMeta(): int {
        return $this->meta;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getBuyPrice(): float {
        return $this->buyPrice;
    }

    public function getSellPrice(): float {
        return $this->sellPrice;
    }

    public function getAmount(): int {
        return $this->amount;
    }

    public function setBuyPrice(float $price): void {
        $this->buyPrice = $price;
    }

    public function setSellPrice(float $price): void {
        $this->sellPrice = $price;
    }

    public function setDescription(string $description): void {
        $this->description = $description;
    }

    public function getIcon(): ?string {
        return $this->icon;
    }

    public function setIcon(?string $icon): void {
        $this->icon = $icon;
    }

    public function isSellable(): bool {
        return $this->sellPrice > 0;
    }

    public function toArray(): array {
        return [
            "id" => $this->id,
            "meta" => $this->meta,
            "name" => $this->name,
            "description" => $this->description,
            "buy-price" => $this->buyPrice,
            "sell-price" => $this->sellPrice,
            "amount" => $this->amount,
            "icon" => $this->icon
        ];
    }
}
