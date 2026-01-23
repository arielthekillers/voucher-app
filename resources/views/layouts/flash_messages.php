<?php
if (isset($_SESSION['flash_success'])) {
    $msg = $_SESSION['flash_success'];
    $type = 'success';
    unset($_SESSION['flash_success']);
} elseif (isset($_SESSION['flash_error'])) {
    $msg = $_SESSION['flash_error'];
    $type = 'error';
    unset($_SESSION['flash_error']);
} else {
    $msg = null;
    $type = null;
}
?>

<?php if ($msg): ?>
<div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
    <div id="toast-message" class="toast toast-<?= $type ?>" style="
        background: #fff;
        color: #333;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 300px;
        border-left: 5px solid <?= $type === 'success' ? '#22c55e' : '#ef4444' ?>;
        transform: translateX(100%);
        opacity: 0;
        transition: all 0.4s ease-out;
    ">
        <i class='bx <?= $type === 'success' ? 'bxs-check-circle' : 'bxs-error-circle' ?>' style="font-size: 1.5rem; color: <?= $type === 'success' ? '#22c55e' : '#ef4444' ?>;"></i>
        <div style="flex: 1; font-weight: 500; font-size: 0.95rem;">
            <?= htmlspecialchars($msg) ?>
        </div>
        <button onclick="closeToast()" style="background: none; border: none; cursor: pointer; color: #9ca3af; font-size: 1.25rem; display: flex;">
            <i class='bx bx-x'></i>
        </button>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const toast = document.getElementById('toast-message');
        
        // Slide in
        setTimeout(() => {
            toast.style.transform = "translateX(0)";
            toast.style.opacity = "1";
        }, 100);

        // Auto close after 4 seconds
        setTimeout(() => {
            closeToast();
        }, 4000);
    });

    function closeToast() {
        const toast = document.getElementById('toast-message');
        toast.style.transform = "translateX(100%)";
        toast.style.opacity = "0";
        
        // Remove from DOM after animation
        setTimeout(() => {
            const container = document.getElementById('toast-container');
            if(container) container.remove();
        }, 400);
    }
</script>
<?php endif; ?>
