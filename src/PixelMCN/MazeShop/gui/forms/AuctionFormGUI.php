<?php

declare(strict_types=1);

namespace PixelMCN\MazeShop\gui\forms;

use pocketmine\player\Player;
use pocketmine\form\Form;
use PixelMCN\MazeShop\Main;
use PixelMCN\MazeShop\auction\Auction;

class AuctionFormGUI {

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
                    "title" => $this->plugin->getMessage("auction.title"),
                    "content" => $this->plugin->getMessage("auction.description"),
                    "buttons" => [
                        [
                            "text" => $this->plugin->getMessage("auction.list-button"),
                            "image" => [
                                "type" => "path",
                                "data" => $this->plugin->getMessage("images.auction-icon")
                            ]
                        ],
                        [
                            "text" => $this->plugin->getMessage("auction.create-button"),
                            "image" => [
                                "type" => "path",
                                "data" => $this->plugin->getMessage("images.buy-icon")
                            ]
                        ],
                        [
                            "text" => $this->plugin->getMessage("auction.my-auctions-button"),
                            "image" => [
                                "type" => "path",
                                "data" => $this->plugin->getMessage("images.shop-icon")
                            ]
                        ]
                    ]
                ];
            }

            public function handleResponse(Player $player, $data): void {
                if ($data === null) {
                    return;
                }

                $gui = new AuctionFormGUI($this->plugin);

                switch ($data) {
                    case 0:
                        $gui->sendAuctionList($player);
                        break;
                    case 1:
                        $gui->sendCreateAuction($player);
                        break;
                    case 2:
                        $gui->sendMyAuctions($player);
                        break;
                }
            }
        };

        $player->sendForm($form);
    }

    public function sendAuctionList(Player $player): void {
        $auctions = $this->plugin->getAuctionManager()->getAllAuctions();

        if (empty($auctions)) {
            $player->sendMessage($this->plugin->getMessage("auction.no-active-auctions"));
            return;
        }

        $form = new class($this->plugin, $player, $auctions) implements Form {
            private Main $plugin;
            private Player $player;
            private array $auctions;

            public function __construct(Main $plugin, Player $player, array $auctions) {
                $this->plugin = $plugin;
                $this->player = $player;
                $this->auctions = $auctions;
            }

            public function jsonSerialize(): array {
                $buttons = [];
                $currency = $this->plugin->getEconomyManager()->getCurrencySymbol();

                foreach ($this->auctions as $auction) {
                    $buttons[] = [
                        "text" => $this->plugin->getMessage("auction-form.auction-button", [
                            "item" => $auction->getItemName(),
                            "current-bid" => $auction->getCurrentBid(),
                            "currency" => $currency
                        ]),
                        "image" => [
                            "type" => "path",
                            "data" => $this->plugin->getMessage("images.auction-icon")
                        ]
                    ];
                }

                // Add back button
                $buttons[] = [
                    "text" => $this->plugin->getMessage("shop.back-button")
                ];

                return [
                    "type" => "form",
                    "title" => $this->plugin->getMessage("auction-form.list-title"),
                    "content" => $this->plugin->getMessage("auction-form.list-content"),
                    "buttons" => $buttons
                ];
            }

            public function handleResponse(Player $player, $data): void {
                if ($data === null) {
                    return;
                }

                $auctionsList = array_values($this->auctions);

                // Check if back button was clicked
                if ($data === count($auctionsList)) {
                    $gui = new AuctionFormGUI($this->plugin);
                    $gui->sendMainMenu($player);
                    return;
                }

                if (!isset($auctionsList[$data])) {
                    return;
                }

                $gui = new AuctionFormGUI($this->plugin);
                $gui->sendAuctionView($player, $auctionsList[$data]);
            }
        };

        $player->sendForm($form);
    }

    public function sendAuctionView(Player $player, Auction $auction): void {
        $form = new class($this->plugin, $player, $auction) implements Form {
            private Main $plugin;
            private Player $player;
            private Auction $auction;

            public function __construct(Main $plugin, Player $player, Auction $auction) {
                $this->plugin = $plugin;
                $this->player = $player;
                $this->auction = $auction;
            }

            public function jsonSerialize(): array {
                $currency = $this->plugin->getEconomyManager()->getCurrencySymbol();
                $content = $this->plugin->getMessage("auction-form.view-content", [
                    "item" => $this->auction->getItemName(),
                    "amount" => $this->auction->getAmount(),
                    "seller" => $this->auction->getSeller(),
                    "current-bid" => $this->auction->getCurrentBid(),
                    "time-left" => $this->auction->getTimeRemainingFormatted(),
                    "currency" => $currency
                ]);

                return [
                    "type" => "custom_form",
                    "title" => $this->plugin->getMessage("auction-form.view-title", [
                        "auction-id" => $this->auction->getId()
                    ]),
                    "content" => [
                        [
                            "type" => "label",
                            "text" => $content
                        ],
                        [
                            "type" => "input",
                            "text" => "Bid Amount:",
                            "placeholder" => (string)($this->auction->getCurrentBid() + 1)
                        ]
                    ]
                ];
            }

            public function handleResponse(Player $player, $data): void {
                if ($data === null) {
                    return;
                }

                $bidAmount = (float)($data[1] ?? 0);

                if ($bidAmount <= 0) {
                    $player->sendMessage($this->plugin->getMessage("general.invalid-amount"));
                    return;
                }

                if ($this->plugin->getAuctionManager()->placeBid($player, $this->auction->getId(), $bidAmount)) {
                    $player->sendMessage($this->plugin->getMessage("auction.bid-success", [
                        "amount" => $bidAmount,
                        "auction-id" => $this->auction->getId(),
                        "currency" => $this->plugin->getEconomyManager()->getCurrencySymbol()
                    ]));
                } else {
                    $player->sendMessage($this->plugin->getMessage("auction.bid-failed"));
                }
            }
        };

        $player->sendForm($form);
    }

    public function sendCreateAuction(Player $player): void {
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
                    "title" => $this->plugin->getMessage("auction-form.create-title"),
                    "content" => [
                        [
                            "type" => "label",
                            "text" => $this->plugin->getMessage("auction-form.create-content")
                        ],
                        [
                            "type" => "input",
                            "text" => "Starting Bid:",
                            "placeholder" => "100"
                        ],
                        [
                            "type" => "input",
                            "text" => $this->plugin->getMessage("auction-form.duration-label"),
                            "placeholder" => "3600"
                        ]
                    ]
                ];
            }

            public function handleResponse(Player $player, $data): void {
                if ($data === null) {
                    return;
                }

                $startingBid = (float)($data[1] ?? 0);
                $duration = (int)($data[2] ?? 0);

                if ($startingBid <= 0 || $duration <= 0) {
                    $player->sendMessage($this->plugin->getMessage("general.invalid-amount"));
                    return;
                }

                $config = $this->plugin->getConfig();
                $auctionConfig = $config->get("auction");
                
                if (!is_array($auctionConfig)) {
                    $player->sendMessage("§cAuction configuration error!");
                    return;
                }

                if ($duration < ($auctionConfig["min-duration"] ?? 300)) {
                    $player->sendMessage("§cDuration must be at least " . ($auctionConfig["min-duration"] ?? 300) . " seconds!");
                    return;
                }

                if ($duration > ($auctionConfig["max-duration"] ?? 86400)) {
                    $player->sendMessage("§cDuration cannot exceed " . ($auctionConfig["max-duration"] ?? 86400) . " seconds!");
                    return;
                }

                $heldItem = $player->getInventory()->getItemInHand();

                if ($heldItem->isNull()) {
                    $player->sendMessage($this->plugin->getMessage("admin.hold-item"));
                    return;
                }

                $auction = $this->plugin->getAuctionManager()->createAuction(
                    $player,
                    $heldItem->getTypeId(),
                    0,
                    $heldItem->getName(),
                    $heldItem->getCount(),
                    $startingBid,
                    $duration
                );

                if ($auction !== null) {
                    $player->getInventory()->setItemInHand($heldItem->setCount(0));
                    $player->sendMessage($this->plugin->getMessage("auction.create-success", [
                        "auction-id" => $auction->getId()
                    ]));
                } else {
                    $player->sendMessage($this->plugin->getMessage("auction.max-auctions-reached"));
                }
            }
        };

        $player->sendForm($form);
    }

    public function sendMyAuctions(Player $player): void {
        $auctions = $this->plugin->getAuctionManager()->getPlayerAuctions($player->getName());

        if (empty($auctions)) {
            $player->sendMessage("§cYou have no active auctions!");
            return;
        }

        $form = new class($this->plugin, $player, $auctions) implements Form {
            private Main $plugin;
            private Player $player;
            private array $auctions;

            public function __construct(Main $plugin, Player $player, array $auctions) {
                $this->plugin = $plugin;
                $this->player = $player;
                $this->auctions = $auctions;
            }

            public function jsonSerialize(): array {
                $buttons = [];
                $currency = $this->plugin->getEconomyManager()->getCurrencySymbol();

                foreach ($this->auctions as $auction) {
                    $buttons[] = [
                        "text" => "#{$auction->getId()} - {$auction->getItemName()} - {$currency}{$auction->getCurrentBid()}"
                    ];
                }

                // Add back button
                $buttons[] = [
                    "text" => $this->plugin->getMessage("shop.back-button")
                ];

                return [
                    "type" => "form",
                    "title" => "§l§bMy Auctions",
                    "content" => "§7Your active auctions:",
                    "buttons" => $buttons
                ];
            }

            public function handleResponse(Player $player, $data): void {
                if ($data === null) {
                    return;
                }

                $auctionsList = array_values($this->auctions);

                // Check if back button was clicked
                if ($data === count($auctionsList)) {
                    $gui = new AuctionFormGUI($this->plugin);
                    $gui->sendMainMenu($player);
                    return;
                }

                if (!isset($auctionsList[$data])) {
                    return;
                }

                $gui = new AuctionFormGUI($this->plugin);
                $gui->sendAuctionView($player, $auctionsList[$data]);
            }
        };

        $player->sendForm($form);
    }
}
