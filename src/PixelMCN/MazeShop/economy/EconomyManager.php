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
