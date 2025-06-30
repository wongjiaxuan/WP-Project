document.addEventListener('DOMContentLoaded', function () {
    const successMsg = document.getElementById('success-msg');
    const params = new URLSearchParams(window.location.search);

    if (params.get("success") === "1") {
        if (successMsg) {
            successMsg.style.display = 'block';
        }

        // Show popup alert
        alert("✅ Budget saved successfully!");
    }
});