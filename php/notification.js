function fetchNotifications() {
    fetch('get_department_notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.notifications.length > 0) {
                const notificationContainer = document.getElementById('notification-container');
                notificationContainer.innerHTML = '';
                
                data.notifications.forEach(notification => {
                    const notificationElement = document.createElement('div');
                    notificationElement.className = `notification notification-${notification.status}`;
                    notificationElement.innerHTML = `
                        <div class="notification-content">
                            <p>${notification.message}</p>
                        </div>
                    `;
                    notificationContainer.appendChild(notificationElement);
                });
                
                notificationContainer.style.display = 'block';
            }
        })
        .catch(error => console.error('Error fetching notifications:', error));
}

// Fetch notifications every 30 seconds
setInterval(fetchNotifications, 30000);

// Initial fetch
document.addEventListener('DOMContentLoaded', fetchNotifications);
