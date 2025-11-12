# 📈 Profit/Loss Application (Aplikasi Laba/Rugi)

Aplikasi manajemen keuangan sederhana yang berfungsi untuk mencatat transaksi harian (pemasukan dan pengeluaran) dan menghasilkan Laporan Laba/Rugi secara real-time.

---

## 🛠️ 1. Tools dan Teknologi

Proyek ini dibangun menggunakan arsitektur *Full-Stack* dengan pemisahan antara *Frontend* (Nuxt.js) dan *Backend* (Laravel).

| Kategori | Teknologi | Deskripsi |
| :--- | :--- | :--- |
| **Frontend** | **Nuxt.js 3** (Vue 3) | Framework Vue.js untuk SSR dan struktur *frontend*. |
| | **Tailwind CSS** | Framework CSS utilitas untuk *styling* cepat dan responsif. |
| **Backend** | **Laravel** | Framework PHP untuk API RESTful, *routing*, dan *business logic*. |
| | **PHP** | Bahasa pemrograman utama di sisi *server*. |
| **Database** | **MySQL** | Sistem manajemen database relasional. |
| **Integrasi** | **RESTful API** | Digunakan sebagai jembatan komunikasi antara Nuxt dan Laravel. |
| | **RuntimeConfig** | Fitur Nuxt.js untuk konfigurasi variabel lingkungan API secara dinamis. |

---

## 📚 2. Struktur Data (Dataset)

Aplikasi menggunakan skema database akuntansi dasar yang melibatkan tiga tabel utama yang saling berelasi:
https://dbdiagram.io/d/Profit/Loss-Application-691472ac6735e11170709832 

| Tabel | Deskripsi | Relasi Kunci |
| :--- | :--- | :--- |
| **`coa_categories`** | Menyimpan kategori utama COA (misalnya: 'Salary', 'Transport Expense'). | - |
| **`chart_of_accounts`** | Menyimpan daftar akun buku besar (COA). | `category_id` (ke `coa_categories`) |
| **`transactions`** | Menyimpan semua jurnal transaksi (debit/kredit, tanggal, deskripsi). | `coa_id` (ke `chart_of_accounts`) |

**Poin Kunci Data:**
* Setiap `transaction` terhubung ke satu `ChartOfAccount`.
* Setiap `ChartOfAccount` terhubung ke satu `CoaCategory` yang menentukan apakah transaksi tersebut adalah Pemasukan atau Pengeluaran.

---

## ✨ 3. Fitur Utama

Berikut adalah fitur inti yang dikembangkan dalam aplikasi:

| Fitur | Deskripsi | Status |
| :--- | :--- | :--- |
| **Pencatatan Transaksi** | Fitur untuk menambah transaksi baru dengan detail Tanggal, Deskripsi, Jumlah, dan pemilihan Akun COA yang relevan. | ✅ Selesai |
| **Tampilan Transaksi** | Halaman utama (`/`) menampilkan semua data transaksi yang sudah diformat (tanggal, deskripsi, COA, tipe, dan jumlah) secara *real-time*. | ✅ Selesai |
| **Pelaporan Laba/Rugi** | Menampilkan ringkasan Pemasukan, Pengeluaran, dan Laba Bersih/Rugi selama periode tertentu (asumsi menggunakan halaman `/reports/profitloss`). | ✅ Selesai |
| **Pengelolaan COA** | Fungsionalitas untuk mengelola daftar Akun (Chart of Accounts) dan Kategori COA (Pemasukan/Pengeluaran). | ✅ Selesai (Logika) |
| **API Transaksi** | *Endpoint* API `/api/transactions` yang siap digunakan oleh *frontend* untuk menampilkan data tabel dengan *eager loading* relasi. | ✅ Selesai |

---

## 🚀 4. Alur Penggunaan Aplikasi

Berikut adalah langkah-langkah dasar untuk menggunakan aplikasi:

1.  **Persiapan Akun (COA):**
    * Pengguna perlu memastikan daftar Akun (COA) dan Kategorinya sudah terdefinisi di database (`chart_of_accounts` dan `coa_categories`).
    * Kategori COA harus memiliki tipe yang jelas (`Income` atau `Expense`).
2.  **Pencatatan Transaksi:**
    * Akses menu **Tambah Transaksi Baru**.
    * Masukkan **Tanggal**, **Deskripsi**, dan **Jumlah** transaksi.
    * Pilih **Akun COA** yang sesuai (misalnya, jika itu adalah biaya listrik, pilih akun 'Beban Listrik').
    * Simpan transaksi.
3.  **Memantau Transaksi:**
    * Akses **Halaman Utama (`/`)** untuk melihat tabel lengkap yang berisi semua transaksi yang telah dicatat (urut terbaru).
4.  **Menghasilkan Laporan:**
    * Akses **Laporan Laba/Rugi** (misalnya di `/reports/profitloss`).
    * Sistem akan menghitung total Pemasukan (dari COA bertipe 'Income') dikurangi total Pengeluaran (dari COA bertipe 'Expense') untuk menghasilkan nilai **Laba Bersih** atau **Rugi** periode tersebut.
