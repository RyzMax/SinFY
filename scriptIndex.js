document.addEventListener('DOMContentLoaded', function() {
    const userBtn = document.getElementById('userBtn');
    const userDropdown = document.getElementById('userDropdown');

    userBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        userDropdown.classList.toggle('active');
    });


    document.addEventListener('click', function() {
        userDropdown.classList.remove('active');
    });
});
