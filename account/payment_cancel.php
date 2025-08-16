<?php
require_once '../include/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please login to access this page';
    echo "<script>window.location.href = 'login.php';</script>";
    exit();
}

try {
    global $conn, $logger;

    $dbOrderId = $_POST['db_order_id'] ?? null;

    if ($dbOrderId) {
        // Mark the order as cancelled / unpaid
        $sql = "UPDATE orders 
                SET payment_status = 'unpaid' 
                WHERE id = ? AND payment_status = 'unpaid'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $dbOrderId);
        $stmt->execute();

        $logger->warning("Payment cancelled by user for Order ID: " . $dbOrderId);
    }

    // Clear temp application if needed
    unset($_SESSION['temp_application']);

} catch (Exception $e) {
    $logger->error("Payment cancel error: " . $e->getMessage());
}

?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-danger text-white text-center">
                    <h4><i class="fas fa-times-circle me-2"></i>Payment Cancelled</h4>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-times-circle text-danger" style="font-size: 4rem;"></i>
                    </div>
                    <h5 class="text-danger">Your payment was not completed</h5>
                    <p class="text-muted">It looks like you cancelled the transaction or it failed to process.</p>

                    <div class="alert alert-warning mt-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Next Steps:</strong>
                        <ul class="mt-2 mb-0 text-start">
                            <li>You can try making the payment again</li>
                            <li>If the amount was deducted, it will be auto-refunded by Razorpay</li>
                            <li>Contact support if you face repeated issues</li>
                        </ul>
                    </div>

                    <div class="mt-4">
                        <a href="new_application.php" class="btn btn-success me-2">
                            <i class="fas fa-redo me-1"></i> Try Again
                        </a>
                        <a href="dashboard.php" class="btn btn-outline-secondary">
                            <i class="fas fa-home me-1"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card-header.bg-danger {
    background: #dc3545 !important;
}
</style>

<?php include '../include/footer.php'; ?>
