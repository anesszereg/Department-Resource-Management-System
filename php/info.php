<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Boumerdes Shopping - Products</title>

   <!-- Font Awesome CDN -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   
   <!-- Bootstrap CSS -->
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   
   <style>
    :root {
        --primary-color: #29486b;
        --secondary-color: #e74c3c;
        --light-gray: #f5f5f5;
        --box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        --text-color: #333;
    }
    
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f0f2f5;
        margin: 0;
        padding: 0;
    }
    
    /* Header Styles */
    header {
        background-color: var(--primary-color);
        color: white;
        text-align: center;
        padding: 15px 0;
        box-shadow: var(--box-shadow);
        position: relative;
    }
    
    .logo {
        font-size: 24px;
        font-weight: bold;
    }
    
    .logo span {
        color: var(--secondary-color);
    }
    
    .login-button {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        background-color: var(--secondary-color);
        color: white;
        border: none;
        padding: 5px 15px;
        border-radius: 4px;
        cursor: pointer;
    }
    
    /* Main Content Styles */
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    
    /* Welcome Section */
    .welcome-section {
        background-color: var(--primary-color);
        color: white;
        padding: 30px;
        border-radius: 8px;
        margin-bottom: 30px;
    }
    
    .welcome-title {
        font-size: 24px;
        margin-bottom: 10px;
    }
    
    /* Products Section */
    .products-section {
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: var(--box-shadow);
        margin-bottom: 30px;
    }
    
    .section-header {
        color: var(--primary-color);
        font-size: 22px;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    
    .product-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }
    
    .product-card {
        border: 1px solid #eee;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--box-shadow);
    }
    
    .product-image {
        height: 180px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .product-details {
        padding: 15px;
    }
    
    .product-name {
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .product-price {
        color: var(--secondary-color);
        font-size: 18px;
        margin-bottom: 15px;
    }
    
    .product-description {
        color: #777;
        font-size: 14px;
        margin-bottom: 15px;
        line-height: 1.5;
    }
    
    .order-button {
        background-color: var(--primary-color);
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 4px;
        cursor: pointer;
        width: 100%;
    }
    
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.7);
    }
    
    .modal-content {
        background-color: white;
        margin: 15% auto;
        padding: 20px;
        border-radius: 8px;
        width: 80%;
        max-width: 500px;
        text-align: center;
    }
    
    .modal-button {
        background-color: var(--secondary-color);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        margin-top: 15px;
    }
    
    .close-modal {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    
    /* Footer */
    footer {
        background-color: var(--primary-color);
        color: white;
        text-align: center;
        padding: 15px 0;
        margin-top: 20px;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .product-container {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        }
        
        .login-button {
            position: static;
            display: block;
            margin: 10px auto 0;
            transform: none;
        }
    }
    
    @media (max-width: 576px) {
        .welcome-section {
            padding: 15px;
        }
        
        .welcome-title {
            font-size: 20px;
        }
        
        .section-header {
            font-size: 18px;
        }
        
        .product-container {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        }
    }
   </style>
</head>
<body>

<!-- Header -->
<header>
    <div class="logo">Boum<span>Erdes</span> Shopping</div>
    <button class="login-button" id="loginBtn">Se Connecter</button>
</header>

<!-- Main Content -->
<div class="container">
    <!-- Welcome Section -->
    <div class="welcome-section">
        <div class="welcome-title">
            <i class="fas fa-box"></i>
            Our Products
        </div>
        <p>Browse through our collection of high-quality products.</p>
    </div>
    
    <!-- Products Section -->
    <div class="products-section">
        <h2 class="section-header">Available Products</h2>
        
        <div class="product-container">
            <!-- Sample Product Cards -->
            <div class="product-card">
                <div class="product-image">
                    <img src="../assets/imgs/product-1.jpg" alt="Product 1">
                </div>
                <div class="product-details">
                    <div class="product-name">Wireless Headphones</div>
                    <div class="product-price">$89.99</div>
                    <div class="product-description">Premium wireless headphones with noise cancellation and long battery life...</div>
                    <button class="order-button">Commander</button>
                </div>
            </div>
            
            <div class="product-card">
                <div class="product-image">
                    <img src="images/product2.jpg" alt="Product 2">
                </div>
                <div class="product-details">
                    <div class="product-name">Smart Watch</div>
                    <div class="product-price">$149.99</div>
                    <div class="product-description">Feature-rich smartwatch with health monitoring and notifications...</div>
                    <button class="order-button">Commander</button>
                </div>
            </div>
            
            <div class="product-card">
                <div class="product-image">
                    <img src="images/product3.jpg" alt="Product 3">
                </div>
                <div class="product-details">
                    <div class="product-name">Laptop Backpack</div>
                    <div class="product-price">$45.99</div>
                    <div class="product-description">Durable laptop backpack with multiple compartments and water resistance...</div>
                    <button class="order-button">Commander</button>
                </div>
            </div>
            
            <div class="product-card">
                <div class="product-image">
                    <img src="images/product4.jpg" alt="Product 4">
                </div>
                <div class="product-details">
                    <div class="product-name">Coffee Maker</div>
                    <div class="product-price">$79.99</div>
                    <div class="product-description">Programmable coffee maker with thermal carafe to keep your coffee hot...</div>
                    <button class="order-button">Commander</button>
                </div>
            </div>
            
            <div class="product-card">
                <div class="product-image">
                    <img src="images/product5.jpg" alt="Product 5">
                </div>
                <div class="product-details">
                    <div class="product-name">Yoga Mat</div>
                    <div class="product-price">$29.99</div>
                    <div class="product-description">Eco-friendly non-slip yoga mat with carrying strap...</div>
                    <button class="order-button">Commander</button>
                </div>
            </div>
            
            <div class="product-card">
                <div class="product-image">
                    <img src="images/product6.jpg" alt="Product 6">
                </div>
                <div class="product-details">
                    <div class="product-name">Bluetooth Speaker</div>
                    <div class="product-price">$59.99</div>
                    <div class="product-description">Waterproof bluetooth speaker with 360° sound and 12-hour battery life...</div>
                    <button class="order-button">Commander</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Login Message -->
<div id="loginModal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h3>Vous devez vous connecter</h3>
        <p>Pour commander ce produit, vous devez d'abord vous connecter à votre compte.</p>
        <button id="redirectLogin" class="modal-button">Se Connecter</button>
    </div>
</div>

<!-- Footer -->
<footer>
    <p>&copy; 2025 Boumerdes Shopping. All rights reserved.</p>
</footer>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Get the modal
    const modal = document.getElementById("loginModal");
    
    // Get all order buttons
    const orderButtons = document.querySelectorAll(".order-button");
    
    // Get the close button
    const closeModal = document.querySelector(".close-modal");
    
    // Get the login button in modal
    const redirectLogin = document.getElementById("redirectLogin");
    
    // Get the login button in header
    const loginBtn = document.getElementById("loginBtn");
    
    // Add click event to all order buttons
    orderButtons.forEach(button => {
        button.addEventListener("click", function() {
            modal.style.display = "block";
        });
    });
    
    // Close modal when clicking on X
    closeModal.addEventListener("click", function() {
        modal.style.display = "none";
    });
    
    // Close modal when clicking outside of it
    window.addEventListener("click", function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    });
    
    // Redirect to login button when clicking on modal button
    redirectLogin.addEventListener("click", function() {
        modal.style.display = "none";
        // Scroll to top and highlight login button
        window.scrollTo(0, 0);
        loginBtn.focus();
        loginBtn.style.animation = "pulse 1s infinite";
        setTimeout(function() {
            loginBtn.style.animation = "";
        }, 3000);
    });
</script>

</body>
</html>