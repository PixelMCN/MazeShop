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
use pocketmine\plugin\Plugin;
use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\currency\CurrencyManager;

class BedrockEconomyProvider implements EconomyProvider {

    private Plugin $plugin;

    public function __construct(Plugin $plugin) {
        $this->plugin = $plugin;
    }

    public function getBalance(Player $player): float {
        $currency = CurrencyManager::getInstance()->getDefault();
        $balance = BedrockEconomyAPI::legacy()->getPlayerBalance(
            $player->getName(),
            function(?int $balance) {
                return $balance ?? 0;
            }
        );
        return (float) $balance;
    }

    public function addMoney(Player $player, float $amount): bool {
        $currency = CurrencyManager::getInstance()->getDefault();
        BedrockEconomyAPI::legacy()->addToPlayerBalance(
            $player->getName(),
            (int) $amount,
            function(bool $success) {
                return $success;
            }
        );
        return true;
    }

    public function reduceMoney(Player $player, float $amount): bool {
        $currency = CurrencyManager::getInstance()->getDefault();
        BedrockEconomyAPI::legacy()->subtractFromPlayerBalance(
            $player->getName(),
            (int) $amount,
            function(bool $success) {
                return $success;
            }
        );
        return true;
    }
}
