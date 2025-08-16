<?php
require_once '../include/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please login to access this page';
    echo "<script>window.location.href = 'login.php';</script>";
    exit();
}

// Check if payment response is received
if (!isset($_POST['payment_id']) || !isset($_POST['order_id']) || !isset($_POST['signature'])) {
    $_SESSION['error'] = 'Invalid payment response';
    echo "<script>window.location.href = 'application_form.php';</script>";
    exit();
}

try {
    global $conn, $logger;
    
    $paymentId = $_POST['payment_id'];
    $razorpayOrderId = $_POST['order_id'];
    $signature = $_POST['signature'];
    $dbOrderId = $_POST['db_order_id'];
    
    // Verify payment signature (optional but recommended)
    // You can add Razorpay signature verification here
    
    // Update order status to paid with Razorpay details in your orders table
    $sql = "UPDATE orders SET 
                payment_status = 'paid',
                payment_id = ?,
                order_id = ?,
                signature = ?,
                paid_at = NOW()
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $paymentId, $razorpayOrderId, $signature, $dbOrderId);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update order status: " . $stmt->error);
    }
    
    if ($stmt->affected_rows === 0) {
        throw new Exception("Order not found or already processed");
    }
    
    // Log successful payment
    $logger->info("Payment successful - Payment ID: " . $paymentId . ", Order ID: " . $dbOrderId);
    
    // Update application status to paid
    if (isset($_SESSION['temp_application']['application_number'])) {
        $sql = "UPDATE applications SET status = 'paid' WHERE application_number = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $_SESSION['temp_application']['application_number']);
        $stmt->execute();
    }
    
    // Get order details for success message using your schema
    $sql = "SELECT o.*, a.application_number, a.service_type FROM orders o 
            LEFT JOIN applications a ON o.application_number = a.application_number 
            WHERE o.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $dbOrderId);
    $stmt->execute();
    $result = $stmt->get_result();
    $orderDetails = $result->fetch_assoc();
    
    // Decode customer details
    $customerDetails = json_decode($orderDetails['customer_details'], true);
    
    // Clear temp application data
    unset($_SESSION['temp_application']);
    
    // Set success message
    $_SESSION['success'] = 'Payment completed successfully! Your application has been submitted.';
    
} catch (Exception $e) {
    $logger->error("Payment processing error: " . $e->getMessage());
    $_SESSION['error'] = 'Payment processing failed: ' . $e->getMessage();
    echo "<script>window.location.href = 'application_form.php';</script>";
    exit();
}

?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white text-center">
                    <h4><i class="fas fa-check-circle me-2"></i>Payment Successful!</h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="success-icon mb-3">
                            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                        </div>
                        <h5 class="text-success">Thank you for your payment!</h5>
                        <p class="text-muted">Your application has been submitted successfully and is now being processed.</p>
                    </div>
                    
                    <?php if (isset($orderDetails)): ?>
                    <div class="payment-details">
                        <h6>Payment Details</h6>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Application Number:</span>
                                    <strong><?php echo htmlspecialchars($orderDetails['application_number']); ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Service Type:</span>
                                    <span><?php echo htmlspecialchars($orderDetails['service_type']); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Payment ID:</span>
                                    <span class="small"><?php echo htmlspecialchars($orderDetails['payment_id']); ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Order Number:</span>
                                    <span><?php echo htmlspecialchars($orderDetails['order_no']); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Amount Paid:</span>
                                    <strong>₹<?php echo number_format($orderDetails['amount']); ?> <?php echo $orderDetails['currency']; ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Payment Date:</span>
                                    <span><?php echo date('d M Y, h:i A', strtotime($orderDetails['paid_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="alert alert-info mt-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>What's Next?</strong>
                        <ul class="mt-2 mb-0">
                            <li>You will receive an email confirmation shortly</li>
                            <li>Our team will review your application and documents</li>
                            <li>You can track your application status from your dashboard</li>
                            <li>We will contact you if any additional information is required</li>
                        </ul>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="dashboard.php" class="btn btn-primary me-2">
                            <i class="fas fa-tachometer-alt me-1"></i>Go to Dashboard
                        </a>
                        <a href="application_form.php" class="btn btn-outline-secondary">
                            <i class="fas fa-plus me-1"></i>New Application
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.success-icon {
    animation: successPulse 2s ease-in-out infinite;
}

@keyframes successPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}
</style>

<?php include '../include/footer.php'; ?>