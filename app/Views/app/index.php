<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($setting['app_name']) ?></title>
    <meta name="theme-color" content="#2f7fc7">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="<?= esc($setting['app_name']) ?>">
    <link rel="manifest" href="<?= base_url('manifest.webmanifest') ?>">
    <link rel="icon" type="image/svg+xml" href="<?= base_url('icons/icon.svg') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('icons/icon-192.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.4/dist/jspdf.plugin.autotable.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        soft: {
                            50: '#f4f9ff',
                            100: '#e5f1ff',
                            200: '#c9e3ff',
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
<body class="bg-soft-50 text-slate-800">
<div id="app" class="min-h-screen">
    <aside class="fixed inset-y-0 left-0 z-20 hidden w-64 border-r border-blue-100 bg-white/90 p-5 shadow-sm backdrop-blur lg:block">
        <div class="flex items-center gap-3">
            <div class="grid h-11 w-11 place-items-center overflow-hidden rounded-xl bg-soft-100 text-lg font-bold text-soft-700">
                <img v-if="setting.logo_url" :src="setting.logo_url" :alt="setting.app_name" class="h-full w-full object-contain p-1">
                <span v-else>C</span>
            </div>
            <div>
                <h1 class="font-semibold leading-tight">{{ setting.app_name }}</h1>
                <p class="text-xs capitalize text-slate-500">{{ user.role }}</p>
            </div>
        </div>

        <nav class="mt-8 space-y-2">
            <button @click="activeTab = 'dashboard'" :class="navClass('dashboard')" class="w-full rounded-xl px-4 py-3 text-left text-sm font-medium">Dashboard</button>
            <button @click="activeTab = 'data'" :class="navClass('data')" class="w-full rounded-xl px-4 py-3 text-left text-sm font-medium">Data</button>
            <button v-if="isSuperadmin" @click="activeTab = 'settings'" :class="navClass('settings')" class="w-full rounded-xl px-4 py-3 text-left text-sm font-medium">Setting Sistem</button>
        </nav>

        <a href="<?= site_url('logout') ?>" class="absolute bottom-5 left-5 right-5 rounded-xl border border-blue-100 px-4 py-3 text-center text-sm font-medium text-slate-600 hover:bg-blue-50">Logout</a>
    </aside>

    <main class="lg:pl-64">
        <header class="sticky top-0 z-10 border-b border-blue-100 bg-soft-50/85 px-4 py-4 backdrop-blur md:px-8">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4">
                <div>
                    <p class="text-sm text-slate-500">Halo, {{ user.name }}</p>
                    <h2 class="text-xl font-semibold md:text-2xl">{{ pageTitle }}</h2>
                </div>
                <div class="flex items-center gap-2">
                    <button v-if="installButtonVisible" @click="installApp" class="rounded-xl bg-soft-600 px-3 py-2 text-sm font-semibold text-white shadow-sm">
                        Install
                    </button>
                    <select v-model="activeTab" class="rounded-xl border border-blue-100 bg-white px-3 py-2 text-sm lg:hidden">
                        <option value="dashboard">Dashboard</option>
                        <option value="data">Data</option>
                        <option v-if="isSuperadmin" value="settings">Setting</option>
                    </select>
                    <a href="<?= site_url('logout') ?>" class="rounded-xl bg-white px-3 py-2 text-sm font-medium text-slate-600 shadow-sm lg:hidden">Logout</a>
                </div>
            </div>
        </header>

        <section class="mx-auto max-w-7xl px-4 py-6 md:px-8">
            <div v-if="activeTab === 'dashboard'" class="space-y-6">
                <div class="grid gap-4 md:grid-cols-3">
                    <article class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Saldo Saat Ini</p>
                        <p class="mt-3 text-3xl font-semibold text-soft-700">{{ money(summary.balance) }}</p>
                    </article>
                    <article class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Pemasukan Bulan Ini</p>
                        <p class="mt-3 text-3xl font-semibold text-emerald-600">{{ money(summary.income_month) }}</p>
                    </article>
                    <article class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Pengeluaran Bulan Ini</p>
                        <p class="mt-3 text-3xl font-semibold text-rose-500">{{ money(summary.expense_month) }}</p>
                    </article>
                </div>

                <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="font-semibold">Grafik Pemasukan & Pengeluaran</h3>
                            <p class="text-sm text-slate-500">Ringkasan 6 bulan terakhir</p>
                        </div>
                    </div>
                    <div class="h-80">
                        <canvas id="cashflowChart"></canvas>
                    </div>
                </div>

                <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <h3 class="font-semibold">Transaksi Terbaru Bulan Ini</h3>
                        <button @click="activeTab = 'data'" class="rounded-xl bg-soft-600 px-4 py-2 text-sm font-semibold text-white">Kelola Data</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="text-slate-500">
                                <tr>
                                    <th class="py-3">Tanggal</th>
                                    <th>Kategori</th>
                                    <th>Tipe</th>
                                    <th class="text-right">Nominal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-blue-50">
                                <tr v-for="row in transactions.slice(0, 6)" :key="row.id">
                                    <td class="py-3">{{ row.transaction_date }}</td>
                                    <td>{{ row.category }}</td>
                                    <td><span :class="typeBadge(row.type)">{{ typeLabel(row.type) }}</span></td>
                                    <td class="text-right font-semibold">{{ money(row.amount) }}</td>
                                </tr>
                                <tr v-if="transactions.length === 0">
                                    <td colspan="4" class="py-8 text-center text-slate-500">Belum ada transaksi bulan ini.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div v-if="activeTab === 'data'" class="space-y-6">
                <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <h3 class="font-semibold">Data Cashflow</h3>
                        <div class="flex flex-wrap gap-2">
                            <button @click="exportExcel" class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700">Export Excel</button>
                            <button @click="exportPdf" class="rounded-xl border border-rose-100 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-600">Export PDF</button>
                            <button @click="openTransactionModal()" class="rounded-xl bg-soft-600 px-4 py-2.5 text-sm font-semibold text-white">Tambah Data</button>
                        </div>
                    </div>

                    <div class="mb-4 grid gap-3 md:grid-cols-4">
                        <label class="text-sm font-medium text-slate-600">Dari
                            <input v-model="filters.start_date" type="date" class="mt-2 w-full rounded-xl border border-blue-100 px-3 py-2">
                        </label>
                        <label class="text-sm font-medium text-slate-600">Sampai
                            <input v-model="filters.end_date" type="date" class="mt-2 w-full rounded-xl border border-blue-100 px-3 py-2">
                        </label>
                        <label class="text-sm font-medium text-slate-600">Tipe
                            <select v-model="filters.type" class="mt-2 w-full rounded-xl border border-blue-100 px-3 py-2">
                                <option value="">Semua</option>
                                <option value="income">Pemasukan</option>
                                <option value="expense">Pengeluaran</option>
                            </select>
                        </label>
                        <div class="flex items-end gap-2">
                            <button @click="loadTransactions" class="w-full rounded-xl bg-soft-600 px-4 py-2.5 font-semibold text-white">Filter</button>
                            <button @click="setCurrentMonth" class="w-full rounded-xl border border-blue-100 px-4 py-2.5 font-semibold text-slate-600">Bulan Ini</button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="text-slate-500">
                                <tr>
                                    <th class="py-3">Tanggal</th>
                                    <th>Kategori</th>
                                    <th>Tipe</th>
                                    <th>Oleh</th>
                                    <th class="text-right">Nominal</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-blue-50">
                                <tr v-for="row in transactions" :key="row.id">
                                    <td class="py-3">{{ row.transaction_date }}</td>
                                    <td>
                                        <p class="font-medium">{{ row.category }}</p>
                                        <p class="max-w-xs truncate text-xs text-slate-500">{{ row.description }}</p>
                                    </td>
                                    <td><span :class="typeBadge(row.type)">{{ typeLabel(row.type) }}</span></td>
                                    <td>{{ row.created_by_name || '-' }}</td>
                                    <td class="text-right font-semibold">{{ money(row.amount) }}</td>
                                    <td class="text-right">
                                        <button @click="openTransactionModal(row)" class="rounded-lg bg-blue-50 px-3 py-1.5 font-medium text-soft-700">Edit</button>
                                        <button @click="deleteTransaction(row)" class="ml-2 rounded-lg bg-rose-50 px-3 py-1.5 font-medium text-rose-600">Hapus</button>
                                    </td>
                                </tr>
                                <tr v-if="transactions.length === 0">
                                    <td colspan="6" class="py-8 text-center text-slate-500">Tidak ada data pada filter ini.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div v-if="activeTab === 'settings' && isSuperadmin" class="space-y-6">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <button @click="openSettingModal('system')" class="rounded-2xl border border-blue-100 bg-white p-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="mb-4 grid h-11 w-11 place-items-center rounded-xl bg-soft-100 text-lg font-bold text-soft-700">S</div>
                        <h3 class="font-semibold">Sistem</h3>
                        <p class="mt-2 text-sm text-slate-500">{{ setting.app_name }} · {{ setting.currency }}</p>
                    </button>

                    <button @click="openSettingModal('logo')" class="rounded-2xl border border-blue-100 bg-white p-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="mb-4 grid h-11 w-11 place-items-center overflow-hidden rounded-xl bg-soft-100 text-lg font-bold text-soft-700">
                            <img v-if="setting.logo_url" :src="setting.logo_url" :alt="setting.app_name" class="h-full w-full object-contain p-1">
                            <span v-else>C</span>
                        </div>
                        <h3 class="font-semibold">Logo</h3>
                        <p class="mt-2 text-sm text-slate-500">{{ setting.logo_url ? 'Logo custom aktif' : 'Logo default aktif' }}</p>
                    </button>

                    <button @click="openSettingModal('categories')" class="rounded-2xl border border-blue-100 bg-white p-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="mb-4 grid h-11 w-11 place-items-center rounded-xl bg-emerald-50 text-lg font-bold text-emerald-700">K</div>
                        <h3 class="font-semibold">Kategori</h3>
                        <p class="mt-2 text-sm text-slate-500">{{ categories.length }} kategori pemasukan dan pengeluaran</p>
                    </button>

                    <button @click="openSettingModal('users')" class="rounded-2xl border border-blue-100 bg-white p-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="mb-4 grid h-11 w-11 place-items-center rounded-xl bg-blue-50 text-lg font-bold text-soft-700">U</div>
                        <h3 class="font-semibold">User</h3>
                        <p class="mt-2 text-sm text-slate-500">{{ users.length }} akun terdaftar</p>
                    </button>
                </div>

                <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="grid h-16 w-16 place-items-center overflow-hidden rounded-2xl bg-soft-100 text-2xl font-bold text-soft-700">
                                <img v-if="setting.logo_url" :src="setting.logo_url" :alt="setting.app_name" class="h-full w-full object-contain p-2">
                                <span v-else>C</span>
                            </div>
                            <div>
                                <h3 class="font-semibold">{{ setting.app_name }}</h3>
                                <p class="text-sm text-slate-500">Role aktif: {{ user.role }}</p>
                            </div>
                        </div>
                        <button @click="openSettingModal('system')" class="rounded-xl bg-soft-600 px-4 py-2.5 text-sm font-semibold text-white">Ubah Identitas</button>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <div v-if="transactionModalOpen" @click.self="closeTransactionModal" class="fixed inset-0 z-50 grid place-items-center bg-slate-900/40 px-4 py-6">
        <form @submit.prevent="saveTransaction" class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-blue-100 bg-white p-5 shadow-2xl">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="font-semibold">{{ transactionForm.id ? 'Edit Data' : 'Tambah Data' }}</h3>
                <button type="button" @click="closeTransactionModal" class="rounded-full bg-blue-50 px-3 py-1.5 text-sm font-semibold text-slate-600">Tutup</button>
            </div>
            <label class="block text-sm font-medium text-slate-600">Tanggal
                <input v-model="transactionForm.transaction_date" type="date" class="mt-2 w-full rounded-xl border border-blue-100 px-3 py-2.5 outline-none focus:border-soft-500">
            </label>
            <label class="mt-4 block text-sm font-medium text-slate-600">Tipe
                <select v-model="transactionForm.type" @change="syncTransactionCategory" class="mt-2 w-full rounded-xl border border-blue-100 px-3 py-2.5 outline-none focus:border-soft-500">
                    <option value="income">Pemasukan</option>
                    <option value="expense">Pengeluaran</option>
                </select>
            </label>
            <label class="mt-4 block text-sm font-medium text-slate-600">Kategori
                <select v-model="transactionForm.category" class="mt-2 w-full rounded-xl border border-blue-100 px-3 py-2.5 outline-none focus:border-soft-500">
                    <option value="" disabled>Pilih kategori</option>
                    <option v-for="category in activeCategoriesFor(transactionForm.type)" :key="category.id" :value="category.name">{{ category.name }}</option>
                </select>
            </label>
            <label class="mt-4 block text-sm font-medium text-slate-600">Nominal
                <input v-model.number="transactionForm.amount" type="number" min="1" step="0.01" class="mt-2 w-full rounded-xl border border-blue-100 px-3 py-2.5 outline-none focus:border-soft-500">
            </label>
            <label class="mt-4 block text-sm font-medium text-slate-600">Keterangan
                <textarea v-model.trim="transactionForm.description" rows="3" class="mt-2 w-full rounded-xl border border-blue-100 px-3 py-2.5 outline-none focus:border-soft-500"></textarea>
            </label>
            <div class="mt-5 flex gap-2">
                <button class="flex-1 rounded-xl bg-soft-600 px-4 py-2.5 font-semibold text-white">{{ transactionForm.id ? 'Simpan' : 'Tambah' }}</button>
                <button type="button" @click="resetTransactionForm" class="rounded-xl border border-blue-100 px-4 py-2.5 font-semibold text-slate-600">Reset</button>
            </div>
        </form>
    </div>

    <div v-if="settingModal === 'system'" @click.self="closeSettingModal" class="fixed inset-0 z-50 grid place-items-center bg-slate-900/40 px-4 py-6">
        <form @submit.prevent="saveSettings" class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-blue-100 bg-white p-5 shadow-2xl">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="font-semibold">Setting Sistem</h3>
                <button type="button" @click="closeSettingModal" class="rounded-full bg-blue-50 px-3 py-1.5 text-sm font-semibold text-slate-600">Tutup</button>
            </div>
            <label class="block text-sm font-medium text-slate-600">Nama Aplikasi
                <input v-model.trim="settingForm.app_name" class="mt-2 w-full rounded-xl border border-blue-100 px-3 py-2.5 outline-none focus:border-soft-500">
            </label>
            <label class="mt-4 block text-sm font-medium text-slate-600">Mata Uang
                <input v-model.trim="settingForm.currency" class="mt-2 w-full rounded-xl border border-blue-100 px-3 py-2.5 outline-none focus:border-soft-500">
            </label>
            <div class="mt-5 flex gap-2">
                <button class="flex-1 rounded-xl bg-soft-600 px-4 py-2.5 font-semibold text-white">Simpan</button>
                <button type="button" @click="settingForm = { ...setting }" class="rounded-xl border border-blue-100 px-4 py-2.5 font-semibold text-slate-600">Reset</button>
            </div>
        </form>
    </div>

    <div v-if="settingModal === 'logo'" @click.self="closeSettingModal" class="fixed inset-0 z-50 grid place-items-center bg-slate-900/40 px-4 py-6">
        <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-blue-100 bg-white p-5 shadow-2xl">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="font-semibold">Logo Sistem</h3>
                <button type="button" @click="closeSettingModal" class="rounded-full bg-blue-50 px-3 py-1.5 text-sm font-semibold text-slate-600">Tutup</button>
            </div>
            <div class="mb-4 flex items-center gap-4">
                <div class="grid h-20 w-20 place-items-center overflow-hidden rounded-2xl bg-soft-100 text-2xl font-bold text-soft-700">
                    <img v-if="setting.logo_url" :src="setting.logo_url" :alt="setting.app_name" class="h-full w-full object-contain p-2">
                    <span v-else>C</span>
                </div>
                <div class="min-w-0">
                    <p class="font-medium">{{ setting.app_name }}</p>
                    <p class="text-sm text-slate-500">{{ setting.logo_url ? 'Logo custom aktif' : 'Menggunakan logo default' }}</p>
                </div>
            </div>
            <input ref="logoInput" type="file" accept="image/png,image/jpeg,image/webp,image/gif" class="w-full rounded-xl border border-blue-100 bg-blue-50/40 px-3 py-2.5 text-sm">
            <div class="mt-4 flex gap-2">
                <button @click="uploadLogo" type="button" class="flex-1 rounded-xl bg-soft-600 px-4 py-2.5 font-semibold text-white">Upload Logo</button>
                <button v-if="setting.logo_url" @click="deleteLogo" type="button" class="rounded-xl border border-rose-100 px-4 py-2.5 font-semibold text-rose-600">Hapus</button>
            </div>
        </div>
    </div>

    <div v-if="settingModal === 'categories'" @click.self="closeSettingModal" class="fixed inset-0 z-50 grid place-items-center bg-slate-900/40 px-4 py-6">
        <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-2xl border border-blue-100 bg-white p-5 shadow-2xl">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="font-semibold">Kategori</h3>
                <button type="button" @click="closeSettingModal" class="rounded-full bg-blue-50 px-3 py-1.5 text-sm font-semibold text-slate-600">Tutup</button>
            </div>
            <form @submit.prevent="saveCategory" class="mb-5 grid gap-3 md:grid-cols-[160px_1fr_140px_auto]">
                <select v-model="categoryForm.type" class="rounded-xl border border-blue-100 px-3 py-2.5">
                    <option value="income">Pemasukan</option>
                    <option value="expense">Pengeluaran</option>
                </select>
                <input v-model.trim="categoryForm.name" placeholder="Nama kategori" class="rounded-xl border border-blue-100 px-3 py-2.5">
                <select v-model.number="categoryForm.is_active" class="rounded-xl border border-blue-100 px-3 py-2.5">
                    <option :value="1">Aktif</option>
                    <option :value="0">Nonaktif</option>
                </select>
                <div class="flex gap-2">
                    <button class="rounded-xl bg-soft-600 px-4 py-2.5 font-semibold text-white">{{ categoryForm.id ? 'Update' : 'Tambah' }}</button>
                    <button type="button" @click="resetCategoryForm" class="rounded-xl border border-blue-100 px-4 py-2.5 font-semibold text-slate-600">Reset</button>
                </div>
            </form>
            <div class="max-h-[55vh] overflow-y-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="text-slate-500">
                        <tr>
                            <th class="py-3">Nama</th>
                            <th>Tipe</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-blue-50">
                        <tr v-for="row in categories" :key="row.id">
                            <td class="py-3 font-medium">{{ row.name }}</td>
                            <td>{{ typeLabel(row.type) }}</td>
                            <td>{{ Number(row.is_active) === 1 ? 'Aktif' : 'Nonaktif' }}</td>
                            <td class="text-right">
                                <button @click="editCategory(row)" class="rounded-lg bg-blue-50 px-3 py-1.5 font-medium text-soft-700">Edit</button>
                                <button @click="deleteCategory(row)" class="ml-2 rounded-lg bg-rose-50 px-3 py-1.5 font-medium text-rose-600">Hapus</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div v-if="settingModal === 'users'" @click.self="closeSettingModal" class="fixed inset-0 z-50 grid place-items-center bg-slate-900/40 px-4 py-6">
        <div class="max-h-[90vh] w-full max-w-5xl overflow-y-auto rounded-2xl border border-blue-100 bg-white p-5 shadow-2xl">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="font-semibold">User</h3>
                <button type="button" @click="closeSettingModal" class="rounded-full bg-blue-50 px-3 py-1.5 text-sm font-semibold text-slate-600">Tutup</button>
            </div>
            <form @submit.prevent="saveUser" class="mb-5 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                <input v-model.trim="userForm.name" placeholder="Nama" class="rounded-xl border border-blue-100 px-3 py-2.5">
                <input v-model.trim="userForm.email" placeholder="Email" type="email" class="rounded-xl border border-blue-100 px-3 py-2.5">
                <input v-model="userForm.password" :placeholder="userForm.id ? 'Password baru opsional' : 'Password'" type="password" class="rounded-xl border border-blue-100 px-3 py-2.5">
                <select v-model="userForm.role" class="rounded-xl border border-blue-100 px-3 py-2.5">
                    <option value="admin">Admin</option>
                    <option value="superadmin">Superadmin</option>
                </select>
                <select v-model.number="userForm.is_active" class="rounded-xl border border-blue-100 px-3 py-2.5">
                    <option :value="1">Aktif</option>
                    <option :value="0">Nonaktif</option>
                </select>
                <div class="flex gap-2">
                    <button class="flex-1 rounded-xl bg-soft-600 px-4 py-2.5 font-semibold text-white">{{ userForm.id ? 'Update User' : 'Tambah User' }}</button>
                    <button type="button" @click="resetUserForm" class="rounded-xl border border-blue-100 px-4 py-2.5 font-semibold text-slate-600">Reset</button>
                </div>
            </form>
            <div class="max-h-[55vh] overflow-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="text-slate-500">
                        <tr>
                            <th class="py-3">Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-blue-50">
                        <tr v-for="row in users" :key="row.id">
                            <td class="py-3 font-medium">{{ row.name }}</td>
                            <td>{{ row.email }}</td>
                            <td class="capitalize">{{ row.role }}</td>
                            <td>{{ Number(row.is_active) === 1 ? 'Aktif' : 'Nonaktif' }}</td>
                            <td class="text-right">
                                <button @click="editUser(row)" class="rounded-lg bg-blue-50 px-3 py-1.5 font-medium text-soft-700">Edit</button>
                                <button @click="deleteUser(row)" class="ml-2 rounded-lg bg-rose-50 px-3 py-1.5 font-medium text-rose-600">Hapus</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
const { createApp } = Vue;
const initialUser = <?= json_encode($user) ?>;
const initialSetting = <?= json_encode($setting) ?>;
const initialCsrf = <?= json_encode(['header' => csrf_header(), 'hash' => csrf_hash()]) ?>;

createApp({
    data() {
        return {
            user: initialUser,
            setting: initialSetting,
            activeTab: 'dashboard',
            summary: { balance: 0, income_month: 0, expense_month: 0 },
            transactions: [],
            report: { labels: [], income: [], expense: [] },
            cashflowChart: null,
            categories: [],
            users: [],
            deferredInstallPrompt: null,
            isStandalone: false,
            csrf: initialCsrf,
            filters: { start_date: '', end_date: '', type: '' },
            transactionModalOpen: false,
            settingModal: null,
            transactionForm: this.blankTransaction(),
            categoryForm: this.blankCategory(),
            settingForm: { ...initialSetting },
            userForm: this.blankUser()
        };
    },
    computed: {
        isSuperadmin() {
            return this.user.role === 'superadmin';
        },
        pageTitle() {
            return { dashboard: 'Dashboard', data: 'Data Cashflow', settings: 'Setting Sistem' }[this.activeTab];
        },
        installButtonVisible() {
            return !this.isStandalone;
        }
    },
    mounted() {
        window.addEventListener('keydown', this.handleEscape);
        this.setupPwa();
        this.setCurrentMonth(false);
        this.loadSummary();
        this.loadMonthlyReport();
        this.loadCategories();
        this.loadTransactions();
        if (this.isSuperadmin) {
            this.loadUsers();
        }
    },
    beforeUnmount() {
        window.removeEventListener('keydown', this.handleEscape);
    },
    watch: {
        activeTab(tab) {
            if (tab === 'dashboard') {
                this.renderCashflowChart();
            }
        }
    },
    methods: {
        blankTransaction() {
            return { id: null, transaction_date: new Date().toISOString().slice(0, 10), type: 'income', category: '', description: '', amount: '' };
        },
        blankCategory() {
            return { id: null, type: 'income', name: '', is_active: 1 };
        },
        blankUser() {
            return { id: null, name: '', email: '', password: '', role: 'admin', is_active: 1 };
        },
        navClass(tab) {
            return this.activeTab === tab ? 'bg-soft-100 text-soft-700' : 'text-slate-600 hover:bg-blue-50';
        },
        openSettingModal(modal) {
            this.settingModal = modal;
            if (modal === 'system') {
                this.settingForm = { ...this.setting };
            }
            if (modal === 'categories') {
                this.resetCategoryForm();
            }
            if (modal === 'users') {
                this.resetUserForm();
            }
        },
        closeSettingModal() {
            this.settingModal = null;
            this.settingForm = { ...this.setting };
            this.resetCategoryForm();
            this.resetUserForm();
        },
        handleEscape(event) {
            if (event.key !== 'Escape') {
                return;
            }

            if (this.transactionModalOpen) {
                this.closeTransactionModal();
                return;
            }

            if (this.settingModal) {
                this.closeSettingModal();
            }
        },
        setupPwa() {
            this.isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

            window.addEventListener('beforeinstallprompt', (event) => {
                event.preventDefault();
                this.deferredInstallPrompt = event;
            });

            window.addEventListener('appinstalled', () => {
                this.isStandalone = true;
                this.deferredInstallPrompt = null;
            });

            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('<?= base_url('sw.js') ?>');
                });
            }
        },
        async installApp() {
            if (this.deferredInstallPrompt) {
                this.deferredInstallPrompt.prompt();
                await this.deferredInstallPrompt.userChoice;
                this.deferredInstallPrompt = null;
                return;
            }

            Swal.fire({
                icon: 'info',
                title: 'Install aplikasi',
                text: /iphone|ipad|ipod/i.test(navigator.userAgent)
                    ? 'Di Safari iOS, tekan tombol Share lalu pilih Add to Home Screen.'
                    : 'Buka menu browser lalu pilih Install app atau Add to Home screen.'
            });
        },
        money(value) {
            return `${this.setting.currency} ${Number(value || 0).toLocaleString('id-ID')}`;
        },
        typeLabel(type) {
            return type === 'income' ? 'Pemasukan' : 'Pengeluaran';
        },
        typeBadge(type) {
            return type === 'income'
                ? 'rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700'
                : 'rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-600';
        },
        activeCategoriesFor(type) {
            return this.categories.filter((category) => category.type === type && Number(category.is_active) === 1);
        },
        syncTransactionCategory() {
            const options = this.activeCategoriesFor(this.transactionForm.type);
            if (!options.some((category) => category.name === this.transactionForm.category)) {
                this.transactionForm.category = options[0]?.name || '';
            }
        },
        setCurrentMonth(load = true) {
            const now = new Date();
            const start = new Date(now.getFullYear(), now.getMonth(), 1);
            const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            this.filters.start_date = this.formatDate(start);
            this.filters.end_date = this.formatDate(end);
            this.filters.type = '';
            if (load) this.loadTransactions();
        },
        formatDate(date) {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        },
        async request(url, options = {}) {
            const headers = {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                [this.csrf.header]: this.csrf.hash,
                ...(options.headers || {})
            };

            const response = await fetch(url, {
                ...options,
                headers
            });
            const json = await response.json().catch(() => ({}));
            if (!response.ok) {
                const message = json.message || Object.values(json.errors || {})[0] || 'Terjadi kesalahan.';
                throw new Error(message);
            }
            return json;
        },
        async loadSummary() {
            this.summary = await this.request('<?= site_url('api/summary') ?>');
        },
        async loadMonthlyReport() {
            this.report = await this.request('<?= site_url('api/reports/monthly') ?>');
            this.renderCashflowChart();
        },
        renderCashflowChart() {
            this.$nextTick(() => {
                const canvas = document.getElementById('cashflowChart');
                if (!canvas || typeof Chart === 'undefined') {
                    return;
                }

                if (this.cashflowChart) {
                    this.cashflowChart.destroy();
                }

                this.cashflowChart = new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: this.report.labels,
                        datasets: [
                            {
                                label: 'Pemasukan',
                                data: this.report.income,
                                backgroundColor: '#10b981',
                                borderRadius: 8
                            },
                            {
                                label: 'Pengeluaran',
                                data: this.report.expense,
                                backgroundColor: '#f43f5e',
                                borderRadius: 8
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' },
                            tooltip: {
                                callbacks: {
                                    label: (context) => `${context.dataset.label}: ${this.money(context.raw)}`
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: (value) => this.money(value)
                                }
                            }
                        }
                    }
                });
            });
        },
        async loadCategories() {
            const url = this.isSuperadmin ? '<?= site_url('api/categories') ?>?all=1' : '<?= site_url('api/categories') ?>';
            const result = await this.request(url);
            this.categories = result.data;
            this.syncTransactionCategory();
        },
        async loadTransactions() {
            const params = new URLSearchParams(this.filters);
            const result = await this.request(`<?= site_url('api/transactions') ?>?${params}`);
            this.transactions = result.data;
        },
        openTransactionModal(row = null) {
            this.transactionForm = row ? { ...row, amount: Number(row.amount) } : this.blankTransaction();
            this.syncTransactionCategory();
            this.transactionModalOpen = true;
        },
        closeTransactionModal() {
            this.transactionModalOpen = false;
            this.resetTransactionForm();
        },
        resetTransactionForm() {
            this.transactionForm = this.blankTransaction();
            this.syncTransactionCategory();
        },
        async saveTransaction() {
            try {
                const isEdit = Boolean(this.transactionForm.id);
                const url = isEdit ? `<?= site_url('api/transactions') ?>/${this.transactionForm.id}` : '<?= site_url('api/transactions') ?>';
                const method = isEdit ? 'PUT' : 'POST';
                const result = await this.request(url, { method, body: JSON.stringify(this.transactionForm) });
                await Swal.fire({ icon: 'success', title: 'Berhasil', text: result.message, timer: 1400, showConfirmButton: false });
                this.closeTransactionModal();
                await Promise.all([this.loadSummary(), this.loadMonthlyReport(), this.loadTransactions()]);
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'Gagal', text: error.message });
            }
        },
        async deleteTransaction(row) {
            const confirm = await Swal.fire({ icon: 'warning', title: 'Hapus data?', text: row.category, showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal' });
            if (!confirm.isConfirmed) return;
            try {
                const result = await this.request(`<?= site_url('api/transactions') ?>/${row.id}`, { method: 'DELETE' });
                await Swal.fire({ icon: 'success', title: 'Berhasil', text: result.message, timer: 1200, showConfirmButton: false });
                await Promise.all([this.loadSummary(), this.loadMonthlyReport(), this.loadTransactions()]);
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'Gagal', text: error.message });
            }
        },
        exportRows() {
            return this.transactions.map((row) => ({
                Tanggal: row.transaction_date,
                Tipe: this.typeLabel(row.type),
                Kategori: row.category,
                Keterangan: row.description || '',
                Oleh: row.created_by_name || '-',
                Nominal: Number(row.amount || 0)
            }));
        },
        exportFileName(extension) {
            return `cashflow-${this.filters.start_date}-sd-${this.filters.end_date}.${extension}`;
        },
        exportExcel() {
            const rows = this.exportRows();
            if (rows.length === 0) {
                Swal.fire({ icon: 'info', title: 'Tidak ada data', text: 'Tidak ada data untuk diexport pada filter ini.' });
                return;
            }

            if (typeof XLSX === 'undefined') {
                Swal.fire({ icon: 'error', title: 'Gagal', text: 'Library Excel belum termuat.' });
                return;
            }

            const worksheet = XLSX.utils.json_to_sheet(rows);
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, 'Cashflow');
            XLSX.writeFile(workbook, this.exportFileName('xlsx'));
        },
        exportPdf() {
            const rows = this.exportRows();
            if (rows.length === 0) {
                Swal.fire({ icon: 'info', title: 'Tidak ada data', text: 'Tidak ada data untuk diexport pada filter ini.' });
                return;
            }

            if (!window.jspdf?.jsPDF) {
                Swal.fire({ icon: 'error', title: 'Gagal', text: 'Library PDF belum termuat.' });
                return;
            }

            const doc = new window.jspdf.jsPDF({ orientation: 'landscape' });
            doc.setFontSize(14);
            doc.text(`${this.setting.app_name} - Laporan Cashflow`, 14, 14);
            doc.setFontSize(10);
            doc.text(`Periode: ${this.filters.start_date} s/d ${this.filters.end_date}`, 14, 21);
            doc.autoTable({
                startY: 28,
                head: [['Tanggal', 'Tipe', 'Kategori', 'Keterangan', 'Oleh', 'Nominal']],
                body: rows.map((row) => [
                    row.Tanggal,
                    row.Tipe,
                    row.Kategori,
                    row.Keterangan,
                    row.Oleh,
                    this.money(row.Nominal)
                ]),
                styles: { fontSize: 8 },
                headStyles: { fillColor: [47, 127, 199] }
            });
            doc.save(this.exportFileName('pdf'));
        },
        async saveCategory() {
            try {
                const isEdit = Boolean(this.categoryForm.id);
                const url = isEdit ? `<?= site_url('api/categories') ?>/${this.categoryForm.id}` : '<?= site_url('api/categories') ?>';
                const method = isEdit ? 'PUT' : 'POST';
                const result = await this.request(url, { method, body: JSON.stringify(this.categoryForm) });
                await Swal.fire({ icon: 'success', title: 'Berhasil', text: result.message, timer: 1400, showConfirmButton: false });
                this.resetCategoryForm();
                await this.loadCategories();
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'Gagal', text: error.message });
            }
        },
        editCategory(row) {
            this.categoryForm = { ...row, is_active: Number(row.is_active) };
        },
        resetCategoryForm() {
            this.categoryForm = this.blankCategory();
        },
        async deleteCategory(row) {
            const confirm = await Swal.fire({ icon: 'warning', title: 'Hapus kategori?', text: row.name, showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal' });
            if (!confirm.isConfirmed) return;
            try {
                const result = await this.request(`<?= site_url('api/categories') ?>/${row.id}`, { method: 'DELETE' });
                await Swal.fire({ icon: 'success', title: 'Berhasil', text: result.message, timer: 1200, showConfirmButton: false });
                await this.loadCategories();
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'Gagal', text: error.message });
            }
        },
        async loadUsers() {
            const result = await this.request('<?= site_url('api/users') ?>');
            this.users = result.data;
        },
        async saveSettings() {
            try {
                const result = await this.request('<?= site_url('api/settings') ?>', { method: 'PUT', body: JSON.stringify(this.settingForm) });
                this.setting = result.data;
                this.settingForm = { ...result.data };
                Swal.fire({ icon: 'success', title: 'Berhasil', text: result.message });
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'Gagal', text: error.message });
            }
        },
        async uploadLogo() {
            const file = this.$refs.logoInput?.files?.[0];
            if (!file) {
                Swal.fire({ icon: 'info', title: 'Pilih logo', text: 'Pilih file logo terlebih dahulu.' });
                return;
            }

            const formData = new FormData();
            formData.append('logo', file);

            try {
                const response = await fetch('<?= site_url('api/settings/logo') ?>', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', [this.csrf.header]: this.csrf.hash },
                    body: formData
                });
                const result = await response.json().catch(() => ({}));
                if (!response.ok) {
                    throw new Error(result.message || 'Logo gagal diupload.');
                }

                this.setting = result.data;
                this.settingForm = { ...result.data };
                this.$refs.logoInput.value = '';
                Swal.fire({ icon: 'success', title: 'Berhasil', text: result.message });
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'Gagal', text: error.message });
            }
        },
        async deleteLogo() {
            const confirm = await Swal.fire({ icon: 'warning', title: 'Hapus logo?', text: 'Identitas sistem akan kembali memakai logo default.', showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal' });
            if (!confirm.isConfirmed) return;

            try {
                const result = await this.request('<?= site_url('api/settings/logo') ?>', { method: 'DELETE' });
                this.setting = result.data;
                this.settingForm = { ...result.data };
                Swal.fire({ icon: 'success', title: 'Berhasil', text: result.message });
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'Gagal', text: error.message });
            }
        },
        async saveUser() {
            try {
                const isEdit = Boolean(this.userForm.id);
                const url = isEdit ? `<?= site_url('api/users') ?>/${this.userForm.id}` : '<?= site_url('api/users') ?>';
                const method = isEdit ? 'PUT' : 'POST';
                const result = await this.request(url, { method, body: JSON.stringify(this.userForm) });
                await Swal.fire({ icon: 'success', title: 'Berhasil', text: result.message, timer: 1400, showConfirmButton: false });
                this.resetUserForm();
                await this.loadUsers();
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'Gagal', text: error.message });
            }
        },
        editUser(row) {
            this.userForm = { ...row, password: '', is_active: Number(row.is_active) };
        },
        resetUserForm() {
            this.userForm = this.blankUser();
        },
        async deleteUser(row) {
            const confirm = await Swal.fire({ icon: 'warning', title: 'Hapus user?', text: row.name, showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal' });
            if (!confirm.isConfirmed) return;
            try {
                const result = await this.request(`<?= site_url('api/users') ?>/${row.id}`, { method: 'DELETE' });
                await Swal.fire({ icon: 'success', title: 'Berhasil', text: result.message, timer: 1200, showConfirmButton: false });
                await this.loadUsers();
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'Gagal', text: error.message });
            }
        }
    }
}).mount('#app');
</script>
</body>
</html>
