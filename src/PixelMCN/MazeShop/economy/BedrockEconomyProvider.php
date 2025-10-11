<?php

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
