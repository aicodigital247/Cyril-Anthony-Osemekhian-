<?php
/**
 * BETELITE - FinTech Wallet & Cashier Withdrawal API Endpoint
 * Outputs status reports as JSON
 */
define('API_REQUEST', true);

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/security.php";
require_once __DIR__ . "/../config/functions.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please sign in to manage wallet coordinates.'
    ]);
    exit();
}

$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Return wallet details and balance summary
    $balance = get_wallet_balance($conn, $userId);
    echo json_encode([
        'status' => 'success',
        'balance' => $balance,
        'currency' => CURRENCY_CODE,
        'token' => $_SESSION['csrf_token'] ?? ''
    ]);
    exit();
} elseif ($method === 'POST') {
    verify_csrf();

    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $action = $input['action'] ?? '';

    if ($action === 'withdraw') {
        $amount = (float)($input['amount'] ?? 0);
        $bank_name = trim($input['bank_name'] ?? '');
        $account_number = trim($input['account_number'] ?? '');
        $account_name = trim($input['account_name'] ?? '');

        if ($amount <= 1000) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Minimum eligible payout withdrawal is ₦1,000.00'
            ]);
            exit();
        }

        if (empty($bank_name) || empty($account_number) || empty($account_name)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Please fill in clean recipient banking destination parameters.'
            ]);
            exit();
        }

        // Fetch user wallet
        $balance = get_wallet_balance($conn, $userId);

        if ($balance < $amount) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Insufficient wallet funds. Payout balance is: ₦' . number_format($balance, 2)
            ]);
            exit();
        }

        mysqli_begin_transaction($conn);
        try {
            // Update balance
            $sql_deb = "UPDATE be_wallets SET balance = balance - ? WHERE user_id = ?";
            $st_deb = mysqli_prepare($conn, $sql_deb);
            mysqli_stmt_bind_param($st_deb, "di", $amount, $userId);
            mysqli_stmt_execute($st_deb);

            // Get Wallet ID
            $sql_w = "SELECT id FROM be_wallets WHERE user_id = ? LIMIT 1";
            $st_w = mysqli_prepare($conn, $sql_w);
            mysqli_stmt_bind_param($st_w, "i", $userId);
            mysqli_stmt_execute($st_w);
            $wallet = mysqli_fetch_assoc(mysqli_stmt_get_result($st_w));
            $walletId = $wallet['id'];

            // Log Transaction (payout withdrawals are log initialized as pending until approved by admin operator panel)
            $ref = "TXN-WDN-" . bin2hex(random_bytes(5));
            log_transaction($conn, $walletId, $amount, 'withdrawal', $ref, 'Bank Transfer', 'Initiated cashier withdrawal request to: ' . $bank_name, 'pending');

            // Log Withdrawal spec
            $sql_wd = "INSERT INTO be_withdrawals (user_id, amount, bank_name, account_number, account_name, status, reference) 
                       VALUES (?, ?, ?, ?, ?, 'pending', ?)";
            $st_wd = mysqli_prepare($conn, $sql_wd);
            mysqli_stmt_bind_param($st_wd, "idssss", $userId, $amount, $bank_name, $account_number, $account_name, $ref);
            mysqli_stmt_execute($st_wd);

            mysqli_commit($conn);
            echo json_encode([
                'status' => 'success',
                'balance' => $balance - $amount,
                'message' => 'Cashout withdrawal of ₦' . number_format($amount, 2) . ' logged successfully. Payout is pending administrative approval.'
            ]);
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode([
                'status' => 'error',
                'message' => 'Fatal cashier transaction exception: ' . $e->getMessage()
            ]);
        }
        exit();
    }
}
