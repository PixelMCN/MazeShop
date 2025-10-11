<?php

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
