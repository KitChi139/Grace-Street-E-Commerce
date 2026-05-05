<?php
declare(strict_types=1);

include __DIR__ . '/components/connect.php';

if (!isset($_SESSION['user-id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int) $_SESSION['user-id'];
$mainOrderId = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : false;
if ($mainOrderId === false || $mainOrderId < 1) {
    header('Location: order.php');
    exit;
}

$stmt = $con->prepare(
    "SELECT mo.mainOrderID, mo.status, mo.total_price, mo.created_at,
            mo.delivery_proof_image,
            mo.courierID,
            c.first_name AS courier_first, c.last_name AS courier_last,
            c.username AS courier_user
     FROM main_order mo
     LEFT JOIN grace_user c ON mo.courierID = c.userID
     WHERE mo.mainOrderID = ? AND mo.userID = ?"
);
$stmt->bind_param('ii', $mainOrderId, $userId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    header('Location: order.php');
    exit;
}

$status = $row['status'];
$steps = [
    ['db' => 'Pending', 'label' => 'Pending'],
    ['db' => 'Ready', 'label' => 'Ready for Pickup'],
    ['db' => 'In Transit', 'label' => 'On Route'],
    ['db' => 'Completed', 'label' => 'Delivered'],
];

$stepIndex = 0;
$allComplete = ($status === 'Completed');
foreach ($steps as $i => $s) {
    if ($status === $s['db']) {
        $stepIndex = $i;
        break;
    }
}
if ($allComplete) {
    $stepIndex = 3;
}

$courierName = '';
if (!empty($row['courierID'])) {
    if ($row['courier_first'] !== null || $row['courier_last'] !== null) {
        $courierName = trim((string) $row['courier_first'] . ' ' . (string) $row['courier_last']);
    }
    if ($courierName === '' && !empty($row['courier_user'])) {
        $courierName = (string) $row['courier_user'];
    }
}

$proofRel = $row['delivery_proof_image'] ?? '';
$showProof = ($status === 'Completed' && $proofRel !== '');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order #<?php echo (int) $mainOrderId; ?> — Grace Street</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="css/style.css">
    <style>
        .track-wrap { max-width: 720px; margin: 2rem auto; padding: 0 1.25rem; font-family: 'Jost', sans-serif; }
        .track-wrap h1 { font-size: 1.35rem; color: #2C2825; }
        .track-meta { color: #A09486; font-size: 0.9rem; margin-bottom: 1.5rem; }
        .progress-bar {
            display: flex; justify-content: space-between; align-items: flex-start;
            margin: 2rem 0; position: relative;
        }
        .progress-bar::before {
            content: ''; position: absolute; left: 0; right: 0; top: 14px; height: 3px;
            background: #E8DED2; z-index: 0;
        }
        .progress-fill {
            position: absolute; left: 0; top: 14px; height: 3px; background: #8B6F56; z-index: 0;
            transition: width 0.4s ease;
        }
        .p-step {
            position: relative; z-index: 1; text-align: center; flex: 1; max-width: 25%;
        }
        .p-dot {
            width: 28px; height: 28px; border-radius: 50%; margin: 0 auto 8px;
            background: #F7F3EE; border: 2px solid #E8DED2; line-height: 24px; font-size: 0.65rem;
            color: #A09486;
        }
        .p-step.done .p-dot { background: #2C2825; border-color: #2C2825; color: #F7F3EE; }
        .p-step.current .p-dot { background: #8B6F56; border-color: #8B6F56; color: #F7F3EE; }
        .p-label { font-size: 0.68rem; letter-spacing: 0.04em; color: #A09486; line-height: 1.3; }
        .p-step.done .p-label, .p-step.current .p-label { color: #2C2825; }
        .courier-box {
            background: rgba(232,222,210,0.35); border: 0.5px solid #E8DED2;
            padding: 1rem 1.25rem; margin-top: 1.5rem;
        }
        .courier-box strong { color: #2C2825; }
        .proof-box { margin-top: 1.5rem; }
        .proof-box img { max-width: 100%; border: 0.5px solid #E8DED2; margin-top: 0.75rem; }
        .back-link { display: inline-block; margin-top: 2rem; color: #8B6F56; text-decoration: none; font-size: 0.85rem; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
<?php include __DIR__ . '/additional/header.php'; ?>

<section class="track-wrap">
    <h1>Order #<?php echo (int) $mainOrderId; ?></h1>
    <p class="track-meta">
        Placed <?php echo htmlspecialchars((string) $row['created_at']); ?>
        · Total PHP <?php echo number_format((float) $row['total_price'], 2); ?>
    </p>

    <div class="progress-bar">
        <?php
        $pct = $allComplete ? 100 : ($stepIndex / (count($steps) - 1) * 100);
        ?>
        <div class="progress-fill" style="width: <?php echo max(0, min(100, $pct)); ?>%;"></div>
        <?php foreach ($steps as $i => $s): ?>
            <?php
            $cls = 'p-step';
            if ($allComplete || $i < $stepIndex) {
                $cls .= ' done';
            } elseif ($i === $stepIndex) {
                $cls .= ' current';
            }
            ?>
            <div class="<?php echo $cls; ?>">
                <div class="p-dot"><?php echo ($allComplete || $i < $stepIndex) ? '<i class="fas fa-check" style="font-size:0.65rem"></i>' : ($i + 1); ?></div>
                <div class="p-label"><?php echo htmlspecialchars($s['label']); ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="courier-box">
        <strong>Courier</strong><br>
        <?php if ($courierName !== ''): ?>
            <?php echo htmlspecialchars($courierName); ?>
        <?php else: ?>
            <span style="color:#A09486;">Not assigned yet</span>
        <?php endif; ?>
    </div>

    <?php if ($showProof): ?>
    <div class="proof-box">
        <strong style="color:#2C2825;">Proof of delivery</strong><br>
        <img src="<?php echo htmlspecialchars($proofRel); ?>" alt="Proof of delivery">
    </div>
    <?php endif; ?>

    <a class="back-link" href="order.php"><i class="fas fa-arrow-left"></i> Back to orders</a>
</section>

<?php include __DIR__ . '/additional/footer.php'; ?>
</body>
</html>
