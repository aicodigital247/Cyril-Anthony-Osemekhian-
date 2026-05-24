<?php
/**
 * BETELITE - Administration Operations API
 * Strict JSON outputs restricted to Administrative clearings
 */
define('API_REQUEST', true);

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/security.php";

header('Content-Type: application/json');

// Force admin clearances
require_admin();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    verify_csrf();
    
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $action = $input['action'] ?? '';

    if ($action === 'payout_action') {
        // Approve / reject cashier withdrawals
        $wd_id = (int)($input['withdrawal_id'] ?? 0);
        $status = $input['status'] ?? ''; // 'approved' or 'rejected'

        if ($wd_id <= 0 || !in_array($status, ['approved', 'rejected'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid parameters specified.']);
            exit();
        }

        mysqli_begin_transaction($conn);
        try {
            // Get withdrawal details
            $sql_wd = "SELECT * FROM be_withdrawals WHERE id = ? LIMIT 1";
            $st_wd = mysqli_prepare($conn, $sql_wd);
            mysqli_stmt_bind_param($st_wd, "i", $wd_id);
            mysqli_stmt_execute($st_wd);
            $wd_data = mysqli_fetch_assoc(mysqli_stmt_get_result($st_wd));

            if (!$wd_data) {
                echo json_encode(['status' => 'error', 'message' => 'Withdrawal request not found.']);
                exit();
            }

            if ($wd_data['status'] !== 'pending') {
                echo json_encode(['status' => 'error', 'message' => 'This request has already been processed and finalized.']);
                exit();
            }

            // Update Withdrawal
            $sql_up = "UPDATE be_withdrawals SET status = ? WHERE id = ?";
            $st_up = mysqli_prepare($conn, $sql_up);
            mysqli_stmt_bind_param($st_up, "si", $status, $wd_id);
            mysqli_stmt_execute($st_up);

            // Update Transaction reference status
            $sql_t = "UPDATE be_transactions SET status = ? WHERE reference = ?";
            $st_t = mysqli_prepare($conn, $sql_t);
            $tx_stat = ($status === 'approved') ? 'completed' : 'failed';
            mysqli_stmt_bind_param($st_t, "ss", $tx_stat, $wd_data['reference']);
            mysqli_stmt_execute($st_t);

            // If rejected, refund back to user wallet
            if ($status === 'rejected') {
                $sql_ref = "UPDATE be_wallets SET balance = balance + ? WHERE user_id = ?";
                $st_ref = mysqli_prepare($conn, $sql_ref);
                mysqli_stmt_bind_param($st_ref, "di", $wd_data['amount'], $wd_data['user_id']);
                mysqli_stmt_execute($st_ref);
            }

            mysqli_commit($conn);
            echo json_encode([
                'status' => 'success',
                'message' => 'Withdrawal order has successfully been ' . strtoupper($status) . '!'
            ]);
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => 'DB execution mismatch: ' . $e->getMessage()]);
        }
        exit();
    } elseif ($action === 'update_live') {
        // Force update home_score, away_score, possession etc in live match
        $matchId = (int)($input['match_id'] ?? 0);
        $home_score = (int)($input['home_score'] ?? 0);
        $away_score = (int)($input['away_score'] ?? 0);
        $match_time = (int)($input['match_time'] ?? 90);
        $status = $input['match_status'] ?? 'Live';

        if ($matchId <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Please state correct match.']);
            exit();
        }

        $sql = "UPDATE be_matches SET home_score = ?, away_score = ?, match_time = ?, match_status = ? 
                WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iiiis", $home_score, $away_score, $match_time, $status, $matchId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Telemetry live score board updated successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'MySQL update query error.']);
        }
        exit();
    } elseif ($action === 'manage_user') {
        // Change user role or suspend
        $target_user = (int)($input['user_id'] ?? 0);
        $field = $input['field'] ?? ''; // 'status' or 'role'
        $value = $input['val'] ?? ''; // 'active', 'suspended' or 'user', 'predictor', 'admin'

        if ($target_user <= 0 || !in_array($field, ['status', 'role'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing parameter variables.']);
            exit();
        }

        if ($field === 'status') {
            $sql = "UPDATE be_users SET status = ? WHERE id = ?";
        } else {
            $sql = "UPDATE be_users SET role = ? WHERE id = ?";
        }

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $value, $target_user);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Punter properties successfully updated.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to execute query update.']);
        }
        exit();
    }
}

echo json_encode(['status' => 'error', 'message' => 'Action unrecognized.']);
exit();
