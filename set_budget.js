document.addEventListener("DOMContentLoaded", function () {
  const msg = localStorage.getItem("budgetMsg");
  if (msg) {
    const alertBox = document.getElementById("alert-box");
    if (alertBox) {
      alertBox.textContent = msg;
      alertBox.style.display = "block";
    }

    localStorage.removeItem("budgetMsg");
  }
});
