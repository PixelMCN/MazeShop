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

namespace PixelMCN\MazeShop\shop;

class Category {

    private string $name;
    private string $displayName;
    private string $icon;
    private array $subCategories = [];

    public function __construct(string $name, string $displayName, string $icon, array $subCategoriesData) {
        $this->name = $name;
        $this->displayName = $displayName;
        $this->icon = $icon;

        foreach ($subCategoriesData as $subName => $subData) {
            $this->subCategories[$subName] = new SubCategory(
                $subName,
                $subData["display-name"] ?? $subName,
                $subData["icon"] ?? "minecraft:chest",
                $subData["items"] ?? []
            );
        }
    }

    public function getName(): string {
        return $this->name;
    }

    public function getDisplayName(): string {
        return $this->displayName;
    }

    public function getIcon(): string {
        return $this->icon;
    }

    public function getSubCategories(): array {
        return $this->subCategories;
    }

    public function getSubCategory(string $name): ?SubCategory {
        return $this->subCategories[$name] ?? null;
    }

    public function hasSubCategory(string $name): bool {
        return isset($this->subCategories[$name]);
    }

    public function addSubCategory(SubCategory $subCategory): void {
        $this->subCategories[$subCategory->getName()] = $subCategory;
    }

    public function removeSubCategory(string $name): bool {
        if (isset($this->subCategories[$name])) {
            unset($this->subCategories[$name]);
            return true;
        }
        return false;
    }

    public function toArray(): array {
        $data = [
            "display-name" => $this->displayName,
            "icon" => $this->icon,
            "subcategories" => []
        ];

        foreach ($this->subCategories as $subName => $subCategory) {
            $data["subcategories"][$subName] = $subCategory->toArray();
        }

        return $data;
    }
}
