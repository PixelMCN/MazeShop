# GUI Directory Structure

This directory contains all GUI-related implementations for MazeShop, organized by type.

## Directory Layout

```
gui/
├── forms/                  # Forms GUI implementations (Bedrock forms)
│   ├── ShopFormGUI.php     # Shop forms interface
│   └── AuctionFormGUI.php  # Auction forms interface
│
├── chest/                  # Chest GUI implementations (Inventory-based)
│   ├── ShopChestGUI.php    # Shop chest interface
│   └── AuctionChestGUI.php # Auction chest interface
│
└── inventory/              # Custom inventory system
    ├── ChestInventory.php  # Custom inventory class
    └── ChestMenuListener.php # Event handler for chest menus
```

## Namespaces

### Forms GUI
- **Namespace:** `PixelMCN\MazeShop\gui\forms`
- **Classes:**
  - `ShopFormGUI` - Handles shop forms interface
  - `AuctionFormGUI` - Handles auction forms interface

### Chest GUI (InvMenu-based)
- **Namespace:** `PixelMCN\MazeShop\gui\chest`
- **Classes:**
  - `ShopChestGUI` - Handles shop chest interface using InvMenu
  - `AuctionChestGUI` - Handles auction chest interface using InvMenu
- **Dependencies:**
  - InvMenu library (muqsit/invmenu)
  - Uses `InvMenuTypeIds::TYPE_DOUBLE_CHEST` and `InvMenuTypeIds::TYPE_CHEST`
  - Transaction handling via `InvMenuTransaction` and `InvMenuTransactionResult`

## Usage

### In Commands
```php
use PixelMCN\MazeShop\gui\forms\ShopFormGUI;
use PixelMCN\MazeShop\gui\chest\ShopChestGUI;

// Forms GUI
$gui = new ShopFormGUI($plugin);
$gui->sendMainMenu($player);

// Chest GUI (requires InvMenu)
$gui = new ShopChestGUI($plugin);
$gui->sendMainMenu($player);
```

### InvMenu Integration Example
```php
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\type\InvMenuTypeIds;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;

$menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
$menu->setName("Shop Name");

$menu->setListener(function(InvMenuTransaction $transaction): InvMenuTransactionResult {
    $player = $transaction->getPlayer();
    $slot = $transaction->getAction()->getSlot();
    
    // Handle click
    
    return $transaction->discard();
});

$menu->send($player);
```

### Configuration
Players can choose their preferred GUI type in `config.yml`:
```yaml
gui:
  shop-type: "form"      # or "chest"
  auction-type: "form"   # or "chest"
```

## Features

### Forms GUI
- Native Bedrock UI
- Image support
- Button-based navigation
- Custom forms for input
- Simple and lightweight

### Chest GUI
- Java Edition-like experience powered by InvMenu
- Visual item representation
- Color-coded actions (emeralds for buy, redstone for sell)
- Decorative borders with dyes
- Pagination support
- Requires InvMenu plugin
- Compatible with PM5 vanilla items
- Robust transaction handling

## Implementation Details

Both GUI types provide identical functionality:
- Browse categories and sub-categories
- View items with prices
- Buy/sell items
- Browse auctions
- Place bids
- Create auctions
- View personal auctions

The choice between forms and chest is purely aesthetic and based on player preference.
