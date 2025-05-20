<?php
session_start();
include 'config.php';
include 'sidebar.php';

// Retrieve departments
$departmentsQuery = "SELECT DISTINCT speciality FROM user_info WHERE speciality IS NOT NULL AND speciality != ''";
$departmentsResult = mysqli_query($conn, $departmentsQuery);

// Check if query was successful
if (!$departmentsResult) {
    $error = mysqli_error($conn);
    echo '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Database error: ' . htmlspecialchars($error) . '</p></div>';
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin - Orders by Department</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --secondary: #6c757d;
            --success: #2ecc71;
            --info: #3498db;
            --warning: #f39c12;
            --danger: #e74c3c;
            --light: #f8f9fa;
            --dark: #343a40;
            --white: #ffffff;
            --border-color: #e9ecef;
            --border-radius: 8px;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fe;
            color: #444;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .dashboard-content {
            padding: 25px;
            margin-left: 20px;
            margin-right: 20px;
            margin-top: 20px;
            margin-bottom: 40px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .page-title {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .page-title i {
            margin-right: 10px;
            color: var(--primary);
            font-size: 26px;
        }

        .department-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 30px;
            background-color: var(--white);
            padding: 15px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .department-btn {
            background-color: var(--white);
            color: var(--dark);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: 10px 15px;
            cursor: pointer;
            transition: var(--transition);
            font-size: 14px;
            font-weight: 500;
            text-align: center;
            display: flex;
            align-items: center;
        }

        .department-btn:hover {
            background-color: var(--light);
            border-color: var(--primary);
            color: var(--primary);
        }

        .department-btn.active {
            background-color: var(--primary);
            color: var(--white);
            border-color: var(--primary);
        }

        .department-btn i {
            margin-right: 8px;
        }

        .card {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 25px;
            overflow: hidden;
        }

        .card-header {
            background-color: var(--white);
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
            color: #333;
        }

        .card-body {
            padding: 20px;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th, .orders-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            font-size: 14px;
        }

        .orders-table th {
            background-color: #f8faff;
            font-weight: 600;
            color: #444;
        }

        .orders-table tr:last-child td {
            border-bottom: none;
        }

        .orders-table tr:hover td {
            background-color: #f5f7fe;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 500;
            text-align: center;
        }

        .status-validée {
            background-color: #e7f9ef;
            color: #2ecc71;
        }

        .status-rejetée {
            background-color: #feecec;
            color: #e74c3c;
        }

        .status-livrée {
            background-color: #e7f0ff;
            color: #3498db;
        }

        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px;
            font-size: 16px;
            color: var(--secondary);
        }

        .loading i {
            margin-right: 10px;
            animation: spin 1s infinite linear;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--secondary);
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .empty-state p {
            font-size: 16px;
            margin: 0;
            opacity: 0.8;
        }

        /* Added styles for filter controls */
        .filter-controls {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px 15px;
            background-color: #f8faff;
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
            gap: 10px;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-group label {
            font-size: 14px;
            font-weight: 500;
            color: #444;
            white-space: nowrap;
        }

        .filter-group select,
        .filter-group input {
            padding: 6px 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 13px;
            color: #444;
        }

        .date-filter {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .date-filter input[type="date"] {
            padding: 5px 8px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 13px;
            color: #444;
            width: auto;
        }

        .search-filter {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            max-width: 300px;
        }
        
        .search-filter input {
            padding: 6px 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 13px;
            width: 100%;
        }
        
        .filter-actions {
            display: flex;
            gap: 8px;
        }
        
        .filter-actions button {
            padding: 6px 12px;
            border-radius: 4px;
            border: none;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .filter-actions button.reset-btn {
            background-color: #f0f0f0;
            color: #333;
            border: 1px solid #ddd;
        }
        
        .filter-actions button.reset-btn:hover {
            background-color: #e0e0e0;
        }
        
        @media (max-width: 768px) {
            .dashboard-content {
                padding: 15px;
                margin: 10px;
            }
            
            .department-nav {
                overflow-x: auto;
                padding: 10px;
            }
            
            .department-btn {
                padding: 8px 12px;
                font-size: 13px;
            }
            
            .orders-table {
                font-size: 13px;
            }
            
            .orders-table th, .orders-table td {
                padding: 10px;
            }
            
            .filter-controls {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }
            
            .search-filter {
                max-width: 100%;
                width: 100%;
            }
        }
        


    </style>
</head> 
<body>
<div class="dashboard-content">
    <div class="page-header">
        <h2 class="page-title"><i class="fas fa-briefcase"></i>Department Archives</h2>
    </div>

    <div class="department-nav">
        <!-- Show All Departments Button -->
        <button class="department-btn active" onclick="loadOrders('all', this)">
            <i class="fas fa-building"></i> All Departments
        </button>

        <?php 
        $firstDepartment = false; // 'All' is the first button now
        
        if ($departmentsResult && mysqli_num_rows($departmentsResult) > 0) {
            while ($department = mysqli_fetch_assoc($departmentsResult)) { 
                $icon = 'fas fa-users';
                $dept = htmlspecialchars($department['speciality']);
                
                // Choose an icon based on department name
                if (stripos($dept, 'tech') !== false) $icon = 'fas fa-laptop-code';
                elseif (stripos($dept, 'market') !== false) $icon = 'fas fa-chart-line';
                elseif (stripos($dept, 'financ') !== false) $icon = 'fas fa-coins';
                elseif (stripos($dept, 'admin') !== false) $icon = 'fas fa-clipboard-list';
                elseif (stripos($dept, 'resource') !== false) $icon = 'fas fa-user-tie';
                elseif (stripos($dept, 'prod') !== false) $icon = 'fas fa-box';
                elseif (stripos($dept, 'design') !== false) $icon = 'fas fa-pencil-ruler';
                elseif (stripos($dept, 'legal') !== false) $icon = 'fas fa-balance-scale';
                elseif (stripos($dept, 'support') !== false) $icon = 'fas fa-headset';
                elseif (stripos($dept, 'sales') !== false) $icon = 'fas fa-shopping-cart';
        ?>
            <button class="department-btn" onclick="loadOrders('<?php echo $dept; ?>', this)">
                <i class="<?php echo $icon; ?>"></i> <?php echo $dept; ?>
            </button>
        <?php 
            }
        } else {
            echo '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>No departments found.</p></div>';
        }
        ?>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Orders List</h3>
            <div class="filter-controls" id="filter-controls" style="display: none;">
                <div class="filter-group">
                    <label for="status-filter">Status:</label>
                    <select id="status-filter" onchange="applyFilters()">
                        <option value="all">All</option>
                        <option value="validée">Approved</option>
                        <option value="rejetée">Rejected</option>
                        <option value="livrée">Delivered</option>
                    </select>
                </div>
                <div class="filter-group date-filter">
                    <label for="date-filter">Date:</label>
                    <input type="date" id="date-filter" onchange="applyFilters()">
                </div>
                <div class="search-filter">
                    <input type="text" id="search-input" placeholder="Search..." onkeyup="applyFilters()">
                </div>
                <div class="filter-actions">
                    <button class="reset-btn" onclick="resetFilters()">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div id="orders-container">
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <p>Select a department to view orders</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function loadOrders(department, buttonElement) {
        document.querySelectorAll('.department-btn').forEach(btn => btn.classList.remove('active'));
        if (buttonElement) buttonElement.classList.add('active');

        const container = document.getElementById('orders-container');
        const filterControls = document.getElementById('filter-controls');

        container.innerHTML = `
            <div class="loading">
                <i class="fas fa-spinner"></i> Loading orders for ${department === 'all' ? 'all departments' : department}...
            </div>
        `;
        filterControls.style.display = 'none';

        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_orders_by_department.php?department=' + encodeURIComponent(department), true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                if (xhr.responseText.trim() === '') {
                    container.innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-search"></i>
                            <p>No orders found for this department.</p>
                        </div>
                    `;
                    filterControls.style.display = 'none';
                } else {
                    container.innerHTML = xhr.responseText;
                    filterControls.style.display = 'flex';

                    document.getElementById('status-filter').value = 'all';
                    document.getElementById('search-input').value = '';
                    document.getElementById('date-filter').value = '';

                    document.querySelectorAll('.status-badge').forEach(badge => {
                        const statusClass = Array.from(badge.classList)
                            .find(cls => cls.startsWith('status-') && cls !== 'status-badge');
                        if (statusClass) {
                            badge.setAttribute('data-status', statusClass.replace('status-', ''));
                        }
                    });
                }
            } else {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Error loading orders.</p>
                    </div>
                `;
                filterControls.style.display = 'none';
            }
        };
        xhr.send();
    }

    function applyFilters() {
        const status = document.getElementById('status-filter').value;
        const searchText = document.getElementById('search-input').value.toLowerCase();
        const dateFilter = document.getElementById('date-filter').value;
        filterOrders(status, searchText, dateFilter);
    }

    function resetFilters() {
        document.getElementById('status-filter').value = 'all';
        document.getElementById('search-input').value = '';
        document.getElementById('date-filter').value = '';
        applyFilters();
    }

    function filterOrders(status, searchText, dateFilter) {
        const rows = document.querySelectorAll('.orders-table tbody tr');
        let visibleCount = 0;
        const filterDate = dateFilter ? new Date(dateFilter) : null;

        rows.forEach(row => {
            const statusCell = row.querySelector('.status-badge');
            const rowStatus = statusCell ? statusCell.getAttribute('data-status') : '';
            const statusMatch = status === 'all' || rowStatus === status;

            let searchMatch = true;
            if (searchText) {
                searchMatch = Array.from(row.querySelectorAll('td'))
                    .some(cell => cell.textContent.toLowerCase().includes(searchText));
            }

            let dateMatch = true;
            if (filterDate) {
                const dateCell = row.querySelector('td:nth-child(4)');
                if (dateCell) {
                    const dateString = dateCell.textContent.trim();
                    let rowDate;

                    if (dateString.includes('-')) {
                        rowDate = new Date(dateString);
                    } else if (dateString.includes('/')) {
                        const parts = dateString.split('/');
                        if (parts.length === 3) {
                            rowDate = new Date(`${parts[2]}-${parts[1]}-${parts[0]}`);
                        } else {
                            rowDate = new Date(dateString);
                        }
                    } else {
                        rowDate = new Date(dateString);
                    }

                    if (!isNaN(rowDate.getTime())) {
                        dateMatch = 
                            rowDate.getFullYear() === filterDate.getFullYear() && 
                            rowDate.getMonth() === filterDate.getMonth() && 
                            rowDate.getDate() === filterDate.getDate();
                    }
                }
            }

            if (statusMatch && searchMatch && dateMatch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const noResultsElement = document.getElementById('no-results-message');
        if (visibleCount === 0) {
            if (!noResultsElement) {
                const container = document.querySelector('.orders-table').parentNode;
                const message = document.createElement('div');
                message.id = 'no-results-message';
                message.className = 'empty-state';
                message.innerHTML = `
                    <i class="fas fa-search"></i>
                    <p>No orders match the selected filters</p>
                `;
                container.appendChild(message);
            }
            document.querySelector('.orders-table').style.display = 'none';
        } else {
            if (noResultsElement) {
                noResultsElement.remove();
            }
            document.querySelector('.orders-table').style.display = 'table';
        }
    }

    window.onload = function() {
        const firstDept = document.querySelector('.department-btn.active');
        if (firstDept) {
            loadOrders('all', firstDept);
        }
        document.getElementById('date-filter').max = formatDateForInput(new Date());
    };

    function formatDateForInput(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
</script>
</body>
</html>