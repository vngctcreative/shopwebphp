document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('theme-toggle');
    themeToggle.addEventListener('change', function() {
        const form = document.createElement('form');
        form.method = 'post';
        form.style.display = 'none';

        const input = document.createElement('input');
        input.name = 'theme';
        input.value = themeToggle.checked ? 'dark' : 'light';
        form.appendChild(input);

        document.body.appendChild(form);
        form.submit();
    });

    // Initialize the default category
    showCategory('account');
});