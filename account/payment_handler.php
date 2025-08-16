<?php
require_once '../include/header.php';

// Check if user is logged in and has temp application data
if (!isset($_SESSION['user_id']) || !isset($_SESSION['temp_application'])) {
    $_SESSION['error'] = 'Invalid access. Please start the application process again.';
    header('Location: application_form.php');
    exit();
}

$appData = $_SESSION['temp_application'];

// Razorpay configuration
$razorpay_key_id = 'YOUR_RAZORPAY_KEY_ID'; // Replace with your Razorpay Key ID
$razorpay_key_secret = 'YOUR_RAZORPAY_KEY_SECRET'; // Replace with your Razorpay Key Secret

// Create Razorpay order
$order_id = 'order_' . uniqid();
$amount = $appData['amount'] * 100; // Amount in paise

?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Complete Payment</h5>
                </div>
                <div class="card-body">
                    <div class="order-summary mb-4">
                        <h6>Order Summary</h6>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Service:</span>
                            <span><?php echo htmlspecialchars($appData['application_type']); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Name:</span>
                            <span><?php echo htmlspecialchars($appData['name']); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Email:</span>
                            <span><?php echo htmlspecialchars($appData['email']); ?></span>
                        </div>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Amount:</span>
                            <span>₹<?php echo number_format($appData['amount']); ?></span>
                        </div>
                    </div>
                    
                    <button id="rzp-button" class="btn btn-primary w-100">
                        <i class="fas fa-credit-card me-2"></i>Pay ₹<?php echo number_format($appData['amount']); ?>
                    </button>
                    
                    <div class="mt-3 text-center">
                        <small class="text-muted">Secured by Razorpay</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('rzp-button').onclick = function(e) {
    var options = {
        "key": "<?php echo $razorpay_key_id; ?>",
        "amount": "<?php echo $amount; ?>",
        "currency": "INR",
        "name": "Your Company Name",
        "description": "<?php echo htmlspecialchars($appData['application_type']); ?>",
        "order_id": "<?php echo $order_id; ?>",
        "handler": function (response) {
            // Payment successful
            window.location.href = 'payment_success.php?payment_id=' + response.razorpay_payment_id + 
                                  '&order_id=' + response.razorpay_order_id + 
                                  '&signature=' + response.razorpay_signature;
        },
        "prefill": {
            "name": "<?php echo htmlspecialchars($appData['name']); ?>",
            "email": "<?php echo htmlspecialchars($appData['email']); ?>",
            "contact": "<?php echo htmlspecialchars($appData['mobile']); ?>"
        },
        "notes": {
            "application_type": "<?php echo htmlspecialchars($appData['application_type']); ?>"
        },
        "theme": {
            "color": "#3399cc"
        },
        "modal": {
            "ondismiss": function() {
                // Payment cancelled
                window.location.href = 'payment_cancel.php';
            }
        }
    };
    var rzp1 = new Razorpay(options);
    rzp1.open();
    e.preventDefault();
}
</script>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<?php include '../include/footer.php'; ?>