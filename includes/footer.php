</div> <!-- /container or /container-fluid -->

<!-- Bootstrap JS (Bundle includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Optional: Notification Polling Script -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    // Simple polling for notifications (you can customize this)
    fetch('notifications.php')
        .then(res => res.json())
        .then(data => {
            if (data.status === "success" && data.notifications.length > 0) {
                data.notifications.forEach(n => {
                    const toast = document.createElement('div');
                    toast.className = 'alert alert-info alert-dismissible fade show position-fixed bottom-0 end-0 m-3';
                    toast.style.zIndex = 1055;
                    toast.innerHTML = `
                        <strong>Notification:</strong> ${n.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                    document.body.appendChild(toast);
                    setTimeout(() => toast.remove(), 6000);
                });
            }
        });
});
</script>

</body>
</html>