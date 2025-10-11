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
use PixelMCN\MazePay\MazePay;

class MazePayProvider implements EconomyProvider {

    private Plugin $plugin;

    public function __construct(Plugin $plugin) {
        $this->plugin = $plugin;
    }

    public function getBalance(Player $player): float {
        try {
            $economy = MazePay::getInstance()->getEconomyManager();
            if ($economy->accountExists($player->getName())) {
                $account = $economy->getAccount($player->getName());
                return (float) $account->getWallet();
            }
        } catch (\Exception $e) {
            return 0.0;
        }
        return 0.0;
    }

    public function addMoney(Player $player, float $amount): bool {
        try {
            $economy = MazePay::getInstance()->getEconomyManager();
            return $economy->addMoney($player->getName(), $amount, "wallet");
        } catch (\Exception $e) {
            return false;
        }
    }

    public function reduceMoney(Player $player, float $amount): bool {
        try {
            $economy = MazePay::getInstance()->getEconomyManager();
            return $economy->removeMoney($player->getName(), $amount, "wallet");
        } catch (\Exception $e) {
            return false;
        }
    }
}
