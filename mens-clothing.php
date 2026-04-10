<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Men's Clothing</title>

    <!-- css connection -->
    <link rel="stylesheet" href="Css/style.css">
    <!-- jQuery UI CSS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
</head>
<body>
    <?php include 'additional/header.php'; ?>
    <section>
        <div class="products-container">
            <div class="filter">
                <h1 style="font-weight: 300;">Filter by</h1>
                <form id="filterForm">
                    <!-- Dropdown menu -->
                    <nav class="filter-nav">
                        <label for="touch"><span>Category</span></label>
                        <input type="checkbox" id="touch">
                        <ul>
                            <li><a href="#">All</a></li>
                            <li><a href="#">Beige/Cream Color</a></li>
                            <li><a href="#">Yellow Color</a></li>
                            <li><a href="#">Black Color</a></li>
                            <li><a href="#">White Color</a></li>
                        </ul>
                    </nav>
                    
                    <button type="button" id="priceButton" class="filter-button">Price:</button>
                    <div id="priceOptions">
                        <div id="priceSlider"></div>
                        <div id="priceRange" syle="color: black;">315 PHP - 600 PHP</div>
                    </div>

                    <!-- Clear Filter button -->
                    <button type="button" id="clearButton" onclick="clearFilter()">Clear Filter</button>
                </form>
            </div>
            <div class="products">
                <div class="products-header">
                    <h1>Men's Clothing</h1>
                </div>
                <div class="products-table">

                </div>
            </div>
        </div>
    </section>
    <?php include 'additional/footer.php'; ?>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- jQuery UI -->
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <!-- JavaScript for filtering -->
    <script>
        $(document).ready(function() {
            // Price slider
            $("#priceSlider").slider({
                range: true,
                min: 315,
                max: 600,
                values: [315, 600],
                step: 5,
                slide: function(event, ui) {
                    $("#priceRange").text(ui.values[0] + " PHP - " + ui.values[1] + " PHP");
                }
            });

            // Toggle price options visibility
            $("#priceButton").click(function() {
                $("#priceOptions").slideToggle();
            });
        });

        function applyFilter() {
            // Get selected values
            var category = document.getElementById("category").value;
            var priceMin = $("#priceSlider").slider("values", 0);
            var priceMax = $("#priceSlider").slider("values", 1);

            // Apply filter logic here
            // For example, you can use these values to filter products displayed on your page

            console.log("Category: " + category);
            console.log("Price Range: " + priceMin + " PHP - " + priceMax + " PHP");
        }

        function clearFilter() {
            // Reset category dropdown to 'All'
            $("#category").val("all");
            $("#categoryButton").text("Category:");

            // Reset price slider
            $("#priceSlider").slider("values", [315, 600]);

            // Update price range display
            $("#priceRange").text("315 PHP - 600 PHP");

            // Apply filter again to reset the display
            applyFilter();
        }

        // Apply filter when the form is submitted
        document.getElementById("filterForm").addEventListener("submit", function(event) {
            event.preventDefault(); // Prevent form submission
            applyFilter();
        });
    </script>
</body>
</html>
