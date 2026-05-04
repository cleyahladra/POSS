<div class="page-toolbar">
    <form method="GET" action="" class="filter-form">
        <input type="hidden" name="url" value="reports/monthly">
        <div class="filter-group">
            <label>Year</label>
            <select name="year">
                <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                <option value="<?= $y ?>" <?= $y === $year ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> View</button>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h3>Monthly Sales — <?= $year ?></h3>
    </div>
    <div class="card-body p-0">
        <table class="table">
            <thead>
                <tr><th>Month</th><th>Transactions</th><th>Total Revenue</th><th>Bar</th></tr>
            </thead>
            <tbody>
                <?php
                $maxRevenue = max(array_column($monthly ?: [['total' => 1]], 'total') ?: [1]);
                $monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
                $monthData = [];
                foreach ($monthly as $m) $monthData[$m['month']] = $m;

                for ($m = 1; $m <= 12; $m++):
                    $row = $monthData[$m] ?? ['transactions' => 0, 'total' => 0, 'month_name' => $monthNames[$m-1]];
                    $pct = $maxRevenue > 0 ? ($row['total'] / $maxRevenue * 100) : 0;
                ?>
                <tr>
                    <td><?= $monthNames[$m-1] ?></td>
                    <td><?= (int)$row['transactions'] ?></td>
                    <td><strong>₱<?= number_format($row['total'], 2) ?></strong></td>
                    <td>
                        <div class="bar-track">
                            <div class="bar-fill" style="width:<?= $pct ?>%"></div>
                        </div>
                    </td>
                </tr>
                <?php endfor; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td><strong>TOTAL</strong></td>
                    <td><strong><?= array_sum(array_column($monthly, 'transactions')) ?></strong></td>
                    <td><strong>₱<?= number_format(array_sum(array_column($monthly, 'total')), 2) ?></strong></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
