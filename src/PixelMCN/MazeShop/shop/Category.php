<?php

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
