<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../components/connect.php';

$footerRole = '';
if (isset($_SESSION['user-id'])) {
    $u_id = $_SESSION['user-id'];
    $res = mysqli_query($con, "SELECT r.role FROM grace_user u JOIN roles r ON u.roleID = r.roleID WHERE u.userID = '$u_id'");
    if ($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        $footerRole = strtolower(trim($row['role']));
    }
}

if ($footerRole !== 'courier'):
?>
<footer>
    <section>
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer">
                    <h1 style="font-weight: bold; margin: 0;">Our Flagship Store</h1>
                    <p>PATEROS<br/>TECHNOLOGICAL<br/>COLLEGE</p>
                    <br/>
                    <p>Tel: 123-456-7890</p>
                    <br/>
                    <a href="">View Stores List</a>
                </div>
                <div class="footer">
                    <h1 style="font-weight: bold; margin: 0;">Shop</h1>
                        <br/>
                        <div style="display:flex; flex-direction: column; gap: 15px;">
                            <a href="">Womens</a>
                            <a href="">Mens</a>
                            <a href="">All Product</a>
                        </div>
                </div>
                <div class="footer">
                    <h1 style="font-weight: bold; margin: 0;">Info</h1>
                    <br/>
                    <div style="display:flex; flex-direction: column; gap: 15px;">
                        <a href="">Our Story</a>
                        <a href="">Contact</a>
                        <a href="">Shipping & Returns</a>
                        <a href="">Store Policy</a>
                        <a href="">Forum</a>
                        <a href="">FAQ</a>
                    </div>
                </div>
                <div class="footer">
                    <h1 style="font-weight: bold; margin: 0;">Get Special Deals & Offers</h1>
                    <br/>
                    <div class="footer-emailaddress" style="display: flex; flex-direction: column;">
                        <p style="margin: 0;">Email Address</p>
                        <input type="email">
                        <input type="button" value="Subscribe">
                        <p>Thanks for submitting!</p>
                    </div>
                    <div class="footer-icons">
                        <a href=""><i class="fa-brands fa-facebook" style="color: white; font-size: 24px;"></i></a>
                        <a href=""><i class="fa-brands fa-instagram" style="color: white; font-size: 24px;"></i></a>
                        <a href=""><i class="fa-brands fa-youtube" style="color: white; font-size: 24px;"></i></a>
                    </div>
                    
                </div>
            </div>
        </div>
    </section>
</footer>

<!-- Cookie Consent Banner -->
<div id="cookie-banner" style="display: none; position: fixed; bottom: 20px; left: 20px; right: 20px; background: rgba(44, 40, 37, 0.95); color: #F7F3EE; padding: 20px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); z-index: 9999; border: 1px solid #D4C5B0; backdrop-filter: blur(10px);">
    <div style="max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 300px;">
            <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 1.4rem; margin: 0 0 10px 0; color: #D4C5B0; letter-spacing: 0.05em;">Cookie Consent</h3>
            <p style="font-family: 'Jost', sans-serif; font-size: 0.9rem; margin: 0; line-height: 1.5; color: rgba(247, 243, 238, 0.85);">
                We use cookies to enhance your browsing experience, serve personalized ads or content, and analyze our traffic. By clicking "Accept All", you consent to our use of cookies. Read our <a href="privacy_policy.php" style="color: #D4C5B0; text-decoration: underline;">Privacy Policy</a> for more details.
            </p>
        </div>
        <div style="display: flex; gap: 12px;">
            <button id="accept-cookies" style="padding: 12px 24px; background: #D4C5B0; color: #2C2825; border: none; border-radius: 6px; font-family: 'Jost', sans-serif; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; cursor: pointer; transition: all 0.3s ease;">Accept All</button>
            <button id="decline-cookies" style="padding: 12px 24px; background: transparent; color: #F7F3EE; border: 1px solid rgba(212, 197, 176, 0.3); border-radius: 6px; font-family: 'Jost', sans-serif; font-size: 0.85rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em; cursor: pointer; transition: all 0.3s ease;">Decline</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cookieBanner = document.getElementById('cookie-banner');
        const acceptBtn = document.getElementById('accept-cookies');
        const declineBtn = document.getElementById('decline-cookies');

        // Check if user has already made a choice
        if (!localStorage.getItem('cookieConsent')) {
            setTimeout(() => {
                cookieBanner.style.display = 'block';
            }, 1000);
        }

        acceptBtn.addEventListener('click', function() {
            localStorage.setItem('cookieConsent', 'accepted');
            cookieBanner.style.opacity = '0';
            setTimeout(() => { cookieBanner.style.display = 'none'; }, 500);
        });

        declineBtn.addEventListener('click', function() {
            localStorage.setItem('cookieConsent', 'declined');
            cookieBanner.style.opacity = '0';
            setTimeout(() => { cookieBanner.style.display = 'none'; }, 500);
        });
    });
</script>
<?php endif; ?>
