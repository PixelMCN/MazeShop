<?php

declare(strict_types=1);

namespace PixelMCN\MazeShop\economy;

use pocketmine\player\Player;
use PixelMCN\MazeShop\Main;

class EconomyManager {

    private Main $plugin;
    private ?EconomyProvider $provider = null;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
        $this->detectEconomy();
    }

    private function detectEconomy(): void {
        $server = $this->plugin->getServer();
        $pluginManager = $server->getPluginManager();

        // Try MazePay first
        $mazePay = $pluginManager->getPlugin("MazePay");
        if ($mazePay !== null && $mazePay->isEnabled()) {
            $this->provider = new MazePayProvider($mazePay);
            $this->plugin->getLogger()->info("Hooked into MazePay for economy!");
            return;
        }

        // Try BedrockEconomy
        $bedrockEconomy = $pluginManager->getPlugin("BedrockEconomy");
        if ($bedrockEconomy !== null && $bedrockEconomy->isEnabled()) {
            $this->provider = new BedrockEconomyProvider($bedrockEconomy);
            $this->plugin->getLogger()->info("Hooked into BedrockEconomy for economy!");
            return;
        }

        $this->plugin->getLogger()->warning("No economy plugin found! Please install MazePay or BedrockEconomy.");
    }

    public function hasProvider(): bool {
        return $this->provider !== null;
    }

    public function getBalance(Player $player): float {
        if ($this->provider === null) {
            return 0.0;
        }
        return $this->provider->getBalance($player);
    }

    public function addMoney(Player $player, float $amount): bool {
        if ($this->provider === null) {
            return false;
        }
        return $this->provider->addMoney($player, $amount);
    }

    public function reduceMoney(Player $player, float $amount): bool {
        if ($this->provider === null) {
            return false;
        }
        return $this->provider->reduceMoney($player, $amount);
    }

    public function hasMoney(Player $player, float $amount): bool {
        if ($this->provider === null) {
            return false;
        }
        return $this->getBalance($player) >= $amount;
    }

    public function getCurrencySymbol(): string {
        $currency = $this->plugin->getConfig()->get("currency");
        return is_array($currency) ? ($currency["symbol"] ?? "$") : "$";
    }

    public function getCurrencyName(): string {
        $currency = $this->plugin->getConfig()->get("currency");
        return is_array($currency) ? ($currency["name"] ?? "Money") : "Money";
    }

    public function formatMoney(float $amount): string {
        return $this->getCurrencySymbol() . number_format($amount, 2);
    }
}
