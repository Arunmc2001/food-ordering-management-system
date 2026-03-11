<?php
session_start();

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

require('vendor/autoload.php');

$success = true;
$keyId = "rzp_test_fVWlxNwdI90zig";
$keySecret = "3hY88cB4f5DNc10FGOIpPYsH";
$error = "Payment Failed";

// Accept both POST and GET for Razorpay response
$razorpay_payment_id = $_POST['razorpay_payment_id'] ?? $_GET['payment_id'] ?? null;
$razorpay_order_id = $_POST['razorpay_order_id'] ?? $_GET['order_id'] ?? null;
$razorpay_signature = $_POST['razorpay_signature'] ?? $_GET['signature'] ?? null;

if ($razorpay_payment_id !== null)
{
    $api = new Api($keyId, $keySecret);

    try
    {
        $attributes = array(
            'razorpay_order_id' => $_SESSION['razorpay_order_id'],
            'razorpay_payment_id' => $razorpay_payment_id,
            'razorpay_signature' => $razorpay_signature
        );

        $api->utility()->verifyPaymentSignature($attributes);

        // Redirect to My Orders page after successful payment
        header("Location: your_orders.php?success=1&payment_id=" . urlencode($razorpay_payment_id));
        exit();
    }
    catch(SignatureVerificationError $e)
    {
        $success = false;
        $error = 'Razorpay Error : ' . $e->getMessage();
    }
}

if ($success === true)
{
    $html = "<p>Your payment was successful</p>
             <p>Payment ID: {$razorpay_payment_id}</p>";
}
else
{
    $html = "<p>Your payment failed</p>
             <p>{$error}</p>";
}

echo $html;