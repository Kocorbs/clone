<?php
// src/ML/DemandCalculator.php

namespace App\ML;

class DemandCalculator {
    public static function calculateNeed(string $priority, int $households, int $population, string $supplyType, int $availableStock): array {
        $households = max(1, $households);
        $population = max(1, $population);

        // Priority demand multipliers
        $multipliers = [
            'Urgent' => 1.10, // 100% + 10% safety buffer for immediate evacuation
            'High'   => 1.00, // 100% full coverage
            'Medium' => 0.75, // 75% coverage for prioritized vulnerable units
            'Low'    => 0.50  // 50% initial reserve deployment
        ];

        $mult = $multipliers[$priority] ?? 0.75;
        $calculatedDemand = 1;
        $formulaNote = "";

        switch ($supplyType) {
            case 'Relief Goods':
            case 'Clean Water':
                $calculatedDemand = (int)ceil(round($households * $mult, 4));
                $formulaNote = "1 pack per household × " . ($mult * 100) . "% priority allocation ({$households} households)";
                break;

            case 'Medical Supplies':
                // 1 First aid / trauma kit per 25 affected individuals
                $baseKits = ceil($population / 25.0);
                $calculatedDemand = (int)ceil(round($baseKits * $mult, 4));
                $formulaNote = "1 kit per 25 persons × " . ($mult * 100) . "% priority allocation ({$population} persons)";
                break;

            case 'Rescue Equipment':
                // Inflatable boats / life vests scaled by river proximity & population
                $baseUnits = ceil($population / 20.0);
                $calculatedDemand = (int)ceil(round($baseUnits * $mult, 4));
                $formulaNote = "Rescue gear scaled for {$population} persons × " . ($mult * 100) . "% priority allocation";
                break;

            case 'Hygiene Kits':
            case 'Shelter Kits':
                $calculatedDemand = (int)ceil(round(($households * 0.6) * $mult, 4));
                $formulaNote = "60% of households × " . ($mult * 100) . "% priority allocation ({$households} households)";
                break;

            default:
                $calculatedDemand = (int)ceil(round($households * $mult, 4));
                $formulaNote = "Standard ratio × " . ($mult * 100) . "% priority allocation";
                break;
        }

        $calculatedDemand = max(1, $calculatedDemand);

        // Inventory safety capping
        $isShortage = false;
        $recommendedQuantity = $calculatedDemand;

        if ($calculatedDemand > $availableStock) {
            $isShortage = true;
            $recommendedQuantity = max(0, $availableStock);
        }

        return [
            'priority' => $priority,
            'multiplier' => $mult,
            'calculated_demand' => $calculatedDemand,
            'available_stock' => $availableStock,
            'recommended_quantity' => $recommendedQuantity,
            'is_shortage' => $isShortage,
            'formula_note' => $formulaNote,
            'shortage_notice' => $isShortage 
                ? "Calculated demand is {$calculatedDemand} units, but remaining inventory is {$availableStock} units. Allocation is capped at available stock. ICDRRMO emergency replenishment is required."
                : "Inventory is sufficient to fulfill the full calculated allocation ({$calculatedDemand} units)."
        ];
    }
}
