# 🛒 MazeShop - Shop Plugin for PocketMine-MP

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![API](https://img.shields.io/badge/PocketMine--MP-5.0.0+-green.svg)
![PHP](https://img.shields.io/badge/PHP-8.4+-purple.svg)

A feature-rich shop plugin for PocketMine-MP servers with beautiful form-based UI and full MazePay integration.

**Authors:** Pixelis0P & MazecraftMCN Team

---

## 📋 Table of Contents
- [Features](#-features)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Commands](#-commands)
- [Permissions](#-permissions)
- [Configuration](#%EF%B8%8F-configuration)
- [Shop Configuration](#-shop-configuration)
- [FAQ](#-faq)
- [Support](#-support)

---

## ✨ Features

### 🛍️ **Shop System**
- Beautiful form-based GUI for easy shopping
- Category-based organization (Wood, Blocks, Food, Tools, Armor, Potions, etc.)
- Buy and sell items with customizable prices
- Custom images for categories and items

### 💰 **Selling System**
- `/sell <amount>` - Sell specific amount from hand
- `/sell invall` - Sell all matching items from inventory
- Confirmation dialog for bulk sales
- Instant price calculation

### 👑 **Admin Controls**
- `/shop disable` - Disable shop for non-OPs
- `/shop enable` - Re-enable shop access
- Operators can always access shop even when disabled

### 🔗 **MazePay Integration**
- Full integration with MazePay economy plugin
- Automatic wallet balance checking
- Seamless money transactions
- Uses MazePay's currency formatting

### 🎨 **Highly Customizable**
- Two separate config files (`config.yml` and `shop.yml`)
- Customize all messages, colors, and prefixes
- Add/remove/modify categories and items
- Set custom prices for buying and selling
- Add custom button images (URLs)
- Version control for easy updates

---

## 📦 Requirements

- **PocketMine-MP:** 5.0.0 or higher
- **PHP:** 8.4 or higher
- **MazePay:** Required for economy system

---

## 📥 Installation

1. **Install MazePay** first (if not already installed)
2. **Download** the latest `MazeShop.phar` from [Releases](https://github.com/Pixelis0P/MazeShop/releases)
3. **Place** the `.phar` file in your server's `plugins/` folder
4. **Restart** your server
5. **Configure** the plugin by editing:
   - `plugins/MazeShop/config.yml` (Messages and settings)
   - `plugins/MazeShop/shop.yml` (Items and prices)
6. **Restart** again to apply changes
7. **Enjoy!** 🎉

---

## 📝 Commands

### 👤 Player Commands
| Command | Description | Usage |
|---------|-------------|-------|
| `/shop` | Open shop category list | `/shop` |
| `/shop <category>` | Open specific category | `/shop wood` |
| `/sell <amount>` | Sell items from hand | `/sell 64` |
| `/sell invall` | Sell all matching items | `/sell invall` |

### 👑 Admin Commands
| Command | Description | Permission |
|---------|-------------|------------|
| `/shop disable` | Disable shop for players | `mazeshop.command.shop.admin` |
| `/shop enable` | Enable shop for players | `mazeshop.command.shop.admin` |
| `/shopadmin` | Open shop management GUI | `mazeshop.command.admin` |

**Shop Management Features:**
- **Create/Delete/Edit Categories** - Full category management
- **Add/Remove/Edit Items** - Manage items in each category
- **Edit Prices** - Change buy/sell prices on the fly
- **Custom Images** - Set image URLs for categories and items
- **Real-time Updates** - Changes saved instantly to shop.yml

---

## 🔐 Permissions

```yaml
mazeshop.command.shop         # Use /shop command (default: true)
mazeshop.command.shop.admin   # Admin shop controls (default: op)
mazeshop.command.sell         # Use /sell command (default: true)
mazeshop.command.admin        # Use /shopadmin for shop management (default: op)
```

---

## ⚙️ Configuration

### **config.yml** - General Settings & Messages

```yaml
# Plugin Settings
prefix: "§b[MazeShop]§r "
shop-enabled: true

# Messages
messages:
  no-permission: "§cYou don't have permission!"
  shop-disabled: "§cThe shop is currently disabled!"
  buy-success: "§aYou bought §e{amount}x {item} §afor §e{price}§a!"
  sell-success: "§aYou sold §e{amount}x {item} §afor §e{price}§a!"
  # ... and many more customizable messages
```

**Tip:** All messages support Minecraft color codes (§)!

---

## 🏪 Shop Configuration

### **shop.yml** - Items & Prices

The `shop.yml` file controls all shop categories and items:

```yaml
categories:
  - name: "Wood"
    icon: "oak_log"
    image: "https://i.imgur.com/wood-icon.png"
    items:
      - item: "oak_log"
        buy_price: 5.0
        sell_price: 2.5
        image: ""
      - item: "spruce_log"
        buy_price: 5.0
        sell_price: 2.5
        image: ""
```

### **Category Properties:**
- `name` - Display name of the category
- `icon` - Item to represent the category
- `image` - URL to button image (optional)
- `items` - List of items in this category

### **Item Properties:**
- `item` - Item ID (use PocketMine item names)
- `buy_price` - Price players pay to buy
- `sell_price` - Price players receive when selling
- `image` - URL to item image (optional)

### **Adding New Categories:**

```yaml
- name: "Custom Category"
  icon: "diamond"
  image: ""
  items:
    - item: "diamond"
      buy_price: 100.0
      sell_price: 50.0
      image: ""
```

### **Adding New Items:**

Just add to the `items:` list under any category:

```yaml
- item: "emerald"
  buy_price: 150.0
  sell_price: 75.0
  image: "https://i.imgur.com/emerald.png"
```

---

## 🎮 How to Use

### **For Players:**

1. **Open Shop:**
   ```
   /shop
   ```
   Browse categories and select items to buy/sell

2. **Open Specific Category:**
   ```
   /shop wood
   /shop food
   /shop tools
   ```

3. **Sell Items from Hand:**
   ```
   /sell 32    # Sells 32 items
   /sell 64    # Sells 64 items
   ```

4. **Sell All Matching Items:**
   ```
   /sell invall
   ```
   Opens confirmation dialog showing total items and price

### **For Admins:**

1. **Disable Shop:**
   ```
   /shop disable
   ```
   Players cannot access shop (OPs still can)

2. **Enable Shop:**
   ```
   /shop enable
   ```
   Re-enable shop access for everyone

3. **Manage Shop (GUI):**
   ```
   /shopadmin
   ```
   Opens the shop management interface where you can:
   - **Create new categories** with custom icons and images
   - **Edit existing categories** (rename, change icon, update image)
   - **Delete categories** (removes all items inside)
   - **Add items to categories** with buy/sell prices
   - **Edit item prices** and images
   - **Delete items** from categories
   
   All changes are saved instantly to `shop.yml`!

---

## ❓ FAQ

### **Q: Does this require MazePay?**
**A:** Yes! MazePay is required for the economy system. MazeShop integrates directly with MazePay's wallet system.

### **Q: Can I add custom items?**
**A:** Yes! You can either edit `shop.yml` manually or use the `/shopadmin` command for a user-friendly GUI to add items. Use PocketMine's item names (e.g., `diamond_sword`, `cooked_beef`, etc.)

### **Q: How do I change prices?**
**A:** You can either edit `shop.yml` manually or use `/shopadmin` GUI → Select category → Manage Items → Select item → Edit Prices.

### **Q: Can players sell items not in the shop?**
**A:** No, only items configured in `shop.yml` with a `sell_price > 0` can be sold.

### **Q: What happens if a player tries to buy with insufficient funds?**
**A:** They receive an error message showing how much money they need.

### **Q: Can I use custom images for buttons?**
**A:** Yes! Add image URLs in the `image` field for categories or items in `shop.yml`. Leave empty `""` for text-only buttons.

### **Q: Does /sell invall work with enchanted items?**
**A:** Yes, it counts all items of the same type regardless of enchantments (when using `equals` with ignoreNBT).

### **Q: How do I remove a category?**
**A:** Use `/shopadmin` → Select the category → Delete Category. Or manually delete it from `shop.yml` and restart.

---

## 📊 File Structure

```
MazeShop/
├── plugin.yml
├── resources/
│   ├── config.yml
│   └── shop.yml
└── src/
    └── Pixelis0P/
        └── MazeShop/
            ├── MazeShop.php
            ├── commands/
            │   ├── ShopCommand.php
            │   ├── SellCommand.php
            │   └── ShopAdminCommand.php
            ├── forms/
            │   ├── CategoryListForm.php
            │   ├── ShopForm.php
            │   ├── BuySellForm.php
            │   ├── SellConfirmForm.php
            │   └── admin/
            │       ├── CategoryManageForm.php
            │       ├── CategoryCreateForm.php
            │       ├── CategoryEditForm.php
            │       ├── CategoryEditInfoForm.php
            │       ├── CategoryDeleteForm.php
            │       ├── ItemManageForm.php
            │       ├── ItemCreateForm.php
            │       ├── ItemEditForm.php
            │       ├── ItemEditPriceForm.php
            │       └── ItemDeleteForm.php
            └── utils/
                └── ItemUtils.php
```

---

## 📜 Example Usage

### Player Shopping Experience:
```
Player: /shop
→ Opens category list (Wood, Blocks, Food, Tools, Armor, Potions)

Player: Clicks "Wood"
→ Shows all wood items with prices

Player: Clicks "Oak Log"
→ Form with Buy/Sell options and amount input

Player: Selects "Buy", enters "64"
→ Buys 64 oak logs for $320.00
→ Money deducted from wallet
→ Items added to inventory
```

### Selling Items:
```
Player: Holds dirt, types /sell 64
→ Opens confirmation: "Sell 64x Dirt for $16.00?"
→ Player clicks "Yes"
→ 64 dirt removed from inventory
→ $16.00 added to wallet

Player: Types /sell invall
→ Counts all dirt in inventory (e.g., 320 total)
→ "Sell 320x Dirt for $80.00?"
→ Confirms and sells all
```

---

## 🐛 Known Issues

None at the moment! Report bugs on [GitHub Issues](https://github.com/Pixelis0P/MazeShop/issues).

---

## 🤝 Support

Need help? Found a bug? Have a suggestion?

- **GitHub Issues:** [Report Issues](https://github.com/Pixelis0P/MazeShop/issues)
- **Discord:** [Join our Discord](#)
- **Wiki:** [Read the Wiki](https://github.com/Pixelis0P/MazeShop/wiki)

---

## 📄 License

This project is licensed under the **MIT License**.

---

## 🌟 Contributing

Contributions are welcome! Please submit Pull Requests.

---

## 💖 Credits

**Developed by:**
- **Pixelis0P** - Lead Developer
- **MazecraftMCN Team** - Development Team

**Special Thanks:**
- MazePay for economy integration
- PocketMine-MP Team

---

<div align="center">

### ⭐ If you like MazeShop, please star the repository!

**Made with ❤️ by Pixelis0P & MazecraftMCN Team**

[⬆ Back to Top](#-mazeshop---shop-plugin-for-pocketmine-mp)

</div>