<?php
declare(strict_types=1);

include __DIR__ . '/components/connect.php';
include __DIR__ . '/components/courier_auth.php';

$auth = courier_auth_check();
if (!$auth['ok']) {
    header('Location: login.php');
    exit;
}

$courierId = $auth['user_id'];

$readyStmt = $con->prepare(
    "SELECT mo.mainOrderID, mo.total_price, mo.created_at,
            gu.first_name, gu.last_name
     FROM main_order mo
     JOIN grace_user gu ON mo.userID = gu.userID
     WHERE mo.status = 'Ready'
     ORDER BY mo.created_at ASC"
);
$readyStmt->execute();
$readyOrders = $readyStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$transitStmt = $con->prepare(
    "SELECT mo.mainOrderID, mo.total_price, mo.created_at,
            gu.first_name, gu.last_name
     FROM main_order mo
     JOIN grace_user gu ON mo.userID = gu.userID
     WHERE mo.status = 'In Transit' AND mo.courierID = ?
     ORDER BY mo.created_at ASC"
);
$transitStmt->bind_param('i', $courierId);
$transitStmt->execute();
$transitOrders = $transitStmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courier Dashboard — Grace Street</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="css/style.css">
    <style>
        .courier-wrap { max-width: 1100px; margin: 2rem auto; padding: 0 1.25rem; font-family: 'Jost', sans-serif; }
        .courier-wrap h1 { font-size: 1.5rem; color: #2C2825; margin-bottom: 0.5rem; }
        .courier-wrap h2 { font-size: 1.1rem; color: #8B6F56; margin: 2rem 0 1rem; letter-spacing: 0.06em; text-transform: uppercase; }
        .courier-note { color: #A09486; font-size: 0.9rem; margin-bottom: 1.5rem; }
        .cd-table { width: 100%; border-collapse: collapse; background: rgba(247,243,238,0.85); border: 0.5px solid #E8DED2; }
        .cd-table th, .cd-table td { padding: 12px 16px; text-align: left; border-bottom: 0.5px solid #E8DED2; font-size: 0.9rem; }
        .cd-table th { text-transform: uppercase; letter-spacing: 0.08em; font-size: 0.68rem; color: #A09486; font-weight: 400; }
        .cd-btn {
            padding: 8px 14px; border: 0.5px solid #2C2825; background: transparent; color: #2C2825;
            font-family: inherit; font-size: 0.72rem; letter-spacing: 0.08em; text-transform: uppercase; cursor: pointer;
            margin-right: 6px; transition: background 0.2s, color 0.2s;
        }
        .cd-btn:hover { background: #2C2825; color: #F7F3EE; }
        .cd-btn-primary { background: #2C2825; color: #F7F3EE; }
        .cd-btn-primary:hover { background: #8B6F56; border-color: #8B6F56; }
        .cd-empty { padding: 2rem; text-align: center; color: #A09486; border: 0.5px dashed #E8DED2; }
        .deliver-form { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-top: 6px; }
        .deliver-form input[type=file] { font-size: 0.8rem; max-width: 220px; }
        .msg { margin-top: 8px; font-size: 0.85rem; }
        .msg.err { color: #B85C38; }
        .msg.ok { color: #2a6f4a; }
    </style>
</head>
<body>
<?php include __DIR__ . '/additional/header.php'; ?>

<section class="courier-wrap">
    <h1><i class="fas fa-truck"></i> Courier Dashboard</h1>
    <p class="courier-note">Orders marked <strong>Ready for Pickup</strong> in the warehouse appear below. After you pick up, they move to <strong>On Route</strong>. Deliveries require a proof-of-delivery photo.</p>

    <h2>Ready for Pickup</h2>
    <?php if (count($readyOrders) === 0): ?>
        <div class="cd-empty">No orders waiting for pickup.</div>
    <?php else: ?>
        <table class="cd-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Placed</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($readyOrders as $o): ?>
                <tr>
                    <td>#<?php echo (int) $o['mainOrderID']; ?></td>
                    <td><?php echo htmlspecialchars($o['first_name'] . ' ' . $o['last_name']); ?></td>
                    <td><?php echo htmlspecialchars((string) $o['created_at']); ?></td>
                    <td>PHP <?php echo number_format((float) $o['total_price'], 2); ?></td>
                    <td>
                        <button type="button" class="cd-btn cd-btn-primary btn-pickup" data-id="<?php echo (int) $o['mainOrderID']; ?>">Pick Up</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h2>On Route (your deliveries)</h2>
    <?php if (count($transitOrders) === 0): ?>
        <div class="cd-empty">You have no active deliveries.</div>
    <?php else: ?>
        <table class="cd-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Placed</th>
                    <th>Total</th>
                    <th>Deliver</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($transitOrders as $o): ?>
                <tr>
                    <td>#<?php echo (int) $o['mainOrderID']; ?></td>
                    <td><?php echo htmlspecialchars($o['first_name'] . ' ' . $o['last_name']); ?></td>
                    <td><?php echo htmlspecialchars((string) $o['created_at']); ?></td>
                    <td>PHP <?php echo number_format((float) $o['total_price'], 2); ?></td>
                    <td>
                        <div class="deliver-form" data-order="<?php echo (int) $o['mainOrderID']; ?>">
                            <input type="file" class="proof-input" accept="image/jpeg,image/png,image/webp" />
                            <button type="button" class="cd-btn cd-btn-primary btn-deliver">Mark as Delivered</button>
                        </div>
                        <div class="msg deliver-feedback" style="display:none;"></div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/additional/footer.php'; ?>

<script>
document.querySelectorAll('.btn-pickup').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.getAttribute('data-id');
        var fd = new FormData();
        fd.append('id', id);
        fetch('update_order_pickup.php', { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Pickup failed');
                }
            })
            .catch(function() { alert('Network error'); });
    });
});

document.querySelectorAll('.btn-deliver').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var form = this.closest('.deliver-form');
        var id = form.getAttribute('data-order');
        var input = form.querySelector('.proof-input');
        var feedback = form.parentElement.querySelector('.deliver-feedback');
        if (!input.files || !input.files.length) {
            feedback.style.display = 'block';
            feedback.className = 'msg err';
            feedback.textContent = 'Please choose a proof-of-delivery photo.';
            return;
        }
        var fd = new FormData();
        fd.append('id', id);
        fd.append('proof', input.files[0]);
        feedback.style.display = 'none';
        fetch('update_order_delivered.php', { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                feedback.style.display = 'block';
                if (data.success) {
                    feedback.className = 'msg ok';
                    feedback.textContent = 'Marked as delivered.';
                    setTimeout(function() { location.reload(); }, 600);
                } else {
                    feedback.className = 'msg err';
                    feedback.textContent = data.error || 'Failed';
                }
            })
            .catch(function() {
                feedback.style.display = 'block';
                feedback.className = 'msg err';
                feedback.textContent = 'Network error';
            });
    });
});
</script>
</body>
</html>
