<div align="center">

# 🛒 MazeShop

### Advanced Shop & Auction System for PocketMine-MP

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/PixelMCN/MazeShop/releases)
[![PocketMine-MP](https://img.shields.io/badge/PocketMine--MP-5.0.0+-orange.svg)](https://github.com/pmmp/PocketMine-MP)
[![PHP](https://img.shields.io/badge/PHP-8.4+-purple.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Downloads](https://img.shields.io/github/downloads/PixelMCN/MazeShop/total.svg)](https://github.com/PixelMCN/MazeShop/releases)
[![Stars](https://img.shields.io/github/stars/PixelMCN/MazeShop.svg)](https://github.com/PixelMCN/MazeShop/stargazers)

**Developed by PixelMCN** | [Discord](https://discord.gg/pixelmcn) | [Issues](https://github.com/PixelMCN/MazeShop/issues)

---

</div>

## ✨ Features

<details open>
<summary><b>🛒 Shop System</b></summary>
<br>
- ✅ **Multi-level structure** - Category → Sub-category → Items
- ✅ **Forms GUI** - Native Bedrock UI with image support
- ✅ **Database sync** - MySQL support for cross-server synchronization
- ✅ **Custom blocks** - Full support for custom blocks from other plugins
- ✅ **Manual editing** - Edit `shop.yml` directly or use admin commands
- ✅ **Direct access** - Use `/shop <category>` for instant navigation

</details>

<details open>
<summary><b>🏆 Auction House</b></summary>
<br>
- ✅ **Player auctions** - Configurable duration and starting bids
- ✅ **Real-time bidding** - Automatic refund system
- ✅ **Forms GUI** - Easy-to-use bidding interface
- ✅ **Expiration tracking** - Automatic auction ending
- ✅ **Anti-spam** - Configurable auction fees

</details>

<details open>
<summary><b>💰 Economy Integration</b></summary>
<br>
- ✅ **Auto-detection** - Supports MazePay or BedrockEconomy
- ✅ **No balance storage** - All transactions handled by economy plugins
- ✅ **Configurable currency** - Customize symbol and name

</details>

<details open>
<summary><b>🎨 Highly Customizable</b></summary>
<br>
- ✅ **messages.yml** - Custom messages, placeholders, and colors
- ✅ **config.yml** - Database, currency, auction settings
- ✅ **shop.yml** - Complete shop structure with 188+ items

</details>

<details open>
<summary><b>📱 Native Bedrock Forms</b></summary>
<br>
- ✅ **Native UI** - Bedrock's built-in form system
- ✅ **Image support** - Custom texture icons
- ✅ **Button-based** - Simple and intuitive navigation
- ✅ **Custom input** - Easy amount selection
- ✅ **Quick bidding** - Simple bid input

</details>

---

## 📦 Installation

```bash
# 1. Download latest release
wget https://github.com/PixelMCN/MazeShop/releases/latest/download/MazeShop.phar

# 2. Place in plugins folder
mv MazeShop.phar /path/to/server/plugins/

# 3. Install economy plugin (REQUIRED)
# - MazePay: https://github.com/PixelMCN/MazePay
# - BedrockEconomy: https://github.com/cooldogedev/BedrockEconomy

# 4. Restart server
restart
```

> **⚠️ Important:** MazeShop requires either **MazePay** or **BedrockEconomy** to function. The plugin will automatically disable if no economy plugin is detected.

---

## ⚙️ Configuration

<details>
<summary><b>config.yml</b> (Click to expand)</summary>

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

</details>

<details>
<summary><b>shop.yml</b> (Click to expand)</summary>

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

</details>

---

## 📜 Commands

<div align="center">

### 👤 Player Commands

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
| `/sell <amount\|all>` | Quick sell items | `mazeshop.use` |

### 🛠️ Admin Commands

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

</div>

---

## 🔐 Permissions

| Permission | Default | Description |
|------------|---------|-------------|
| `mazeshop.use` | `true` | Use shop commands |
| `mazeshop.admin` | `op` | Admin commands |
| `mazeshop.auction.use` | `true` | Use auction system |
| `mazeshop.auction.admin` | `op` | Auction admin commands |
| `mazeshop.category.edit` | `op` | Edit categories |

---

## 🔌 API & Events

<details>
<summary><b>Available Events</b> (Click to expand)</summary>

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

</details>

<details>
<summary><b>API Usage</b> (Click to expand)</summary>

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

</details>

---

## 🗄️ Database Structure

<details>
<summary><b>MySQL Tables</b> (Click to expand)</summary>

If MySQL sync is enabled, MazeShop creates these tables:

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

</details>

---

## 💬 Support & Links

<div align="center">

[![GitHub Issues](https://img.shields.io/badge/Issues-GitHub-red.svg?logo=github)](https://github.com/PixelMCN/MazeShop/issues)
[![Discord](https://img.shields.io/badge/Discord-Join%20Us-7289DA.svg?logo=discord)](https://discord.gg/pixelmcn)
[![Documentation](https://img.shields.io/badge/Docs-Wiki-blue.svg?logo=gitbook)](https://github.com/PixelMCN/MazeShop/wiki)

</div>

---

## 🚀 Quick Start

1. Start server with MazeShop installed
2. Player types `/shop` to open shop GUI
3. Browse categories and purchase items
4. Type `/auction` to access auction house
5. Hold item and type `/auction create 100 3600` to create 1-hour auction
6. Other players can bid using `/auction bid <id> <amount>`

### 🛠️ Admin Quick Start

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

---

## ❓ Troubleshooting

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

---

## ⚡ Performance

MazeShop is optimized for:
- ✅ Multiple servers with database sync
- ✅ Async database operations
- ✅ Cached shop data in memory
- ✅ Efficient auction expiration checking
- ✅ No unnecessary file I/O operations

---

<div align="center">

## ⭐ Star History

[![Star History Chart](https://api.star-history.com/svg?repos=PixelMCN/MazeShop&type=Date)](https://star-history.com/#PixelMCN/MazeShop&Date)

---

### 📝 License

This project is licensed under the **MIT License** - see the [LICENSE](LICENSE) file for details.

---

### 👨‍💻 Credits

**Developed with ❤️ by PixelMCN**

[![GitHub](https://img.shields.io/badge/GitHub-PixelMCN-181717.svg?logo=github)](https://github.com/PixelMCN)
[![Website](https://img.shields.io/badge/Website-pixelmcn.com-00ADD8.svg?logo=google-chrome)](https://pixelmcn.com)

---

**Thank you for using MazeShop!** 🎉

If you find this plugin useful, please consider:
- ⭐ **Starring** this repository
- 🐛 **Reporting** bugs and issues
- 💡 **Suggesting** new features
- 📢 **Sharing** with others

</div>
