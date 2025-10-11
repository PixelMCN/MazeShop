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

namespace PixelMCN\MazeShop\database;

use mysqli;
use PixelMCN\MazeShop\Main;
use pocketmine\utils\Config;

class DatabaseManager {

    private Main $plugin;
    private ?mysqli $connection = null;
    private bool $enabled = false;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
        $this->initialize();
    }

    private function initialize(): void {
        $config = $this->plugin->getConfig();
        $dbConfig = $config->get("database");

        if (!is_array($dbConfig) || !isset($dbConfig["enabled"]) || !$dbConfig["enabled"]) {
            $this->plugin->getLogger()->info("Database sync is disabled in config.");
            return;
        }

        try {
            $this->connection = new mysqli(
                $dbConfig["host"] ?? "localhost",
                $dbConfig["username"] ?? "root",
                $dbConfig["password"] ?? "",
                $dbConfig["database"] ?? "mazeshop",
                $dbConfig["port"] ?? 3306
            );

            if ($this->connection->connect_error) {
                $this->plugin->getLogger()->error("Failed to connect to MySQL: " . $this->connection->connect_error);
                return;
            }

            $this->enabled = true;
            $this->createTables();
            $this->startSyncTask();
            $this->plugin->getLogger()->info("Connected to MySQL database successfully!");

        } catch (\Exception $e) {
            $this->plugin->getLogger()->error("Database connection error: " . $e->getMessage());
        }
    }

    private function createTables(): void {
        if (!$this->enabled || $this->connection === null) {
            return;
        }

        $queries = [
            "CREATE TABLE IF NOT EXISTS mazeshop_categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) UNIQUE NOT NULL,
                display_name VARCHAR(255) NOT NULL,
                icon VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS mazeshop_subcategories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                category_name VARCHAR(100) NOT NULL,
                name VARCHAR(100) NOT NULL,
                display_name VARCHAR(255) NOT NULL,
                icon VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_subcategory (category_name, name),
                FOREIGN KEY (category_name) REFERENCES mazeshop_categories(name) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS mazeshop_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                category_name VARCHAR(100) NOT NULL,
                subcategory_name VARCHAR(100) NOT NULL,
                item_id VARCHAR(255) NOT NULL,
                meta INT DEFAULT 0,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                buy_price DOUBLE NOT NULL,
                sell_price DOUBLE NOT NULL,
                amount INT DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_item (category_name, subcategory_name, item_id, meta)
            )"
        ];

        foreach ($queries as $query) {
            if (!$this->connection->query($query)) {
                $this->plugin->getLogger()->error("Failed to create table: " . $this->connection->error);
            }
        }
    }

    private function startSyncTask(): void {
        if (!$this->enabled) {
            return;
        }

        $config = $this->plugin->getConfig();
        $dbConfig = $config->get("database");
        $syncInterval = is_array($dbConfig) ? ($dbConfig["sync-interval"] ?? 300) : 300;

        $this->plugin->getScheduler()->scheduleRepeatingTask(
            new DatabaseSyncTask($this->plugin),
            $syncInterval * 20 // Convert seconds to ticks
        );
    }

    public function syncShopData(): void {
        if (!$this->enabled || $this->connection === null) {
            return;
        }

        // Sync shop data from database to local shop.yml
        $shopData = $this->getShopDataFromDatabase();
        if ($shopData !== null) {
            $shopFile = $this->plugin->getDataFolder() . "shop.yml";
            $config = new Config($shopFile, Config::YAML);
            $config->set("categories", $shopData);
            $config->save();
            $this->plugin->getShopManager()->reload();
        }
    }

    private function getShopDataFromDatabase(): ?array {
        if (!$this->enabled || $this->connection === null) {
            return null;
        }

        $categories = [];

        // Get all categories
        $result = $this->connection->query("SELECT * FROM mazeshop_categories");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $categoryName = $row["name"];
                $categories[$categoryName] = [
                    "display-name" => $row["display_name"],
                    "icon" => $row["icon"],
                    "subcategories" => []
                ];

                // Get subcategories for this category
                $subResult = $this->connection->query(
                    "SELECT * FROM mazeshop_subcategories WHERE category_name = '" . 
                    $this->connection->real_escape_string($categoryName) . "'"
                );

                if ($subResult) {
                    while ($subRow = $subResult->fetch_assoc()) {
                        $subName = $subRow["name"];
                        $categories[$categoryName]["subcategories"][$subName] = [
                            "display-name" => $subRow["display_name"],
                            "icon" => $subRow["icon"],
                            "items" => []
                        ];

                        // Get items for this subcategory
                        $itemResult = $this->connection->query(
                            "SELECT * FROM mazeshop_items WHERE category_name = '" . 
                            $this->connection->real_escape_string($categoryName) . 
                            "' AND subcategory_name = '" . 
                            $this->connection->real_escape_string($subName) . "'"
                        );

                        if ($itemResult) {
                            while ($itemRow = $itemResult->fetch_assoc()) {
                                $categories[$categoryName]["subcategories"][$subName]["items"][] = [
                                    "id" => $itemRow["item_id"],
                                    "meta" => (int) $itemRow["meta"],
                                    "name" => $itemRow["name"],
                                    "description" => $itemRow["description"],
                                    "buy-price" => (float) $itemRow["buy_price"],
                                    "sell-price" => (float) $itemRow["sell_price"],
                                    "amount" => (int) $itemRow["amount"]
                                ];
                            }
                        }
                    }
                }
            }
        }

        return $categories;
    }

    public function saveShopDataToDatabase(array $shopData): void {
        if (!$this->enabled || $this->connection === null) {
            return;
        }

        // Clear existing data
        $this->connection->query("DELETE FROM mazeshop_items");
        $this->connection->query("DELETE FROM mazeshop_subcategories");
        $this->connection->query("DELETE FROM mazeshop_categories");

        // Insert new data
        foreach ($shopData as $categoryName => $categoryData) {
            $stmt = $this->connection->prepare(
                "INSERT INTO mazeshop_categories (name, display_name, icon) VALUES (?, ?, ?)"
            );
            $stmt->bind_param("sss", $categoryName, $categoryData["display-name"], $categoryData["icon"]);
            $stmt->execute();

            if (isset($categoryData["subcategories"])) {
                foreach ($categoryData["subcategories"] as $subName => $subData) {
                    $stmt = $this->connection->prepare(
                        "INSERT INTO mazeshop_subcategories (category_name, name, display_name, icon) VALUES (?, ?, ?, ?)"
                    );
                    $stmt->bind_param("ssss", $categoryName, $subName, $subData["display-name"], $subData["icon"]);
                    $stmt->execute();

                    if (isset($subData["items"])) {
                        foreach ($subData["items"] as $item) {
                            $stmt = $this->connection->prepare(
                                "INSERT INTO mazeshop_items (category_name, subcategory_name, item_id, meta, name, description, buy_price, sell_price, amount) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
                            );
                            $stmt->bind_param(
                                "sssissddi",
                                $categoryName,
                                $subName,
                                $item["id"],
                                $item["meta"],
                                $item["name"],
                                $item["description"],
                                $item["buy-price"],
                                $item["sell-price"],
                                $item["amount"]
                            );
                            $stmt->execute();
                        }
                    }
                }
            }
        }
    }

    public function isEnabled(): bool {
        return $this->enabled;
    }

    public function close(): void {
        if ($this->connection !== null) {
            $this->connection->close();
        }
    }
}
