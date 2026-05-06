<?php
include('./components/connect.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$create_table_query = "CREATE TABLE IF NOT EXISTS `reviews` (
    `reviewsID` INT AUTO_INCREMENT PRIMARY KEY,
    `userID` INT NOT NULL,
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
        
        $insert_review = mysqli_query($con, "INSERT INTO reviews (userID, rating, comment) VALUES ('$user_id', '$rating', '$comment')");
        
        if ($insert_review) {
            $message = "Thank you for your feedback!";
        } else {
            $message = "Failed to submit review. Please try again.";
        }
    }
}

// Fetch Reviews
$select_reviews = mysqli_query($con, "SELECT reviews.*, grace_user.username FROM reviews JOIN grace_user ON reviews.userID = grace_user.userID ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reviews - Grace Street</title>
    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
            .reviews-container {
        max-width: 1200px;
        margin: 50px auto;
        padding: 20px;
    }
    .review-form {
        background: rgba(247, 243, 238, 0.85);
        padding: 30px;
        border-radius: 16px;
        margin-bottom: 50px;
        box-shadow: 0 8px 24px rgba(44, 40, 37, 0.50);
        border: 0.5px solid #E8DED2;
    }
    .review-form h2 {
        font-family: 'Cormorant Garamond', serif;
        font-weight: 400;
        font-size: 2rem;
        color: #2C2825;
        margin-bottom: 20px;
    }
    .review-form label {
        font-family: 'Jost', sans-serif;
        font-size: 0.75rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #A09486;
        display: block;
        margin-bottom: 6px;
    }
    .review-form select, .review-form textarea {
        width: 100%;
        padding: 12px;
        margin-bottom: 20px;
        border: 0.5px solid #E8DED2;
        border-radius: 6px;
        background-color: rgba(232, 220, 210, 0.3);
        font-family: 'Jost', sans-serif;
        font-size: 0.85rem;
        color: #2C2825;
        outline: none;
    }
    .review-form input[type="submit"] {
        background-color: #2C2825;
        color: #F7F3EE;
        border: none;
        padding: 14px 30px;
        cursor: pointer;
        font-family: 'Jost', sans-serif;
        font-size: 0.8rem;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        transition: background-color 0.25s;
        border-radius: 0;
    }
    .review-form input[type="submit"]:hover {
        background-color: #8B6F56;
    }
    .reviews-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }
    .review-card {
        background: rgba(247, 243, 238, 0.85);
        padding: 22px;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(44, 40, 37, 0.08);
        border: 0.5px solid #E8DED2;
        transition: transform 0.25s, box-shadow 0.25s;
        height: 180px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .review-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(44, 40, 37, 0.13);
    }
    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }
    .review-username {
        font-family: 'Jost', sans-serif;
        font-weight: 500;
        font-size: 0.95rem;
        color: #2C2825;
    }
    .review-rating { color: #C4956A; }
    .review-date {
        font-size: 0.75rem;
        color: #A09486;
        margin-bottom: 12px;
        font-family: 'Jost', sans-serif;
    }
    .review-comment {
    line-height: 1.6;
    font-size: 0.88rem;
    color: #2C2825;
    font-family: 'Jost', sans-serif;
    overflow-y: auto;
    flex: 1;
}

.review-comment::-webkit-scrollbar { display: none; }
.review-comment { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body>
    <?php include 'additional/header.php'; ?>

    <div class="reviews-container">
        <h1 style="text-align: center; margin-bottom: 40px;font-family: 'Cormorant Garamond', serif;
  font-size: 3.5rem;">Customer Reviews & Feedbacks</h1>

        <?php if ($message): ?>
<script>
    window.addEventListener('DOMContentLoaded', function() {
        <?php if ($message === "Thank you for your feedback!"): ?>
        Swal.fire({
            icon: 'success',
            title: 'Thank you!',
            text: '<?= $message ?>',
            confirmButtonColor: '#2C2825'
        });
        <?php elseif ($message === "Please login to leave a review."): ?>
        Swal.fire({
            icon: 'warning',
            title: 'Login Required',
            text: '<?= $message ?>',
            confirmButtonColor: '#2C2825'
        });
        <?php else: ?>
        Swal.fire({
            icon: 'error',
            title: 'Oops!',
            text: '<?= $message ?>',
            confirmButtonColor: '#2C2825'
        });
        <?php endif; ?>
    });
</script>
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

        <div class="reviews-list" id="reviewsList">
            <?php if (mysqli_num_rows($select_reviews) > 0): ?>
                <?php while ($review = mysqli_fetch_assoc($select_reviews)): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <span class="review-username"><?= htmlspecialchars($review['username']); ?></span>
                            <span class="review-rating">
                                <?php for($i=0; $i<$review['rating']; $i++) echo '<i class="fa-solid fa-star"></i>'; ?>
                                <?php for($i=$review['rating']; $i<5; $i++) echo '<i class="fa-regular fa-star"></i>'; ?>
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
<div style="text-align: center; margin-top: 2rem; margin-bottom: 4rem;">
    <button id="showMoreBtn" onclick="showMore()" style="padding: 12px 32px; background: transparent; border: 0.5px solid #2C2825; color: #2C2825; font-family: 'Jost', sans-serif; font-size: 0.8rem; letter-spacing: 0.1em; text-transform: uppercase; cursor: pointer; transition: all 0.25s;" onmouseover="this.style.backgroundColor='#2C2825';this.style.color='#F7F3EE';" onmouseout="this.style.backgroundColor='transparent';this.style.color='#2C2825';">Show More</button>
</div>

<script>
    const cards = document.querySelectorAll('.review-card');
    const perPage = 6;
    let shown = perPage;

    // hide cards beyond first 6
    cards.forEach((card, i) => {
        if (i >= perPage) card.style.display = 'none';
    });

    // hide button if 6 or fewer
    if (cards.length <= perPage) {
        document.getElementById('showMoreBtn').style.display = 'none';
    }

    function showMore() {
        for (let i = shown; i < shown + perPage && i < cards.length; i++) {
            cards[i].style.display = 'flex';
        }
        shown += perPage;
        if (shown >= cards.length) {
            document.getElementById('showMoreBtn').style.display = 'none';
        }
    }
</script>
    <?php include 'additional/footer.php'; ?>
</body>
</html>
