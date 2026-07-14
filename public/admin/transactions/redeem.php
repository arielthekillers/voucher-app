<?php
session_start();

require_once '../../../vendor/autoload.php';

auth_required();

$db = Database::connect();

$customer_id = $_GET['customer_id'] ?? '';
$customer = null;

// Fetch customer by ID if provided
if ($customer_id) {
    $stmt = $db->prepare("
        SELECT c.*, 
        (
            COALESCE((SELECT SUM(point_amount) FROM transactions WHERE customer_id = c.id AND type = 'EARN'), 0) -
            COALESCE((SELECT SUM(point_amount) FROM transactions WHERE customer_id = c.id AND type = 'REDEEM'), 0)
        ) as current_points
        FROM customers c
        WHERE c.id = ?
    ");
    $stmt->execute([$customer_id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch active promos
$promos = $db->query("SELECT * FROM promos WHERE is_active = 1 ORDER BY point_cost ASC")->fetchAll();
?>

<?php include '../../../resources/views/layouts/header.php'; ?>
<?php include '../../../resources/views/layouts/sidebar.php'; ?>

<div style="max-width: 500px; width: 100%;">
    <div style="margin-bottom: 1.5rem;">
        <h2 style="margin: 0;">Redeem Promo</h2>
    </div>

    <!-- PENCARIAN CUSTOMER -->
    <div class="card" style="padding: 1.5rem; margin-bottom: 1rem;">
        <label style="font-weight: 500; color: var(--text-main); margin-bottom: 0.5rem; display: block;">Cari & Pilih Customer</label>
        <select id="customer-select" placeholder="Ketik nama atau no HP customer..."></select>
    </div>

    <?php if ($customer_id && !$customer): ?>
        <div class="alert alert-danger" style="background: #fee2e2; color: #b91c1c; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem;">
            Customer tidak ditemukan.
        </div>
    <?php endif; ?>

    <?php if ($customer): ?>
        <!-- DAFTAR PROMO UNTUK DIPILIH -->
        <div class="card" style="padding: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <div>
                    <h3 style="margin-bottom: 0.2rem; font-size: 1.15rem;"><?= htmlspecialchars($customer['name']) ?></h3>
                    <p style="color: var(--text-muted); margin-bottom: 0; font-size: 0.9rem;"><?= htmlspecialchars($customer['phone']) ?></p>
                </div>
                <div style="padding: 0.5rem 1rem; background: #eef2ff; color: var(--primary); border-radius: 20px; font-weight: 700;">
                    <?= number_format($customer['current_points']) ?> <?= CURRENCY_NAME ?>
                </div>
            </div>

            <form action="redeem_store.php" method="POST" onsubmit="return confirm('Proses Redeem promo ini? <?= CURRENCY_NAME ?> customer akan dipotong.')">
                <input type="hidden" name="customer_id" value="<?= $customer['id'] ?>">
                <?= csrf_field() ?>
                
                <label style="font-weight: 500; color: var(--text-main); margin-bottom: 0.75rem; display: block; padding-top: 1rem; border-top: 1px dashed var(--border-color);">
                    Pilih Promo yang Tersedia
                </label>
                
                <div class="promo-list" style="display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1.5rem;">
                    <?php foreach ($promos as $p): ?>
                        <?php 
                            $can_afford = $customer['current_points'] >= $p['point_cost'];
                            $opacity = $can_afford ? '1' : '0.5';
                            $cursor = $can_afford ? 'pointer' : 'not-allowed';
                        ?>
                        <label class="promo-card" style="cursor: <?= $cursor ?>; opacity: <?= $opacity ?>; position: relative; border: 1px solid var(--border-color); border-radius: var(--radius); overflow: hidden; display: flex;">
                            <input type="radio" name="promo_id" value="<?= $p['id'] ?>" style="position: absolute; opacity: 0;" required <?= !$can_afford ? 'disabled' : '' ?>>
                            
                            <?php if ($p['image']): ?>
                                <img src="<?= BASE_URL ?>/storage/uploads/promos/<?= $p['image'] ?>" alt="<?= htmlspecialchars($p['title']) ?>" style="width: 100px; height: 100px; object-fit: cover;">
                            <?php else: ?>
                                <div style="width: 100px; height: 100px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #9ca3af;">
                                    <i class='bx bx-image' style="font-size: 2.5rem;"></i>
                                </div>
                            <?php endif; ?>

                            <div class="promo-content" style="padding: 0.75rem 1rem; flex: 1; display: flex; flex-direction: column; justify-content: center;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.25rem;">
                                    <h4 style="font-size: 1rem; margin-bottom: 0; color: var(--text-main); font-weight: 600;"><?= htmlspecialchars($p['title']) ?></h4>
                                    <div style="font-weight: 700; color: var(--primary); white-space: nowrap; font-size: 0.95rem;">
                                        <?= number_format($p['point_cost']) ?> <?= CURRENCY_NAME ?>
                                    </div>
                                </div>
                                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0; line-height: 1.3;">
                                    <?= htmlspecialchars($p['description'] ?? '') ?>
                                </p>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>

                <button type="submit" class="btn btn-primary" id="submit-btn" disabled style="width: 100%; justify-content: center; padding: 0.85rem; font-size: 1.05rem;">
                    Proses Redeem
                </button>
            </form>
        </div>
    <?php endif; ?>
</div>

<!-- Tom Select CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<style>
    .ts-control { border-radius: var(--radius); padding: 0.75rem 1rem; border-color: var(--border-color); }
    .ts-dropdown { border-radius: var(--radius); }
    .ts-dropdown .option { padding: 0.75rem 1rem; }
    .ts-dropdown .option strong { color: var(--text-main); font-weight: 600; }
    .ts-dropdown .option small { color: var(--text-muted); font-size: 0.85rem; }

    /* Style for selected promo */
    input[type="radio"]:checked ~ .promo-content {
        background-color: #f5f3ff;
    }
    input[type="radio"]:checked ~ .promo-content h4 {
        color: var(--primary) !important;
    }
    input[type="radio"]:checked + img,
    input[type="radio"]:checked + div {
        border-right: 3px solid var(--primary);
    }
    .promo-card {
        transition: all 0.2s;
    }
    input[type="radio"]:checked ~ .promo-content::after {
        content: '\ecf9'; /* bx-check-circle */
        font-family: 'boxicons';
        position: absolute;
        top: 0.75rem;
        right: 1rem;
        font-size: 1.25rem;
        color: var(--primary);
    }
</style>

<script>
    const initCustId = <?= json_encode($customer_id) ?>;
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
        },
        onChange: function(value) {
            if (value && value != initCustId) {
                window.location.href = `redeem.php?customer_id=${value}`;
            }
        }
    });

    // If there's an initial customer, pre-fill the TomSelect display.
    <?php if ($customer): ?>
    customerDropdown.addOption({id: '<?= $customer['id'] ?>', name: '<?= addslashes($customer['name']) ?>', phone: '<?= addslashes($customer['phone']) ?>'});
    customerDropdown.setValue('<?= $customer['id'] ?>', true); // true = silent, don't trigger onChange
    <?php else: ?>
    // Auto-focus pada pencarian jika belum ada customer yang dipilih
    customerDropdown.focus();
    <?php endif; ?>

    // Enable submit button when a promo is selected
    const radios = document.querySelectorAll('input[name="promo_id"]');
    const submitBtn = document.getElementById('submit-btn');

    radios.forEach(radio => {
        radio.addEventListener('change', () => {
            submitBtn.disabled = false;
        });
    });
</script>

<?php include '../../../resources/views/layouts/footer.php'; ?>
