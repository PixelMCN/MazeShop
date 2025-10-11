# Changelog

All notable changes to MazeShop will be documented in this file.

## [1.0.1] - 2025-10-11

### Fixed
- Fixed undefined variable bug in ShopFormGUI line 225 (`$item` → `$this->item`)
- Fixed MazePayProvider to use correct MazePay API methods
- Added proper error handling in economy providers
- Fixed `GRAY_STAINED_GLASS_PANE` crash - replaced with compatible items (dyes and vanilla items)
- Fixed `LIME_STAINED_GLASS_PANE` and `RED_STAINED_GLASS_PANE` compatibility issues
- Fixed `ORANGE_STAINED_GLASS_PANE` in auction GUI

### Changed
- **Switched to InvMenu library for Chest GUIs** - More robust and feature-rich!
- Removed custom inventory system in favor of InvMenu
- InvMenu is now a required dependency (add to plugin.yml depend)
- Removed commands section from plugin.yml (commands now registered programmatically)
- Plugin now auto-disables if MazePay or BedrockEconomy is not found
- Improved error messages for missing dependencies

### Added
- **Built-in beautiful Chest GUI** - No external plugins needed! 
  - Custom inventory system with click handlers
  - Beautiful bordered layouts with glass panes
  - Smooth navigation with back/close buttons
  - Color-coded buy/sell options (green/red glass panes)
  - Pagination support for large catalogs
  - Works for both Shop and Auction systems
- **Organized GUI directory structure**
  - `gui/forms/` - Forms GUI implementations
  - `gui/chest/` - Chest GUI implementations  
  - `gui/inventory/` - Custom inventory system
  - Clear namespace separation for better code organization
- Help commands for `/shop help` and `/auction help`
- Event listener system for chest menu interactions
- Better troubleshooting documentation
- Auto-detection for economy plugins with proper error messages

## [1.0.0] - 2025-10-11

### Added
- Multi-level shop system (Category → Sub-category → Items)
- Dual GUI support (Forms and Chest GUI, configurable)
- MySQL database synchronization for cross-server shops
- Auction house with real-time bidding
- Economy integration (MazePay and BedrockEconomy)
- Custom blocks support
- Comprehensive admin commands for shop management
- Player commands for buying, selling, and auctions
- Public API with custom events
- Fully customizable messages and configurations
- Database sync with configurable intervals
- Auction expiration tracking and automatic ending
- Permission-based access control
- Complete README with examples and documentation

### Features
- ItemPurchaseEvent, ItemSellEvent for shop transactions
- AuctionCreateEvent, AuctionBidEvent, AuctionEndEvent for auctions
- CustomBlockShopAddEvent for custom block integration
- Async database operations for performance
- Cached shop data in memory
- Auction fee system
- Maximum auctions per player limit
- Configurable currency symbol and name
- Search functionality for items
- Multiple sub-commands for shop and auction management

### Technical
- PocketMine-MP 5.0.0+ compatible
- PHP 8.4+ required
- Clean OOP architecture
- Singleton pattern for main instance
- Form-based GUI implementation
- MySQL database with proper foreign key relationships
- Scheduled tasks for database sync and auction checks
