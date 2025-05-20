<?php
session_start();
include 'config.php';
include 'sidebar.php';

if (!isset($_SESSION['user_id'])) {
    header('location: login.php');
    exit();
}

// Check if user is admin
$user_id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM user_info WHERE id = '$user_id'"));
if ($user['type'] != 'admin') {
    header('location: indexx.php');
    exit();
}

// Check if product exists
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $product_query = mysqli_query($conn, "SELECT * FROM products WHERE id = $id");
    $product = mysqli_fetch_assoc($product_query);

    if (!$product) {
        header('location: products.php');
        exit();
    }
}

// Update product
if (isset($_POST['update_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $quantity = intval($_POST['quantity']);
    $speciality = mysqli_real_escape_string($conn, $_POST['speciality']);
    $image = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $image_path = $product['image'];  // keep old image if no new image is uploaded

    if ($image) {
        $image_path = 'images/' . basename($image);
        move_uploaded_file($image_tmp, $image_path);
    }

    // Update product in database
    mysqli_query($conn, "UPDATE products SET name = '$name', quantity = $quantity, speciality = '$speciality', image = '$image_path' WHERE id = $id");

    header('location: products.php');
    exit();
}

$departments = mysqli_query($conn, "SELECT DISTINCT speciality FROM departement");
?>

<div class="section">
    <h2>✏️ Edit Product</h2>

    <!-- Back button to product management page -->
    <div class="back-button-container">
        <a href="products.php" class="back-button">← Back</a>
    </div>

    <!-- Product edit form -->
    <form method="POST" enctype="multipart/form-data" class="form-grid">
        <div class="input-group">
            <label for="name">Product Name</label>
            <input type="text" id="name" name="name" value="<?= $product['name']; ?>" required>
        </div>
        <div class="input-group">
            <label for="quantity">Quantity</label>
            <input type="number" id="quantity" name="quantity" value="<?= $product['quantity']; ?>" required>
        </div>
        <div class="input-group">
            <label for="speciality">Department</label>
            <select id="speciality" name="speciality" required>
                <?php while ($d = mysqli_fetch_assoc($departments)): ?>
                    <option value="<?= $d['speciality']; ?>" <?= $d['speciality'] == $product['speciality'] ? 'selected' : ''; ?>>
                        <?= ucfirst($d['speciality']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="input-group">
            <label for="image">Image</label>
            <div class="custom-file-input">
                <input type="file" id="image" name="image" class="file-input-hidden">
                <div class="file-input-display">
                    <span class="file-input-text">No file selected</span>
                    <button type="button" class="file-input-button">Choose file</button>
                </div>
            </div>
            <?php if (!empty($product['image'])): ?>
                <div class="current-image">
                    <p>Current image:</p>
                    <img src="<?= $product['image']; ?>" alt="<?= $product['name']; ?>">
                </div>
            <?php endif; ?>
        </div>
        <div class="button-container">
            <button type="submit" name="update_product">✅ Update</button>
        </div>
    </form>
</div>

<style>
/* Reset and base styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

body {
    background-color: #f8f9fa;
    color: #333;
    line-height: 1.6;
}

/* Main section */
.section {
    max-width: 1000px;
    margin: 40px auto;
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(149, 157, 165, 0.2);
    transition: all 0.3s ease;
    position: relative;
}

.section:hover {
    box-shadow: 0 12px 28px rgba(149, 157, 165, 0.3);
}

/* Header */
h2 {
    text-align: center;
    color: #2c3e50;
    font-size: 28px;
    margin-bottom: 25px;
    font-weight: 600;
    position: relative;
    padding-bottom: 12px;
}

h2::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 100px;
    height: 3px;
    background: linear-gradient(90deg, #4b6cb7 0%, #182848 100%);
    border-radius: 3px;
}

/* Back button */
.back-button-container {
    margin-bottom: 20px;
}

.back-button {
    display: inline-block;
    padding: 10px 16px;
    background-color: #f1f3f5;
    color: #4a5568;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s ease;
    border: 1px solid #e1e4e8;
}

.back-button:hover {
    background-color: #e9ecef;
    color: #2d3748;
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.back-button:active {
    transform: translateY(0);
}

/* Form */
.form-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
    margin-bottom: 30px;
}

@media (min-width: 768px) {
    .form-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Form fields */
.form-grid input,
.form-grid select {
    padding: 14px 16px;
    border-radius: 8px;
    border: 1px solid #e1e4e8;
    font-size: 15px;
    width: 100%;
    background-color: #f8f9fa;
    transition: all 0.2s ease;
}

.form-grid input:focus,
.form-grid select:focus {
    border-color: #4b6cb7;
    box-shadow: 0 0 0 3px rgba(75, 108, 183, 0.15);
    outline: none;
    background-color: #fff;
}

/* File upload field */
.form-grid input[type="file"] {
    background-color: #fff;
    padding: 12px;
    cursor: pointer;
    border: 1px dashed #ccc;
}

.form-grid input[type="file"]:hover {
    background-color: #f0f4f8;
    border-color: #4b6cb7;
}

/* Custom file input styling */
.custom-file-input {
    position: relative;
    width: 100%;
}

.file-input-hidden {
    position: absolute;
    width: 0.1px;
    height: 0.1px;
    opacity: 0;
    overflow: hidden;
    z-index: -1;
}

.file-input-display {
    display: flex;
    align-items: center;
    width: 100%;
    padding: 12px 16px;
    border-radius: 8px;
    border: 1px solid #e1e4e8;
    background-color: #f8f9fa;
}

.file-input-text {
    flex-grow: 1;
    font-size: 15px;
    color: #6c757d;
}

.file-input-button {
    padding: 8px 12px;
    border-radius: 6px;
    background: linear-gradient(90deg, #4b6cb7 0%, #182848 100%);
    color: #fff;
    font-size: 14px;
    font-weight: 500;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.file-input-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* Button container */
.button-container {
    grid-column: 1 / -1;
    display: flex;
    justify-content: center;
    margin-top: 10px;
}

/* Button */
.form-grid button[type="submit"] {
    padding: 14px 24px;
    border-radius: 8px;
    border: none;
    font-size: 16px;
    font-weight: 600;
    background: linear-gradient(90deg, #4b6cb7 0%, #182848 100%);
    color: #fff;
    cursor: pointer;
    transition: all 0.3s ease;
    width: auto;
    min-width: 180px;
}

.form-grid button[type="submit"]:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(75, 108, 183, 0.3);
}

.form-grid button[type="submit"]:active {
    transform: translateY(0);
}

/* Field labels */
.input-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.input-group label {
    font-weight: 500;
    color: #4a5568;
    font-size: 14px;
}

/* Current image display */
.current-image {
    margin-top: 10px;
    text-align: center;
}

.current-image img {
    max-width: 100px;
    max-height: 100px;
    border-radius: 6px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

/* Success/error messages */
.message {
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-weight: 500;
    text-align: center;
}

.message.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.message.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.querySelector('.file-input-hidden');
    const fileText = document.querySelector('.file-input-text');
    const fileButton = document.querySelector('.file-input-button');
    
    fileButton.addEventListener('click', function() {
        fileInput.click();
    });
    
    fileInput.addEventListener('change', function() {
        if (fileInput.files.length > 0) {
            fileText.textContent = fileInput.files[0].name;
        } else {
            fileText.textContent = 'No file selected';
        }
    });
});
</script>