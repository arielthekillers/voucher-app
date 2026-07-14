<?php
session_start();
require_once '../../../vendor/autoload.php';
role_required('super_admin');

$db = Database::connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::check($_POST['csrf_token'] ?? '');

    $primary_id = (int) ($_POST['primary_id'] ?? 0);
    $group_ids = $_POST['group_ids'] ?? [];

    if (!$primary_id || empty($group_ids)) {
        $_SESSION['flash_error'] = "Data tidak valid.";
        header('Location: merge.php');
        exit;
    }

    // Ensure primary_id is actually part of the group_ids
    if (!in_array($primary_id, $group_ids)) {
        $_SESSION['flash_error'] = "Primary ID tidak valid.";
        header('Location: merge.php');
        exit;
    }

    // Target IDs are all group IDs except the primary
    $target_ids = array_filter($group_ids, function($id) use ($primary_id) {
        return $id != $primary_id;
    });

    if (empty($target_ids)) {
        $_SESSION['flash_error'] = "Tidak ada akun target untuk digabungkan.";
        header('Location: merge.php');
        exit;
    }

    try {
        $db->beginTransaction();

        $placeholders = str_repeat('?,', count($target_ids) - 1) . '?';

        // 1. Move transactions
        $stmt_tx = $db->prepare("UPDATE transactions SET customer_id = ? WHERE customer_id IN ($placeholders)");
        $params_tx = array_merge([$primary_id], $target_ids);
        $stmt_tx->execute($params_tx);

        // 2. Delete target customers
        $stmt_del = $db->prepare("DELETE FROM customers WHERE id IN ($placeholders)");
        $stmt_del->execute($target_ids);

        // 3. Fix the primary customer's phone number to standard 628... format just in case it wasn't
        $primary = $db->prepare("SELECT phone FROM customers WHERE id = ?");
        $primary->execute([$primary_id]);
        $primary_phone = $primary->fetchColumn();
        
        if ($primary_phone) {
            $clean = preg_replace('/[^0-9]/', '', $primary_phone);
            if (strpos($clean, '08') === 0) {
                $clean = '62' . substr($clean, 1);
            } elseif (strpos($clean, '8') === 0) {
                $clean = '62' . $clean;
            }
            if ($clean !== $primary_phone) {
                $stmt_upd = $db->prepare("UPDATE customers SET phone = ? WHERE id = ?");
                $stmt_upd->execute([$clean, $primary_id]);
            }
        }

        $db->commit();

        $count_merged = count($target_ids);
        $_SESSION['flash_success'] = "Berhasil menggabungkan $count_merged akun duplikat.";
        header('Location: merge.php');
        exit;

    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['flash_error'] = "Gagal melakukan merge: " . $e->getMessage();
        header('Location: merge.php');
        exit;
    }
}
