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
<?php endif; ?>
