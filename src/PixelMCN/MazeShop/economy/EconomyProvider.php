<?php

declare(strict_types=1);

namespace PixelMCN\MazeShop\economy;

use pocketmine\player\Player;

interface EconomyProvider {

    public function getBalance(Player $player): float;

    public function addMoney(Player $player, float $amount): bool;

    public function reduceMoney(Player $player, float $amount): bool;
}
