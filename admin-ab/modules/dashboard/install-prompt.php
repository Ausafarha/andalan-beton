<!-- Install App Prompt - muncul di dashboard admin -->
<style>
.install-app-banner {
    background: linear-gradient(135deg, #1e3a8a, #3b82f6);
    color: white;
    padding: 15px 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    display: none;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
}
.install-app-banner .btn-install {
    background: white;
    color: #1e3a8a;
    border: none;
    padding: 8px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
}
</style>

<div class="install-app-banner" id="installBanner">
    <div>
        <i class="fas fa-mobile-alt" style="font-size:20px;margin-right:10px;"></i>
        <span style="font-weight:600;">Install Aplikasi Andalan Beton</span>
        <span style="font-size:13px;opacity:0.8;display:block;margin-top:3px;">Akses lebih cepat, tanpa browser</span>
    </div>
    <button class="btn-install" id="installAppBtn">
        <i class="fas fa-download"></i> Install Sekarang
    </button>
</div>

<script>
let deferredPrompt;
const installBanner = document.getElementById('installBanner');
const installBtn = document.getElementById('installAppBtn');

// Deteksi event install PWA
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    installBanner.style.display = 'flex';
});

// Tombol install diklik
installBtn.addEventListener('click', async () => {
    if (deferredPrompt) {
        deferredPrompt.prompt();
        const result = await deferredPrompt.userChoice;
        if (result.outcome === 'accepted') {
            installBanner.style.display = 'none';
        }
        deferredPrompt = null;
    }
});

// Sembunyikan banner kalo udah terinstall
window.addEventListener('appinstalled', () => {
    installBanner.style.display = 'none';
});

// Cek apakah sudah install (di iOS atau standalone)
if (window.matchMedia('(display-mode: standalone)').matches) {
    installBanner.style.display = 'none';
}
</script>