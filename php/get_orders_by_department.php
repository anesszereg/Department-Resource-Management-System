<?php
include 'config.php';

// Get department from GET parameter
$department = isset($_GET['department']) ? mysqli_real_escape_string($conn, $_GET['department']) : '';

$departmentCondition = '';
if (!empty($department) && strtolower($department) !== 'all') {
    $departmentCondition = "AND user_info.speciality = '$department'";
}

$query = "
SELECT cart.*, 
       user_info.name AS user_name,
       products.name AS product_name, 
       user_info.speciality AS department
FROM cart
JOIN user_info ON cart.user_id = user_info.id
JOIN products ON cart.product_id = products.id
WHERE TRIM(LOWER(cart.status)) NOT IN ('pending', 'not-approved', 'not approved')
$departmentCondition
ORDER BY cart.created_at DESC
";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Database error: ' . htmlspecialchars(mysqli_error($conn)) . '</p></div>';
    exit;
}

if (mysqli_num_rows($result) > 0) {
    echo '<table class="orders-table">';
    echo '<thead>
        <tr>
            <th>User</th>
            <th>Product</th>
            <th>Quantity</th>
            <th>Date</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>';

    while ($row = mysqli_fetch_assoc($result)) {
        $quantity = isset($row['allotted_quantity']) ? $row['allotted_quantity'] : (isset($row['allottedQuantity']) ? $row['allottedQuantity'] : '1');
        $status = strtolower($row['status']);
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['user_name']) . '</td>';
        echo '<td>' . htmlspecialchars($row['product_name']) . '</td>';
        echo '<td>' . $quantity . '</td>';
        $rawDate = $row['status_updated_at'] ?? $row['created_at'];
        $dateObj = date_create($rawDate);
        $formattedDate = $dateObj ? date_format($dateObj, 'Y-m-d') : $rawDate;
        echo '<td data-raw-date="' . $formattedDate . '">' . htmlspecialchars($formattedDate) . '</td>';
        echo '<td>';

        $statusText = '';
        $statusClass = '';

        switch($status) {
            case 'validée':
                $statusText = 'Approved';
                $statusClass = 'status-validée';
                break;
            case 'rejetée':
                $statusText = 'Rejected';
                $statusClass = 'status-rejetée';
                break;
            case 'livrée':
                $statusText = 'Delivered';
                $statusClass = 'status-livrée';
                break;
            default:
                $statusText = htmlspecialchars($row['status']);
                $statusClass = '';
        }

        echo '<span class="status-badge ' . $statusClass . '" data-status="' . strtolower($status) . '">' . $statusText . '</span>';
        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
} else {
    $deptMsg = ($department && strtolower($department) !== 'all') ? htmlspecialchars($department) : 'All Departments';
    echo '<div class="empty-state">
            <i class="fas fa-clipboard-list"></i>
            <p>No orders found for: ' . $deptMsg . '</p>
          </div>';
}
?>

<style>
/* Styles pour la barre de recherche et les filtres */
.search-filter-container {
    margin: 20px 0;
    padding: 15px;
    background-color: #f5f5f5;
    border-radius: 5px;
    display: flex;
    flex-wrap: wrap;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.search-filter-container form {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    width: 100%;
    gap: 10px;
}

.search-box {
    flex: 1;
    min-width: 200px;
    position: relative;
}

.search-box input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    transition: border-color 0.3s, box-shadow 0.3s;
}

.search-box input:focus {
    border-color: #4CAF50;
    box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
    outline: none;
}

.status-filter, .department-filter {
    min-width: 150px;
}

.status-filter select, .department-filter select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    background-color: white;
    transition: border-color 0.3s, box-shadow 0.3s;
    appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 1em;
}

.status-filter select:focus, .department-filter select:focus {
    border-color: #4CAF50;
    box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
    outline: none;
}

button[type="submit"] {
    padding: 10px 18px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
    transition: background-color 0.3s;
}

button[type="submit"]:hover {
    background-color: #45a049;
}

.reset-button {
    padding: 10px 18px;
    background-color: #f0f0f0;
    color: #333;
    border: 1px solid #ccc;
    border-radius: 4px;
    text-decoration: none;
    margin-left: 5px;
    font-weight: bold;
    transition: background-color 0.3s;
}

.reset-button:hover {
    background-color: #e0e0e0;
}

/* Styles pour les indicateurs de statut */
.status {
    padding: 6px 10px;
    border-radius: 4px;
    display: inline-block;
    font-size: 12px;
    font-weight: bold;
    text-align: center;
    transition: transform 0.2s;
}

.status:hover {
    transform: scale(1.05);
}

.status.validée {
    background-color: #D4EDDA;
    color: #155724;
}

.status.rejetée {
    background-color: #F8D7DA;
    color: #721C24;
}

.status.livrée {
    background-color: #D1ECF1;
    color: #0C5460;
}

/* Styles pour le tableau */
.orders-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-radius: 5px;
    overflow: hidden;
}

.orders-table th, .orders-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.orders-table th {
    background-color: #f2f2f2;
    font-weight: bold;
    color: #333;
}

.orders-table tr:last-child td {
    border-bottom: none;
}

.orders-table tr:hover {
    background-color: #f5f5f5;
}

/* Animation pour les filtres */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.search-filter-container {
    animation: fadeIn 0.3s ease-out;
}

/* Animation pour la table */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.orders-table {
    animation: slideIn 0.4s ease-out;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const elements = {
        filterContainer: document.querySelector('.search-filter-container'),
        ordersTable: document.querySelector('.orders-table'),
        searchInput: document.getElementById('searchInput'),
        statusFilter: document.getElementById('statusFilter'),
        departmentFilter: document.getElementById('departmentFilter'),
        resetButton: document.getElementById('resetButton'),
        filterForm: document.getElementById('filterForm')
    };

    const animateElement = (element, delay) => {
        if (element) {
            element.style.opacity = '0';
            setTimeout(() => {
                element.style.transition = `opacity ${delay}s ease`;
                element.style.opacity = '1';
            }, 100);
        }
    };

    animateElement(elements.filterContainer, 0.3);
    animateElement(elements.ordersTable, 0.4);

    const debounce = (func, wait) => {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    };

    const submitForm = () => {
        if (elements.filterForm) {
            elements.filterForm.submit();
        }
    };

    const handleSelectChange = () => submitForm();

    if (elements.statusFilter) {
        elements.statusFilter.addEventListener('change', handleSelectChange);
    }

    if (elements.departmentFilter) {
        elements.departmentFilter.addEventListener('change', handleSelectChange);
    }

    if (elements.searchInput) {
        const debouncedSearch = debounce(submitForm, 300);
        elements.searchInput.addEventListener('input', debouncedSearch);

        if (elements.searchInput.value) {
            const clearButton = document.createElement('button');
            Object.assign(clearButton, {
                type: 'button',
                innerHTML: '×',
                className: 'clear-search'
            });
            Object.assign(clearButton.style, {
                position: 'absolute',
                right: '10px',
                top: '50%',
                transform: 'translateY(-50%)',
                background: 'none',
                border: 'none',
                fontSize: '16px',
                color: '#999',
                cursor: 'pointer'
            });

            elements.searchInput.parentNode.style.position = 'relative';
            elements.searchInput.parentNode.appendChild(clearButton);

            clearButton.addEventListener('click', () => {
                elements.searchInput.value = '';
                submitForm();
            });
        }
    }

    if (elements.resetButton) {
        elements.resetButton.addEventListener('mouseenter', () => {
            elements.resetButton.style.transition = 'transform 0.2s ease';
            elements.resetButton.style.transform = 'scale(1.05)';
        });

        elements.resetButton.addEventListener('mouseleave', () => {
            elements.resetButton.style.transform = 'scale(1)';
        });

        elements.resetButton.addEventListener('click', () => {
            if (elements.searchInput) elements.searchInput.value = '';
            if (elements.statusFilter) elements.statusFilter.value = '';
            if (elements.departmentFilter) elements.departmentFilter.value = '';
            submitForm();
        });
    }
});
</script>