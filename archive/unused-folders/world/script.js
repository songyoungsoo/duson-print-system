function updateClocks() {
    const now = new Date();
    document.getElementById('seoul-time').innerText = now.toLocaleTimeString('en-US', { timeZone: 'Asia/Seoul' });
    document.getElementById('new-york-time').innerText = now.toLocaleTimeString('en-US', { timeZone: 'America/New_York' });
}

const themeToggle = document.getElementById('theme-toggle');
themeToggle.addEventListener('click', () => {
    console.log('Theme toggle clicked');
    document.body.classList.toggle('dark-theme');
});

updateClocks();
setInterval(updateClocks, 1000);
