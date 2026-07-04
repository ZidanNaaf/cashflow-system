<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login <?= esc($setting['app_name']) ?></title>
    <meta name="theme-color" content="#2f7fc7">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="<?= esc($setting['app_name']) ?>">
    <link rel="manifest" href="<?= base_url('manifest.webmanifest') ?>">
    <link rel="icon" type="image/svg+xml" href="<?= base_url('icons/icon.svg') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('icons/icon-192.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        soft: {
                            50: '#f4f9ff',
                            100: '#e5f1ff',
                            500: '#4d9eea',
                            600: '#2f7fc7',
                            700: '#2169a8'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-soft-50 text-slate-800">
    <main class="min-h-screen grid place-items-center px-4 py-10">
        <section class="w-full max-w-md">
            <div class="mb-8 text-center">
                <div class="mx-auto mb-4 grid h-16 w-16 place-items-center overflow-hidden rounded-2xl bg-soft-100 text-soft-700 shadow-sm">
                    <?php if (! empty($setting['logo_url'])) : ?>
                        <img src="<?= esc($setting['logo_url']) ?>" alt="<?= esc($setting['app_name']) ?>" class="h-full w-full object-contain p-1">
                    <?php else : ?>
                        <span class="text-2xl font-bold">C</span>
                    <?php endif; ?>
                </div>
                <h1 class="text-3xl font-semibold tracking-tight"><?= esc($setting['app_name']) ?></h1>
                <p class="mt-2 text-sm text-slate-500">Masuk untuk mengelola pemasukan dan pengeluaran.</p>
            </div>

            <form action="<?= site_url('login') ?>" method="post" class="rounded-2xl border border-blue-100 bg-white p-6 shadow-xl shadow-blue-100/60">
                <?= csrf_field() ?>
                <label class="block">
                    <span class="text-sm font-medium text-slate-600">Email</span>
                    <input name="email" type="email" value="<?= esc(old('email')) ?>" required autofocus
                        class="mt-2 w-full rounded-xl border border-blue-100 bg-blue-50/40 px-4 py-3 outline-none transition focus:border-soft-500 focus:ring-4 focus:ring-blue-100">
                </label>

                <label class="mt-4 block">
                    <span class="text-sm font-medium text-slate-600">Password</span>
                    <div class="mt-2 flex rounded-xl border border-blue-100 bg-blue-50/40 transition focus-within:border-soft-500 focus-within:ring-4 focus-within:ring-blue-100">
                        <input id="password" name="password" type="password" required
                            class="min-w-0 flex-1 rounded-l-xl bg-transparent px-4 py-3 outline-none">
                        <button id="togglePassword" type="button" class="shrink-0 rounded-r-xl px-4 text-sm font-semibold text-soft-700">
                            Lihat
                        </button>
                    </div>
                </label>

                <label class="mt-4 flex items-center gap-3 text-sm font-medium text-slate-600">
                    <input name="remember" type="checkbox" value="1" class="h-4 w-4 rounded border-blue-200 text-soft-600 focus:ring-soft-500">
                    <span>Ingat saya</span>
                </label>

                <button class="mt-6 w-full rounded-xl bg-soft-600 px-4 py-3 font-semibold text-white shadow-lg shadow-blue-200 transition hover:bg-soft-700">
                    Login
                </button>

                <div class="mt-6 rounded-xl bg-blue-50 px-4 py-3 text-sm text-slate-600">
                    <p class="font-medium text-slate-700">Akun awal</p>
                    <p class="mt-1">superadmin@cashflow.local / superadmin123</p>
                    <p>admin@cashflow.local / admin123</p>
                </div>
            </form>

            <button id="installApp" type="button" class="mt-4 w-full rounded-xl border border-blue-100 bg-white px-4 py-3 text-sm font-semibold text-soft-700 shadow-sm">
                Install aplikasi
            </button>
        </section>
    </main>

    <?php if (session()->getFlashdata('error')) : ?>
        <script>
            Swal.fire({ icon: 'error', title: 'Login gagal', text: <?= json_encode(session()->getFlashdata('error')) ?> });
        </script>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')) : ?>
        <script>
            Swal.fire({ icon: 'success', title: 'Berhasil', text: <?= json_encode(session()->getFlashdata('success')) ?> });
        </script>
    <?php endif; ?>

    <script>
        const password = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');

        togglePassword.addEventListener('click', () => {
            const isHidden = password.type === 'password';
            password.type = isHidden ? 'text' : 'password';
            togglePassword.textContent = isHidden ? 'Sembunyikan' : 'Lihat';
        });

        let deferredInstallPrompt = null;
        const installButton = document.getElementById('installApp');
        const standalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;

        if (standalone) {
            installButton.classList.add('hidden');
        }

        window.addEventListener('beforeinstallprompt', (event) => {
            event.preventDefault();
            deferredInstallPrompt = event;
        });

        window.addEventListener('appinstalled', () => {
            installButton.classList.add('hidden');
            deferredInstallPrompt = null;
        });

        installButton.addEventListener('click', async () => {
            if (deferredInstallPrompt) {
                deferredInstallPrompt.prompt();
                await deferredInstallPrompt.userChoice;
                deferredInstallPrompt = null;
                return;
            }

            Swal.fire({
                icon: 'info',
                title: 'Install aplikasi',
                text: /iphone|ipad|ipod/i.test(navigator.userAgent)
                    ? 'Di Safari iOS, tekan tombol Share lalu pilih Add to Home Screen.'
                    : 'Buka menu browser lalu pilih Install app atau Add to Home screen.'
            });
        });

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('<?= base_url('sw.js') ?>');
            });
        }
    </script>
</body>
</html>
