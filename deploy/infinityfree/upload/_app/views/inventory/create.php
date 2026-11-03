<!-- views/inventory/create.php -->
<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <div class="card-title"><i class="fa fa-boxes-stacked"></i> Register New Inventory Item</div>
    </div>
    <div class="card-body">
        <form action="index.php?route=inventory-create" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="barangay_id" value="1">

            <div class="form-group">
                <label class="form-label">Supply Category *</label>
                <select name="supply_type" class="form-control" required>
                    <option value="Relief Goods">Relief Goods (Food Packs, Rice, Canned)</option>
                    <option value="Medical Supplies">Medical Supplies (Trauma Kits, Medicine)</option>
                    <option value="Rescue Equipment">Rescue Equipment (Boats, Life Vests, Searchlights)</option>
                    <option value="Clean Water">Clean Water (Drinking Jugs)</option>
                    <option value="Hygiene Kits">Hygiene & Sanitation Kits</option>
                    <option value="Shelter Kits">Shelter & Bedding Kits</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Supply Name / Description *</label>
                <input type="text" name="supply_name" class="form-control" placeholder="e.g. Standard Family Food Pack" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label class="form-label">Initial Quantity *</label>
                    <input type="number" name="supply_quantity" class="form-control" min="1" value="100" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Unit of Measure</label>
                    <input type="text" name="unit_of_measure" class="form-control" placeholder="packs, kits, units, jugs" value="packs">
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Item</button>
                <a href="index.php?route=inventory" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
