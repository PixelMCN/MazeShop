<?php

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

    public function __construct(
        string $id,
        int $meta,
        string $name,
        string $description,
        float $buyPrice,
        float $sellPrice,
        int $amount
    ) {
        $this->id = $id;
        $this->meta = $meta;
        $this->name = $name;
        $this->description = $description;
        $this->buyPrice = $buyPrice;
        $this->sellPrice = $sellPrice;
        $this->amount = $amount;
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
            "amount" => $this->amount
        ];
    }
}
