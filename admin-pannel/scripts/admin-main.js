// Theme Switch
document.getElementById('theme-toggle').addEventListener('change', function () {
    var theme = this.checked ? 'dark' : 'light';
    document.body.className = theme;
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "../set_theme.php", true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send("theme=" + theme);
});

// Close the modal when clicking outside of it
window.onclick = function(event) {
    var modal = document.getElementById('createCategoryModal');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

// Close the modal when clicking the close button
document.querySelectorAll('.close').forEach(function (element) {
    element.onclick = function () {
        element.closest('.modal').style.display = 'none';
    }
});