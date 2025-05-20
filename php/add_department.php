<?php
session_start();
include 'config.php';
include 'sidebar.php';

// Vérification de l'authentification et des droits admin
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM user_info WHERE id = '$user_id'"));
if ($user['type'] != 'admin') {
    header('Location: indexx.php');
    exit();
}

// Messages flash
$success_message = '';
$error_message = '';

// Traitement du formulaire d'ajout
if (isset($_POST['add_department'])) {
    $name = $_POST['name'];
    
    // Vérifier si le département existe déjà
    $check_query = mysqli_prepare($conn, "SELECT * FROM departement WHERE speciality = ?");
    mysqli_stmt_bind_param($check_query, "s", $name);
    mysqli_stmt_execute($check_query);
    $result = mysqli_stmt_get_result($check_query);
    
    if (mysqli_num_rows($result) > 0) {
        $error_message = "Un département avec ce nom existe déjà.";
    } else {
        // Ajouter le nouveau département
        $insert_query = mysqli_prepare($conn, "INSERT INTO departement (speciality) VALUES (?)");
        mysqli_stmt_bind_param($insert_query, "s", $name);
        if (mysqli_stmt_execute($insert_query)) {
            $success_message = "Département ajouté avec succès !";
            header("Location: products.php");
            exit();
        } else {
            $error_message = "Erreur lors de l'ajout du département : " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un département - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Styles CSS -->
</head>
<body>

<div class="section">
    <h2><i class="fas fa-plus-circle"></i> Ajouter un nouveau département</h2>
    
    <!-- Liens de navigation -->
    <div class="navigation-links">
        <a href="products.php" class="btn-outline"><i class="fas fa-arrow-left"></i> Retour à la gestion des produits</a>
    </div>
    
    <!-- Messages de notification -->
    <?php if (!empty($success_message)): ?>
        <div class="alert success">
            <i class="fas fa-check-circle"></i> <?= $success_message ?>
            <span class="close-btn" onclick="this.parentElement.style.display='none';">×</span>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
        <div class="alert error">
            <i class="fas fa-exclamation-circle"></i> <?= $error_message ?>
            <span class="close-btn" onclick="this.parentElement.style.display='none';">×</span>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <form method="POST" class="add-form">
            <div class="form-group">
                <label for="name"><i class="fas fa-tag"></i> Nom du département</label>
                <input type="text" id="name" name="name" placeholder="Entrez le nom du département" required>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="add_department" class="btn-primary"><i class="fas fa-plus"></i> Ajouter le département</button>
            </div>
        </form>
    </div>
    
    <!-- Liste des départements existants -->
    <div class="existing-departments">
        <h3>Départements existants</h3>
        
        <?php
        $departments = mysqli_query($conn, "SELECT speciality, 
                                          (SELECT COUNT(*) FROM products WHERE speciality = d.speciality) as product_count 
                                          FROM departement d 
                                          ORDER BY speciality ASC");
        ?>
        
        <div class="departments-grid">
            <?php while ($d = mysqli_fetch_assoc($departments)): ?>
                <div class="department-item">
                    <div class="dept-name"><?= ucfirst(htmlspecialchars($d['speciality'])); ?></div>
                    <div class="dept-count"><?= $d['product_count']; ?> produits</div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<!-- Styles CSS -->
<style>
:root {
    --primary-color: #2c3e50;
    --secondary-color: #3498db;
    --success-color: #2ecc71;
    --warning-color: #f39c12;
    --danger-color: #e74c3c;
    --light-color: #ecf0f1;
    --dark-color: #34495e;
    --border-radius: 8px;
    --shadow: 0 2px 10px rgba(0,0,0,0.1);
    --transition: all 0.3s ease;
}

.section {
    max-width: 1200px;
    margin: 40px auto;
    background: #fff;
    padding: 25px;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
}

h2, h3, h4 { 
    text-align: center; 
    color: var(--primary-color);
    margin-bottom: 20px;
}

h2 { font-size: 28px; }
h3 { font-size: 22px; }

/* Statistiques */
.stats-container {
    display: flex;
    justify-content: space-around;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 15px;
}

.stat-box {
    flex: 1;
    min-width: 200px;
    background: #f8f9fa;
    padding: 20px 15px;
    border-radius: var(--border-radius);
    text-align: center;
    box-shadow: var(--shadow);
    transition: var(--transition);
    border-top: 4px solid var(--secondary-color);
}

.stat-box:hover {
    transform: translateY(-5px);
}

.stat-box.warning {
    border-top-color: var(--warning-color);
}

.stat-box.danger {
    border-top-color: var(--danger-color);
}

.stat-box i {
    font-size: 28px;
    color: var(--secondary-color);
    margin-bottom: 10px;
}

.stat-box.warning i {
    color: var(--warning-color);
}

.stat-box.danger i {
    color: var(--danger-color);
}

.stat-value {
    display: block;
    font-size: 26px;
    font-weight: bold;
    margin: 10px 0;
}

.stat-label {
    display: block;
    color: #666;
    font-size: 14px;
}

/* Menu des départements */
.department-menu {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 25px;
    justify-content: center;
}

.dept-btn {
    display: inline-flex;
    align-items: center;
    background: #f8f9fa;
    color: var(--dark-color);
    padding: 8px 15px;
    border-radius: 30px;
    text-decoration: none;
    font-size: 14px;
    border: 1px solid #ddd;
    transition: var(--transition);
}

.dept-btn:hover {
    background: #f1f1f1;
    transform: translateY(-2px);
}

.dept-btn.active {
    background: var(--secondary-color);
    color: white;
    border-color: var(--secondary-color);
}

.badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(0,0,0,0.1);
    color: inherit;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    font-size: 12px;
    margin-left: 5px;
}

.dept-btn.active .badge {
    background: rgba(255,255,255,0.2);
}

/* Alertes */
.alert {
    padding: 15px;
    border-radius: var(--border-radius);
    margin-bottom: 20px;
    position: relative;
    animation: fadeIn 0.5s ease;
}

.alert.success {
    background-color: rgba(46, 204, 113, 0.1);
    border-left: 4px solid var(--success-color, #2ecc71);
    color: #27ae60;
}

.alert.error {
    background-color: rgba(231, 76, 60, 0.1);
    border-left: 4px solid var(--danger-color);
    color: #c0392b;
}

.close-btn {
    position: absolute;
    right: 15px;
    top: 15px;
    cursor: pointer;
    font-size: 18px;
}

/* Onglets */
.tabs {
    display: flex;
    justify-content: center;
    margin-bottom: 25px;
    border-bottom: 1px solid #e0e0e0;
    flex-wrap: wrap;
}

.tab-btn {
    padding: 12px 20px;
    cursor: pointer;
    background: transparent;
    border: none;
    font-size: 15px;
    color: #555;
    position: relative;
    transition: var(--transition);
    margin: 0 5px;
}

.tab-btn:hover {
    color: var(--secondary-color);
}

.tab-btn.active {
    color: var(--secondary-color);
    font-weight: bold;
}

.tab-btn.active:after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--secondary-color);
    border-radius: 3px 3px 0 0;
}

.tab-btn i {
    margin-right: 8px;
}

.tab-content {
    display: block;
    animation: fadeIn 0.5s ease;
}

/* Formulaires */
.filter-form {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 10px;
    margin-bottom: 20px;
    background: #f8f9fa;
    padding: 15px;
    border-radius: var(--border-radius);
}

.filter-group {
    flex: 1;
    min-width: 180px;
}

.filter-form input,
.filter-form select,
.filter-form button {
    width: 100%;
    padding: 10px;
    border-radius: var(--border-radius);
    border: 1px solid #ddd;
    font-size: 14px;
}

.filter-form input:focus,
.filter-form select:focus {
    border-color: var(--secondary-color);
    outline: none;
    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
}

/* Actions en masse */
.bulk-actions {
    display: flex;
    justify-content: end;
    margin-bottom: 20px;
}

/* Tableau */
.table-responsive {
    overflow-x: auto;
    box-shadow: var(--shadow);
    border-radius: var(--border-radius);
    margin-bottom: 30px;
}

.styled-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

.styled-table th,
.styled-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
}

.styled-table th {
    background: #f5f5f5;
    color: var(--dark-color);
    font-weight: bold;
    text-transform: uppercase;
    font-size: 13px;
    letter-spacing: 0.5px;
}

.styled-table tr:last-child td {
    border-bottom: none;
}

.styled-table tr:hover {
    background-color: #f9f9f9;
}

.styled-table img {
    height: 60px;
    width: 60px;
    object-fit: cover;
    border-radius: 6px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.styled-table img:hover {
    transform: scale(1.1);
}

.image-preview {
    display: inline-block;
    position: relative;
}

.quantity-cell {
    position: relative;
    padding-right: 80px !important;
}

.stock-badge {
    position: absolute;
    right: 15px;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
}

.stock-badge.success {
    background: rgba(46, 204, 113, 0.15);
    color: #27ae60;
}

.stock-badge.warning {
    background: rgba(243, 156, 18, 0.15);
    color: #d35400;
}

.stock-badge.critical {
    background: rgba(231, 76, 60, 0.15);
    color: #c0392b;
}

.out-of-stock {
    color: var(--danger-color);
    font-weight: bold;
}

.low-stock {
    color: var(--warning-color);
    font-weight: bold;
}

.in-stock {
    color: var(--success-color);
}

.no-results {
    text-align: center;
    padding: 30px !important;
    color: #777;
    font-style: italic;
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: center;
    margin-top: 20px;
    flex-wrap: wrap;
    gap: 5px;
}

.page-btn {
    display: inline-block;
    padding: 8px 15px;
    margin: 0 2px;
    border-radius: var(--border-radius);
    background: #f8f9fa;
    color: #555;
    text-decoration: none;
    transition: var(--transition);
    border: 1px solid #ddd;
}

.page-btn:hover {
    background: #eee;
}

.page-btn.active {
    background: var(--secondary-color);
    color: white;
    border-color: var(--secondary-color);
}

/* Formulaire d'ajout */
.add-form {
    max-width: 700px;
    margin: 0 auto;
    background: #f8f9fa;
    padding: 25px;
    border-radius: var(--border-radius);
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: var(--dark-color);
}

.form-group label i {
    margin-right: 6px;
    color: var(--secondary-color);
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: var(--border-radius);
    font-size: 14px;
    transition: var(--transition);
}

.form-group input:focus, 
.form-group select:focus,
.form-group textarea:focus {
    border-color: var(--secondary-color);
    outline: none;
    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
}

.image-upload-container {
    position: relative;
    border: 2px dashed #ddd;
    border-radius: var(--border-radius);
    height: 200px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition);
}

.image-upload-container:hover {
    border-color: var(--secondary-color);
}

.image-upload-container input[type="file"] {
    position: absolute;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    z-index: 2;
}

.image-preview {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

#imagePreview {
    max-width: 100%;
    max-height: 180px;
    object-fit: contain;
}

.placeholder {
    text-align: center;
    color: #777;
}

.placeholder i {
    font-size: 40px;
    margin-bottom: 10px;
    color: #ccc;
}

/* Actions sur les formulaires */
.form-actions {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 30px;
}

/* Styles des boutons */
.btn-primary,
.btn-blue,
.btn-outline,
.btn-danger {
    padding: 10px 18px;
    border-radius: var(--border-radius);
    font-size: 14px;
    cursor: pointer;
    transition: var(--transition);
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
}

.btn-primary {
    background: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background: #243342;
    transform: translateY(-2px);
}

.btn-blue {
    background: var(--secondary-color);
    color: white;
}

.btn-blue:hover {
    background: #2980b9;
    transform: translateY(-2px);
}

.btn-danger {
    background: var(--danger-color);
    color: white;
}

.btn-danger:hover {
    background: #c0392b;
    transform: translateY(-2px);
}

.btn-outline {
    background: transparent;
    border: 1px solid #ddd;
    color: #555;
}

.btn-outline:hover {
    background: #f5f5f5;
    transform: translateY(-2px);
}

.btn-primary i, .btn-blue i, .btn-danger i, .btn-outline i {
    margin-right: 8px;
}

/* Actions dans le tableau */
.actions-cell {
    white-space: nowrap;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    text-decoration: none;
    transition: var(--transition);
    margin: 0 2px;
    border: none;
    cursor: pointer;
    color: white;
}

.btn-action.edit {
    background-color: #3498db;
}

.btn-action.delete {
    background-color: #e74c3c;
}

.btn-action.stock {
    background-color: #f39c12;
}

.btn-action.view {
    background-color: #2ecc71;
}

.btn-action:hover {
    transform: translateY(-2px);
    opacity: 0.9;
}

.inline-form {
    display: inline-block;
}

/* Gestion des départements */
.departments-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.department-card {
    background: white;
    border-radius: var(--border-radius);
    border: 1px solid #e0e0e0;
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: var(--transition);
    position: relative;
}

.department-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow);
}

.department-icon {
    width: 60px;
    height: 60px;
    background: rgba(52, 152, 219, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15px;
}

.department-icon i {
    font-size: 24px;
    color: var(--secondary-color);
}

.department-info {
    text-align: center;
    margin-bottom: 15px;
    flex-grow: 1;
}

.department-info h4 {
    margin: 0 0 5px 0;
    color: var(--dark-color);
}

.department-info p {
    color: #777;
    margin: 0;
}

.department-actions {
    display: flex;
    justify-content: center;
    gap: 10px;
    width: 100%;
}

.department-actions .btn-action {
    width: auto;
    height: auto;
    border-radius: var(--border-radius);
    padding: 6px 12px;
}

.department-card.add-new {
    border: 2px dashed #ddd;
    background: #f9f9f9;
    transition: var(--transition);
}

.department-card.add-new:hover {
    border-color: var(--secondary-color);
}

.add-department-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    width: 100%;
    color: #777;
    text-decoration: none;
    transition: var(--transition);
}

.add-department-btn:hover {
    color: var(--secondary-color);
}

.add-department-btn i {
    font-size: 30px;
    margin-bottom: 10px;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes zoomInFade {
    from {
        transform: scale(0.95);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

.animate-zoom-fade {
    animation: zoomInFade 0.5s ease-out forwards;
    opacity: 0;
}

@keyframes slideInLeft {
    0% {
        transform: translateX(-20px);
        opacity: 0;
    }
    100% {
        transform: translateX(0);
        opacity: 1;
    }
}

.animate-slide-left {
    animation: slideInLeft 0.6s ease forwards;
    opacity: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .filter-group {
        flex: 1 0 100%;
    }
    
    .stat-box {
        flex: 1 0 100%;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions button {
        width: 100%;
    }
    
    .btn-action {
        width: 38px;
        height: 38px;
    }
}

.navigation-links {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
}

.card {
    background: white;
    border-radius: var(--border-radius);
    padding: 25px;
    box-shadow: var(--shadow);
    margin-bottom: 30px;
}

.add-form {
    max-width: 600px;
    margin: 0 auto;
}

.existing-departments {
    margin-top: 40px;
}

.departments-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.department-item {
    background: #f8f9fa;
    border-radius: var(--border-radius);
    padding: 15px;
    border-left: 4px solid var(--secondary-color);
    transition: var(--transition);
}

.department-item:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow);
}

.dept-name {
    font-weight: bold;
    color: var(--dark-color);
    margin-bottom: 5px;
}

.dept-count {
    font-size: 13px;
    color: #777;
}
</style>
</body>
</html>