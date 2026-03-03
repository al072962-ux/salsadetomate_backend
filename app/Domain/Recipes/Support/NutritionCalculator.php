<?php

namespace App\Domain\Recipes\Support;

class NutritionCalculator
{
    /**
     * @param array<int, array<string, mixed>> $ingredients
     * @return array<string, float>
     */
    public function calculate(array $ingredients): array
    {
        $totals = [
            'calories' => 0.0,
            'protein_g' => 0.0,
            'carbs_g' => 0.0,
            'fat_g' => 0.0,
        ];

        foreach ($ingredients as $ingredient) {
            $totals['calories'] += (float) ($ingredient['calories'] ?? 0);
            $totals['protein_g'] += (float) ($ingredient['protein_g'] ?? 0);
            $totals['carbs_g'] += (float) ($ingredient['carbs_g'] ?? 0);
            $totals['fat_g'] += (float) ($ingredient['fat_g'] ?? 0);
        }

        return array_map(static fn (float $value): float => round($value, 2), $totals);
    }
}
