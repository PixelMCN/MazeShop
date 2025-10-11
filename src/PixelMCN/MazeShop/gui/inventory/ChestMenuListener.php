<?php

declare(strict_types=1);

namespace PixelMCN\MazeShop\gui\inventory;

use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\inventory\transaction\action\SlotChangeAction;

class ChestMenuListener implements Listener {

    public function onInventoryTransaction(InventoryTransactionEvent $event): void {
        $transaction = $event->getTransaction();
        $player = $transaction->getSource();

        foreach ($transaction->getActions() as $action) {
            if ($action instanceof SlotChangeAction) {
                $inventory = $action->getInventory();
                
                if ($inventory instanceof ChestInventory) {
                    $event->cancel();
                    
                    $clickHandler = $inventory->getClickHandler();
                    if ($clickHandler !== null) {
                        $clickHandler($player, $action->getSlot(), $action->getSourceItem());
                    }
                    
                    return;
                }
            }
        }
    }
}
