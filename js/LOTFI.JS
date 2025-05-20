document.addEventListener('DOMContentLoaded', function() {
    // Toggle notification dropdown
    const notificationToggle = document.querySelector('[data-bs-toggle="dropdown"]');
    
    // Mark all as read functionality
    const markAllAsRead = document.querySelector('.notification-header a');
    markAllAsRead.addEventListener('click', function(e) {
        e.preventDefault();
        const badge = document.querySelector('.notification-badge');
        badge.textContent = '0';
        badge.classList.remove('has-new');
    });
    
    // Approve/Reject buttons functionality
    const actionButtons = document.querySelectorAll('.notification-actions button');
    actionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const notificationItem = this.closest('.notification-item');
            const isApprove = this.classList.contains('btn-success');
            
            // Add visual feedback
            if (isApprove) {
                notificationItem.style.backgroundColor = 'rgba(40, 167, 69, 0.1)';
            } else {
                notificationItem.style.backgroundColor = 'rgba(220, 53, 69, 0.1)';
            }
            
            // Disable buttons
            this.closest('.notification-actions').querySelectorAll('button').forEach(btn => {
                btn.disabled = true;
            });
            
            // Update notification text
            const statusText = isApprove ? 'Approuvée' : 'Rejetée';
            const notificationText = notificationItem.querySelector('.notification-text');
            notificationText.innerHTML = notificationText.innerHTML + ` <span class="badge ${isApprove ? 'bg-success' : 'bg-danger'}">${statusText}</span>`;
            
            // Update badge count
            const badge = document.querySelector('.notification-badge');
            let count = parseInt(badge.textContent);
            if (count > 0) {
                badge.textContent = count - 1;
                if (count - 1 === 0) {
                    badge.classList.remove('has-new');
                }
            }
        });
    });
});
document.addEventListener('DOMContentLoaded', function() {
    // Activer tous les tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
    
    // Activer tous les dropdowns
    var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'))
    var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl)
    });
    
    // Ajouter des écouteurs d'événements pour les boutons de détails
    var detailButtons = document.querySelectorAll('.btn-info');
    detailButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.stopPropagation(); // Empêcher la fermeture du dropdown
            var accountDetailModal = new bootstrap.Modal(document.getElementById('accountDetailModal'))
            accountDetailModal.show();
        });
    });
});




