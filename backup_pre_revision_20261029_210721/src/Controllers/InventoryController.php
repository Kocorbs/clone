<?php
// src/Controllers/InventoryController.php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\View;
use App\Models\Supply;
use App\Models\Barangay;
use App\Models\RequestLog;

class InventoryController {
    public function index(): void {
        Auth::requireRole(['ICDRRMO', 'Barangay Head', 'Responder']);
        $supplies = Supply::all();
        $summary = Supply::getInventorySummary();
        $lowStock = Supply::getLowStock();

        View::render('inventory.index', [
            'title' => 'Resource & Inventory Management',
            'supplies' => $supplies,
            'summary' => $summary,
            'lowStock' => $lowStock
        ]);
    }

    public function create(): void {
        Auth::requireRole(['ICDRRMO']);
        $barangays = Barangay::all();

        if (Request::isPost()) {
            if (!Auth::validateCsrf(Request::post('csrf_token'))) {
                Auth::setFlash('danger', 'Invalid CSRF token.');
                header("Location: index.php?route=inventory-create");
                exit;
            }

            $name = trim(Request::post('supply_name', ''));
            $type = Request::post('supply_type', 'Relief Goods');
            $qty = (int)Request::post('supply_quantity', 0);
            $unit = trim(Request::post('unit_of_measure', 'units'));
            $bId = (int)Request::post('barangay_id', 1);

            if (empty($name) || $qty <= 0) {
                Auth::setFlash('warning', 'Please provide a valid supply name and initial quantity.');
                header("Location: index.php?route=inventory-create");
                exit;
            }

            Supply::create([
                'barangay_id' => $bId,
                'supply_type' => $type,
                'supply_name' => $name,
                'supply_quantity' => $qty,
                'unit_of_measure' => $unit
            ]);

            Auth::setFlash('success', "Added {$name} ({$qty} {$unit}) to centralized inventory.");
            header("Location: index.php?route=inventory");
            exit;
        }

        View::render('inventory.create', ['title' => 'Register Supply Item', 'barangays' => $barangays]);
    }

    public function replenish(): void {
        Auth::requireRole(['ICDRRMO', 'Barangay Head']);
        if (!Auth::validateCsrf(Request::post('csrf_token'))) {
            Auth::setFlash('danger', 'Security verification failed.');
            header("Location: index.php?route=inventory");
            exit;
        }

        $supplyId = (int)Request::post('supply_id');
        $qty = (int)Request::post('replenish_quantity', 0);

        if ($qty <= 0) {
            Auth::setFlash('warning', 'Replenishment quantity must be greater than zero.');
            header("Location: index.php?route=inventory");
            exit;
        }

        Supply::replenishStock($supplyId, $qty);

        $supply = Supply::findById($supplyId);
        RequestLog::create([
            'recommended_by' => 'ICDRRMO / LGU Logistics',
            'action_taken' => 'Stock Replenishment',
            'action_by' => Auth::user()['firstname'] . ' ' . Auth::user()['lastname'],
            'remarks' => "Replenished {$qty} {$supply['unit_of_measure']} of {$supply['supply_name']}. New remaining: {$supply['supply_remaining']}."
        ]);

        Auth::setFlash('success', "Stock replenished successfully (+{$qty}).");
        header("Location: index.php?route=inventory");
        exit;
    }

    public function exportCsv(): void {
        Auth::requireRole(['ICDRRMO', 'Barangay Head']);
        $supplies = Supply::all();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="hinaplanon_inventory_report_' . date('Ymd_His') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Supply ID', 'Barangay', 'Supply Type', 'Item Name', 'Total Stock', 'Remaining Stock', 'Released Quantity', 'Unit', 'Status', 'Last Updated']);

        foreach ($supplies as $s) {
            fputcsv($output, [
                $s['supply_id'],
                $s['barangay_name'],
                $s['supply_type'],
                $s['supply_name'],
                $s['supply_quantity'],
                $s['supply_remaining'],
                $s['total_release'],
                $s['unit_of_measure'],
                $s['supply_status'],
                $s['updated_at']
            ]);
        }
        fclose($output);
        exit;
    }
}
