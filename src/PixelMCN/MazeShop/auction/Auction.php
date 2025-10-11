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

namespace PixelMCN\MazeShop\auction;

class Auction {

    private int $id;
    private string $seller;
    private string $itemId;
    private int $meta;
    private string $itemName;
    private int $amount;
    private float $startingBid;
    private float $currentBid;
    private ?string $currentBidder;
    private int $endTime;

    public function __construct(
        int $id,
        string $seller,
        string $itemId,
        int $meta,
        string $itemName,
        int $amount,
        float $startingBid,
        int $endTime
    ) {
        $this->id = $id;
        $this->seller = $seller;
        $this->itemId = $itemId;
        $this->meta = $meta;
        $this->itemName = $itemName;
        $this->amount = $amount;
        $this->startingBid = $startingBid;
        $this->currentBid = $startingBid;
        $this->currentBidder = null;
        $this->endTime = $endTime;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getSeller(): string {
        return $this->seller;
    }

    public function getItemId(): string {
        return $this->itemId;
    }

    public function getMeta(): int {
        return $this->meta;
    }

    public function getItemName(): string {
        return $this->itemName;
    }

    public function getAmount(): int {
        return $this->amount;
    }

    public function getStartingBid(): float {
        return $this->startingBid;
    }

    public function getCurrentBid(): float {
        return $this->currentBid;
    }

    public function getCurrentBidder(): ?string {
        return $this->currentBidder;
    }

    public function getEndTime(): int {
        return $this->endTime;
    }

    public function getTimeRemaining(): int {
        return max(0, $this->endTime - time());
    }

    public function getTimeRemainingFormatted(): string {
        $remaining = $this->getTimeRemaining();
        
        if ($remaining <= 0) {
            return "Ended";
        }

        $hours = floor($remaining / 3600);
        $minutes = floor(($remaining % 3600) / 60);
        $seconds = $remaining % 60;

        if ($hours > 0) {
            return sprintf("%dh %dm", $hours, $minutes);
        } elseif ($minutes > 0) {
            return sprintf("%dm %ds", $minutes, $seconds);
        } else {
            return sprintf("%ds", $seconds);
        }
    }

    public function isExpired(): bool {
        return time() >= $this->endTime;
    }

    public function placeBid(string $bidder, float $amount): void {
        $this->currentBidder = $bidder;
        $this->currentBid = $amount;
    }

    public function toArray(): array {
        return [
            "id" => $this->id,
            "seller" => $this->seller,
            "item_id" => $this->itemId,
            "meta" => $this->meta,
            "item_name" => $this->itemName,
            "amount" => $this->amount,
            "starting_bid" => $this->startingBid,
            "current_bid" => $this->currentBid,
            "current_bidder" => $this->currentBidder,
            "end_time" => $this->endTime
        ];
    }

    public static function fromArray(array $data): ?self {
        if (!isset($data["id"], $data["seller"], $data["item_id"], $data["item_name"], 
                   $data["amount"], $data["starting_bid"], $data["end_time"])) {
            return null;
        }

        $auction = new self(
            $data["id"],
            $data["seller"],
            $data["item_id"],
            $data["meta"] ?? 0,
            $data["item_name"],
            $data["amount"],
            $data["starting_bid"],
            $data["end_time"]
        );

        if (isset($data["current_bidder"], $data["current_bid"])) {
            $auction->placeBid($data["current_bidder"], $data["current_bid"]);
        }

        return $auction;
    }
}
