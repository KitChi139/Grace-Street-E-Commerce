<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy & Cookie Policy - Grace Street</title>
    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .policy-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 40px;
            background: rgba(247, 243, 238, 0.85);
            border-radius: 12px;
            border: 0.5px solid #D4C5B0;
            box-shadow: 0 8px 24px rgba(44, 40, 37, 0.1);
            font-family: 'Jost', sans-serif;
            color: #2C2825;
            line-height: 1.6;
        }
        .policy-container h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 3rem;
            text-align: center;
            margin-bottom: 30px;
        }
        .policy-container h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.8rem;
            margin-top: 30px;
            border-bottom: 1px solid #D4C5B0;
            padding-bottom: 10px;
        }
        .policy-container p {
            margin-bottom: 15px;
        }
        .policy-container ul {
            margin-bottom: 15px;
            padding-left: 20px;
        }
        .policy-container li {
            margin-bottom: 8px;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 30px;
            color: #8B6F56;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include 'additional/loginheader.php'; ?>

    <section>
        <div class="policy-container">
            <h1>Privacy & Cookie Policy</h1>
            <p>Last Updated: May 5, 2026</p>

            <h2>1. Privacy Policy</h2>
            <p>At Grace Street Clothing, we are committed to protecting your privacy. This policy explains how we collect, use, and safeguard your personal information.</p>
            
            <h3>Information We Collect</h3>
            <ul>
                <li><strong>Personal Details:</strong> Name, email address, phone number, and delivery address.</li>
                <li><strong>Account Information:</strong> Encrypted passwords and account preferences.</li>
                <li><strong>Order History:</strong> Details of the products you purchase and your payment methods.</li>
            </ul>

            <h3>How We Use Your Information</h3>
            <ul>
                <li>To process and fulfill your orders.</li>
                <li>To provide customer support and respond to inquiries.</li>
                <li>To improve our website and shopping experience.</li>
                <li>To comply with legal obligations.</li>
            </ul>

            <h2>2. Cookie Policy</h2>
            <p>Our website uses cookies to enhance your browsing experience and provide personalized services.</p>
            
            <h3>What are Cookies?</h3>
            <p>Cookies are small text files stored on your device when you visit a website. They help us remember your preferences and cart items.</p>

            <h3>Types of Cookies We Use</h3>
            <ul>
                <li><strong>Essential Cookies:</strong> Required for the website to function (e.g., keeping you logged in).</li>
                <li><strong>Performance Cookies:</strong> Help us understand how visitors interact with our site.</li>
                <li><strong>Functionality Cookies:</strong> Remember choices you make (e.g., your username).</li>
            </ul>

            <h2>3. Your Rights</h2>
            <p>You have the right to access, correct, or delete your personal information. You can manage your cookie preferences through your browser settings.</p>

            <a href="javascript:history.back()" class="back-link"><i class="fas fa-arrow-left"></i> Back to Registration</a>
        </div>
    </section>

    <?php include 'additional/footer.php'; ?>
</body>
</html>
