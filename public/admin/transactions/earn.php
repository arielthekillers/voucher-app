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
            <div style="display: flex; gap: 0.5rem; align-items: stretch;">
                <div style="flex: 1; min-width: 0;">
                    <select name="customer_id" id="customer-select" required placeholder="Ketik nama atau no HP customer..."></select>
                </div>
                <button type="button" class="btn btn-secondary" id="btn-scan-qr" style="width: 54px; padding: 0; border: 1px solid var(--border-color); background: #f8fafc; color: var(--text-main); border-radius: var(--radius); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                    <i class='bx bx-qr-scan' style="font-size: 1.5rem;"></i>
                </button>
            </div>

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

<!-- QR Scanner Modal -->
<div id="qr-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #fff; padding: 1.5rem; border-radius: 16px; width: 90%; max-width: 400px; text-align: center; position: relative;">
        <h3 style="margin-top: 0; margin-bottom: 1rem;">Scan QR Customer</h3>
        <div id="qr-reader" style="width: 100%; margin-bottom: 1rem; overflow: hidden; border-radius: 12px;"></div>
        <button type="button" id="btn-close-qr" class="btn" style="width: 100%; background: #e2e8f0; color: var(--text-main);">Batal</button>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
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

    // QR Code Scanner Logic
    let html5QrcodeScanner;
    
    document.getElementById('btn-scan-qr').addEventListener('click', function() {
        document.getElementById('qr-modal').style.display = 'flex';
        
        if (!html5QrcodeScanner) {
            html5QrcodeScanner = new Html5Qrcode("qr-reader");
        }
        
        html5QrcodeScanner.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            (decodedText, decodedResult) => {
                // Berhasil scan
                html5QrcodeScanner.stop().then(() => {
                    document.getElementById('qr-modal').style.display = 'none';
                    // Fetch data ke API
                    fetch(`api_customers.php?phone=${encodeURIComponent(decodedText)}`)
                        .then(response => response.json())
                        .then(json => {
                            if (json && json.length > 0) {
                                customerDropdown.addOption(json[0]);
                                customerDropdown.setValue(json[0].id);
                            } else {
                                alert("Customer tidak ditemukan dari QR ini.");
                            }
                        }).catch(e => {
                            alert("Terjadi kesalahan saat memproses QR.");
                        });
                }).catch(err => console.error(err));
            },
            (errorMessage) => {
                // abaikan error jika sedang proses baca 
            })
        .catch((err) => {
            alert("Tidak dapat mengakses kamera. Pastikan Anda telah mengizinkan akses kamera browser.");
            document.getElementById('qr-modal').style.display = 'none';
        });
    });

    document.getElementById('btn-close-qr').addEventListener('click', function() {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.stop().then(() => {
                document.getElementById('qr-modal').style.display = 'none';
            }).catch(err => {
                document.getElementById('qr-modal').style.display = 'none';
            });
        } else {
            document.getElementById('qr-modal').style.display = 'none';
        }
    });
</script>

<?php include '../../../resources/views/layouts/footer.php'; ?>