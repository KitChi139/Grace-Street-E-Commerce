<?php
include('./components/connect.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$create_table_query = "CREATE TABLE IF NOT EXISTS `reviews` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `username` VARCHAR(255) NOT NULL,
    `rating` INT NOT NULL,
    `comment` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
mysqli_query($con, $create_table_query);

$user_id = isset($_SESSION['user-id']) ? $_SESSION['user-id'] : null;
$message = "";

// Handle Review Submission
if (isset($_POST['submit_review'])) {
    if (!$user_id) {
        $message = "Please login to leave a review.";
    } else {
        $rating = mysqli_real_escape_string($con, $_POST['rating']);
        $comment = mysqli_real_escape_string($con, $_POST['comment']);
        
        // Get username
        $select_user = mysqli_query($con, "SELECT username FROM grace_user WHERE id = '$user_id'");
        $user_data = mysqli_fetch_assoc($select_user);
        $username = $user_data['username'];

        $insert_review = mysqli_query($con, "INSERT INTO reviews (user_id, username, rating, comment) VALUES ('$user_id', '$username', '$rating', '$comment')");
        
        if ($insert_review) {
            $message = "Thank you for your feedback!";
        } else {
            $message = "Failed to submit review. Please try again.";
        }
    }
}

// Fetch Reviews
$select_reviews = mysqli_query($con, "SELECT * FROM reviews ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reviews - Grace Street</title>
    <link rel="stylesheet" href="Css/style.css">
    <style>
        .reviews-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
        }
        .review-form {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 50px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .review-form h2 {
            margin-bottom: 20px;
        }
        .review-form select, .review-form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .review-form input[type="submit"] {
            background: black;
            color: white;
            border: none;
            padding: 10px 30px;
            cursor: pointer;
            border-radius: 5px;
        }
        .reviews-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .review-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            border: 1px solid #eee;
        }
        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .review-username {
            font-weight: bold;
        }
        .review-rating {
            color: #f39c12;
        }
        .review-date {
            font-size: 12px;
            color: #888;
            margin-bottom: 10px;
        }
        .review-comment {
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <?php include 'additional/header.php'; ?>

    <div class="reviews-container">
        <h1 style="text-align: center; margin-bottom: 40px;">Customer Reviews & Feedbacks</h1>

        <?php if ($message): ?>
            <div style="text-align: center; padding: 15px; background: #e8f5e9; color: #2e7d32; border-radius: 5px; margin-bottom: 30px;">
                <?= $message; ?>
            </div>
        <?php endif; ?>

        <div class="review-form">
            <h2>Leave a Review</h2>
            <form action="" method="POST">
                <label for="rating">Rating:</label>
                <select name="rating" id="rating" required>
                    <option value="5">5 - Excellent</option>
                    <option value="4">4 - Very Good</option>
                    <option value="3">3 - Good</option>
                    <option value="2">2 - Fair</option>
                    <option value="1">1 - Poor</option>
                </select>

                <label for="comment">Your Feedback:</label>
                <textarea name="comment" id="comment" rows="5" placeholder="Share your experience with our products..." required></textarea>

                <input type="submit" name="submit_review" value="Submit Review">
            </form>
        </div>

        <div class="reviews-list">
            <?php if (mysqli_num_rows($select_reviews) > 0): ?>
                <?php while ($review = mysqli_fetch_assoc($select_reviews)): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <span class="review-username"><?= htmlspecialchars($review['username']); ?></span>
                            <span class="review-rating">
                                <?php for($i=0; $i<$review['rating']; $i++) echo "★"; ?>
                            </span>
                        </div>
                        <div class="review-date"><?= date('F j, Y', strtotime($review['created_at'])); ?></div>
                        <p class="review-comment"><?= nl2br(htmlspecialchars($review['comment'])); ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; grid-column: 1/-1;">No reviews yet. Be the first to leave one!</p>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'additional/footer.php'; ?>
</body>
</html>
