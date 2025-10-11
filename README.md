# MazeShop - Advanced Shop & Auction System

**Version:** 1.0.1  
**Developer:** PixelMCN  
**PocketMine-MP:** 5.0.0+  
**PHP:** 8.4+

## Features

### 🛒 Shop System
- **Multi-level structure**: Category → Sub-category → Items
- **Forms GUI**: Native Bedrock UI with image support
- **Database sync**: MySQL support for cross-server shop synchronization
- **Custom blocks**: Full support for custom blocks from other plugins
- **Manual editing**: Edit `shop.yml` directly or use admin commands

### 🏆 Auction House
- Players can create auctions with configurable duration
- Real-time bidding system with automatic refunds
- Forms GUI for easy bidding
- Auction expiration tracking with automatic ending
- Auction fees to prevent spam

### 💰 Economy Integration
- **Auto-detection**: Automatically detects MazePay or BedrockEconomy
- **No balance storage**: All transactions handled by economy plugins
- **Configurable currency**: Customize currency symbol and name

### 🎨 Highly Customizable
- **messages.yml**: Fully customizable messages, placeholders, form text, and GUI images
- **config.yml**: Configure database, GUI types, currency, auction settings
- **shop.yml**: Complete shop structure with prices, descriptions, icons

### ✨ Native Bedrock Forms
- **Native UI** - Uses Bedrock's built-in form system
- **Image support** - Custom icons for categories and items
- **Button-based navigation** - Simple and intuitive
- **Custom input forms** - Easy amount selection for buying/selling
- **Quick bidding** - Simple bid input for auctions

## Installation

1. Download the latest release
2. Place `MazeShop.phar` in your `plugins` folder
3. Install either **[MazePay](https://github.com/PixelMCN/MazePay)** or **BedrockEconomy** economy plugin (REQUIRED)
4. (Optional) Configure MySQL in `config.yml` for cross-server sync
5. Restart your server

**Required Dependencies:**
- MazePay OR BedrockEconomy (for economy integration)

**Note:** The plugin will automatically disable if no economy plugin (MazePay or BedrockEconomy) is found.

## Configuration

### config.yml
```yaml
# MazeShop uses Forms GUI for all interfaces

currency:
  symbol: "$"
  name: "Money"

database:
  enabled: true          # Enable MySQL sync
  host: "localhost"
  port: 3306
  username: "root"
  password: ""
  database: "mazeshop"
  sync-interval: 300     # Seconds

auction:
  min-duration: 300      # 5 minutes
  max-duration: 86400    # 24 hours
  min-starting-bid: 1
  auction-fee: 5         # 5% fee
  max-auctions-per-player: 5
```

### shop.yml
```yaml
categories:
  Stone:
    display-name: "§aStone Materials"
    icon: "minecraft:stone"
    subcategories:
      "Cobble Stone":
        display-name: "§eCobblestone Items"
        icon: "minecraft:cobblestone"
        items:
          - id: "minecraft:cobblestone"
            meta: 0
            name: "§fCobblestone"
            description: "Basic building block"
            buy-price: 10
            sell-price: 5
            amount: 1
```

## Commands

### Player Commands
| Command | Description | Permission |
|---------|-------------|------------|
| `/shop` | Open main shop | `mazeshop.use` |
| `/shop category <name>` | Open specific category | `mazeshop.use` |
| `/shop buy <item> <amount>` | Quick buy item | `mazeshop.use` |
| `/shop sell <item> <amount>` | Quick sell item | `mazeshop.use` |
| `/shop help` | Display help | `mazeshop.use` |
| `/auction` | Open auction house | `mazeshop.auction.use` |
| `/auction list` | List active auctions | `mazeshop.auction.use` |
| `/auction bid <id> <amount>` | Place a bid | `mazeshop.auction.use` |
| `/auction create <startingBid> <duration>` | Create auction | `mazeshop.auction.use` |
| `/auction view <id>` | View auction details | `mazeshop.auction.use` |

### Admin Commands
| Command | Description | Permission |
|---------|-------------|------------|
| `/shopadmin addcategory <name>` | Add category | `mazeshop.admin` |
| `/shopadmin removecategory <name>` | Remove category | `mazeshop.admin` |
| `/shopadmin addsubcategory <cat> <name>` | Add sub-category | `mazeshop.admin` |
| `/shopadmin removesubcategory <cat> <name>` | Remove sub-category | `mazeshop.admin` |
| `/shopadmin additem <cat> <sub> <price> [sellprice]` | Add item (hold in hand) | `mazeshop.admin` |
| `/shopadmin removeitem <cat> <sub> <item>` | Remove item | `mazeshop.admin` |
| `/shopadmin reload` | Reload configs | `mazeshop.admin` |
| `/auctionadmin remove <id>` | Cancel auction | `mazeshop.auction.admin` |
| `/auctionadmin end <id>` | Force end auction | `mazeshop.auction.admin` |

## Permissions

```yaml
mazeshop.use: true                    # Use shop commands
mazeshop.admin: op                    # Admin commands
mazeshop.auction.use: true            # Use auction system
mazeshop.auction.admin: op            # Auction admin commands
mazeshop.category.edit: op            # Edit categories
```

## API & Events

MazeShop provides a comprehensive API for developers:

### Events
```php
use PixelMCN\MazeShop\event\ItemPurchaseEvent;
use PixelMCN\MazeShop\event\ItemSellEvent;
use PixelMCN\MazeShop\event\AuctionCreateEvent;
use PixelMCN\MazeShop\event\AuctionBidEvent;
use PixelMCN\MazeShop\event\AuctionEndEvent;
use PixelMCN\MazeShop\event\CustomBlockShopAddEvent;

// Example: Add bonus on purchase
public function onPurchase(ItemPurchaseEvent $event): void {
    $player = $event->getPlayer();
    $item = $event->getItem();
    $amount = $event->getAmount();
    
    // Add 10% discount
    $event->setTotalPrice($event->getTotalPrice() * 0.9);
}
```

### API Usage
```php
use PixelMCN\MazeShop\Main;

// Get plugin instance
$mazeShop = Main::getInstance();

// Access managers
$shopManager = $mazeShop->getShopManager();
$auctionManager = $mazeShop->getAuctionManager();
$economyManager = $mazeShop->getEconomyManager();

// Get shop data
$categories = $shopManager->getCategories();
$category = $shopManager->getCategory("Stone");

// Search for item
$itemData = $shopManager->searchItem("Diamond");

// Get auctions
$auctions = $auctionManager->getAllAuctions();
$playerAuctions = $auctionManager->getPlayerAuctions("PlayerName");
```

## Database Structure

If MySQL sync is enabled, MazeShop creates three tables:

### mazeshop_categories
- `id` - Auto-increment primary key
- `name` - Unique category name
- `display_name` - Display name
- `icon` - Icon texture path
- `created_at` - Timestamp

### mazeshop_subcategories
- `id` - Auto-increment primary key
- `category_name` - Foreign key to categories
- `name` - Sub-category name
- `display_name` - Display name
- `icon` - Icon texture path
- `created_at` - Timestamp

### mazeshop_items
- `id` - Auto-increment primary key
- `category_name` - Category reference
- `subcategory_name` - Sub-category reference
- `item_id` - Minecraft item ID
- `meta` - Item metadata
- `name` - Item display name
- `description` - Item description
- `buy_price` - Purchase price
- `sell_price` - Selling price
- `amount` - Item stack amount
- `created_at` / `updated_at` - Timestamps

## Support

- **Issues**: [GitHub Issues](https://github.com/PixelMCN/MazeShop/issues)
- **Discord**: [PixelMCN Discord](https://discord.gg/pixelmcn)

## Credits

**Developed by PixelMCN**

## License

See [LICENSE](LICENSE) file for details.

---

### Quick Start Example

1. Start server with MazeShop installed
2. Player types `/shop` to open shop GUI
3. Browse categories and purchase items
4. Type `/auction` to access auction house
5. Hold item and type `/auction create 100 3600` to create 1-hour auction
6. Other players can bid using `/auction bid <id> <amount>`

### Admin Quick Start

```bash
# Add a new category
/shopadmin addcategory Tools

# Add a sub-category
/shopadmin addsubcategory Tools Pickaxes

# Add an item (hold diamond pickaxe)
/shopadmin additem Tools Pickaxes 500 250

# Reload configuration
/shopadmin reload
```

## Troubleshooting

**Shop not opening?**
- Ensure economy plugin (MazePay or BedrockEconomy) is installed
- Check permissions: `mazeshop.use`

**Database sync not working?**
- Verify MySQL credentials in `config.yml`
- Check server logs for connection errors
- Ensure MySQL server is running and accessible

**Items not showing in shop?**
- Check `shop.yml` syntax (YAML is strict about indentation)
- Use `/shopadmin reload` after manual edits
- Review server logs for parsing errors

**Plugin disabled on startup?**
- Check if MazePay or BedrockEconomy is installed and enabled
- The plugin requires one of these economy plugins to function
- Check server logs for economy plugin errors

## Performance

MazeShop is optimized for:
- ✅ Multiple servers with database sync
- ✅ Async database operations
- ✅ Cached shop data in memory
- ✅ Efficient auction expiration checking
- ✅ No unnecessary file I/O operations

---

**Thank you for using MazeShop!** 🎉
