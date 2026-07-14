<?php
session_start();

require_once '../../../vendor/autoload.php';

auth_required();

$db = Database::connect();
$nominals = $db->query("SELECT amount FROM purchase_nominals WHERE is_active = 1 ORDER BY amount ASC")->fetchAll(PDO::FETCH_COLUMN);
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<!-- Tom Select CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<style>
    .ts-control { border-radius: var(--radius); padding: 0.75rem 1rem; border-color: var(--border-color); }
    .ts-dropdown { border-radius: var(--radius); }
    .ts-dropdown .option { padding: 0.75rem 1rem; }
    .ts-dropdown .option strong { color: var(--text-main); font-weight: 600; }
    .ts-dropdown .option small { color: var(--text-muted); font-size: 0.85rem; }
    
    .nominal-chip {
        display: inline-block;
        padding: 0.5rem 1rem;
        background: #f1f5f9;
        color: var(--text-main);
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid transparent;
        user-select: none;
    }
    .nominal-chip:hover {
        background: #e2e8f0;
    }
    .nominal-chip.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }
</style>

<div style="max-width: 500px; width: 100%;">
    <div style="margin-bottom: 1.5rem;">
        <h2 style="margin: 0;">Tambah <?= CURRENCY_NAME ?></h2>
    </div>

    <div class="card" style="padding: 1.5rem;">
        <form action="earn_store.php" method="POST" id="earn-form">
            <label style="font-weight: 500; color: var(--text-main); margin-bottom: 0.5rem; display: block;">Cari & Pilih Customer</label>
            <select name="customer_id" id="customer-select" required placeholder="Ketik nama atau no HP customer..."></select>

            <div style="margin-top: 2rem;">
                <label style="font-weight: 500; color: var(--text-main); margin-bottom: 0.75rem; display: block;">Pilih Nominal Cepat (Rp)</label>
                <?php if ($nominals): ?>
                    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
                        <?php foreach ($nominals as $nom): ?>
                            <?php 
                                $amount = (float) $nom;
                                $label = ($amount >= 1000 && $amount % 1000 == 0) ? ($amount / 1000) . 'K' : number_format($amount, 0, ',', '.');
                            ?>
                            <div class="nominal-chip nominal-btn" onclick="selectNominal(this, <?= $nom ?>)">
                                <?= htmlspecialchars($label) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning" style="margin-bottom: 1.5rem;">Belum ada nominal harga aktif. Silakan atur di Master Nominal.</div>
                <?php endif; ?>
            </div>
            
            <input type="hidden" name="amount" id="amount" required>

            <div style="margin-top: 1rem; padding-top: 1.5rem; border-top: 1px dashed var(--border-color);">
                <label style="font-weight: 500; color: var(--text-main); margin-bottom: 0.75rem; display: block;">Jumlah <?= CURRENCY_NAME ?></label>
                <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem;">
                    <div class="nominal-chip active" style="cursor: default;">
                        +1 <?= CURRENCY_NAME ?>
                    </div>
                </div>
            </div>
            
            <input type="hidden" name="point" id="point" value="1">
            
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 0.85rem; font-size: 1.05rem;">
                Simpan <?= CURRENCY_NAME ?>
            </button>
        </form>
    </div>
</div>

<script>
    const customerDropdown = new TomSelect('#customer-select', {
        valueField: 'id',
        labelField: 'name',
        searchField: ['name', 'phone'],
        load: function(query, callback) {
            if (!query.length) return callback();
            fetch(`api_customers.php?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(json => {
                    callback(json);
                }).catch(()=>{
                    callback();
                });
        },
        render: {
            option: function(item, escape) {
                return `<div>
                    <strong>${escape(item.name)}</strong><br>
                    <small>${escape(item.phone)}</small>
                </div>`;
            },
            item: function(item, escape) {
                return `<div><strong>${escape(item.name)}</strong> (${escape(item.phone)})</div>`;
            }
        }
    });

    // Auto-focus pada pencarian saat halaman dimuat
    customerDropdown.focus();

    function selectNominal(element, amount) {
        document.querySelectorAll('.nominal-btn').forEach(el => el.classList.remove('active'));
        element.classList.add('active');
        document.getElementById('amount').value = amount;
    }
    
    document.getElementById('earn-form').addEventListener('submit', function(e) {
        const amount = document.getElementById('amount').value;
        if (!amount) {
            alert('Silakan pilih nominal belanja terlebih dahulu!');
            e.preventDefault();
        }
    });
</script>

<?php include '../../../resources/views/layouts/footer.php'; ?>