</div><!-- /.page-body -->
</div><!-- /.main-content -->
</div><!-- /.admin-wrapper -->

<!-- PWA Manifest Link -->
<link rel="manifest" href="<?= APP_URL ?>/manifest.json">
<meta name="theme-color" content="#20bc95">
<link rel="apple-touch-icon" href="<?= APP_URL ?>/assets/img/icon-192.png">

<!-- Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

<!-- Main JS -->


<!-- Service Worker Registration -->
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('<?= APP_URL ?>/service-worker.js')
            .then(function(registration) {
                console.log('Service Worker registered for admin');
            })
            .catch(function(err) {
                console.log('Service Worker registration failed: ', err);
            });
    });
}
</script>

<!-- Install Button (Admin) -->
<button id="installBtn" style="display:none;position:fixed;bottom:24px;right:24px;background:#20bc95;color:white;border:none;padding:14px 24px;border-radius:50px;box-shadow:0 4px 16px rgba(32,188,149,0.4);z-index:9999;font-weight:600;cursor:pointer;font-size:14px;">
    <i class="fas fa-download"></i> Install Aplikasi
</button>

<script>
// PWA Install Button
let deferredPrompt;
const installBtn = document.getElementById('installBtn');

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    installBtn.style.display = 'flex';
    installBtn.style.alignItems = 'center';
    installBtn.style.gap = '8px';
});

installBtn?.addEventListener('click', async () => {
    if (deferredPrompt) {
        deferredPrompt.prompt();
        const result = await deferredPrompt.userChoice;
        if (result.outcome === 'accepted') {
            installBtn.style.display = 'none';
        }
        deferredPrompt = null;
    }
});

window.addEventListener('appinstalled', () => {
    installBtn.style.display = 'none';
});

// Sembunyikan button kalo udah terinstall
if (window.matchMedia('(display-mode: standalone)').matches) {
    installBtn.style.display = 'none';
}
</script>

<?= $extraJs ?? '' ?>
</body>
</html>