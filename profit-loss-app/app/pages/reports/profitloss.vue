<script setup>
import { ref, reactive, computed } from 'vue';

const config = useRuntimeConfig();
const API_BASE = config.public.apiBase;

// --- STATE ---
const reportDates = reactive({
    // Set default ke awal dan akhir bulan lalu untuk demo
    start_date: new Date(new Date().getFullYear(), new Date().getMonth() - 1, 1).toISOString().substring(0, 10),
    end_date: new Date(new Date().getFullYear(), new Date().getMonth(), 0).toISOString().substring(0, 10),
});

const reportData = ref(null); // Data detail per kategori/bulan
const reportSummary = ref(null); // Data Total Income, Expense, Net Income
const isLoading = ref(false);
const fetchError = ref(null);

// --- FETCHING LOGIC (D2.2) ---

const fetchReport = async () => {
    // Validasi sederhana
    if (!reportDates.start_date || !reportDates.end_date) {
        alert('Mohon lengkapi Periode Mulai dan Akhir.');
        return;
    }
    
    isLoading.value = true;
    fetchError.value = null;
    reportData.value = null;
    reportSummary.value = null;

    try {
        const response = await $fetch('/reports/profitloss', {
            method: 'GET',
            baseURL: API_BASE,
            params: reportDates, // Kirim start_date dan end_date sebagai query parameter
        });

        reportData.value = response.data;
        reportSummary.value = response.summary;

    } catch (err) {
        console.error('Laporan API Error:', err);
        fetchError.value = 'Gagal memuat laporan. Pastikan API berjalan dan rentang tanggal valid.';
    } finally {
        isLoading.value = false;
    }
};

// Panggil laporan saat halaman pertama dimuat
onMounted(() => {
    fetchReport();
});


// --- COMPUTED PROPERTIES UNTUK TAMPILAN TABEL ---

/**
 * Menghasilkan daftar unik bulan (kolom dinamis) dari data laporan.
 * Output: ['2024-01', '2024-02', ...]
 */
const dynamicMonths = computed(() => {
    if (!reportData.value || reportData.value.length === 0) return [];
    // Ambil semua month_year dari setiap item di reportData
    return reportData.value.map(item => item.month_year);
});

/**
 * Menghasilkan daftar unik kategori dari data laporan, 
 * memastikan setiap kategori hanya muncul sekali di baris tabel.
 * Output: { 1: 'Salary', 2: 'Other Income', ... }
 */
const uniqueCategories = computed(() => {
    if (!reportData.value) return {};
    
    const categoriesMap = new Map();
    
    reportData.value.forEach(monthItem => {
        monthItem.categories.forEach(cat => {
            if (!categoriesMap.has(cat.category_id)) {
                categoriesMap.set(cat.category_id, cat.category_name);
            }
        });
    });

    // Ubah Map menjadi objek biasa untuk kemudahan looping di template
    return Object.fromEntries(categoriesMap);
});

/**
 * Fungsi untuk mencari nilai (net_value) kategori tertentu pada bulan tertentu.
 * Digunakan untuk mengisi sel-sel tabel.
 */
const getCategoryValueByMonth = (categoryId, monthYear) => {
    if (!reportData.value) return 0;
    
    // Cari data untuk bulanYear yang spesifik
    const monthData = reportData.value.find(item => item.month_year === monthYear);
    
    if (monthData) {
        // Cari data kategori di dalam bulan tersebut
        const categoryData = monthData.categories.find(cat => cat.category_id === categoryId);
        
        // Kembalikan nilai bersih (net_value) atau 0 jika tidak ada transaksi
        return categoryData ? categoryData.net_value : 0;
    }
    return 0;
};

// Helper untuk format mata uang
const formatCurrency = (value) => {
    if (value === 0) return '0';
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 2 }).format(value);
};

// Helper untuk format nama bulan
const formatMonthName = (monthYear) => {
    const [year, month] = monthYear.split('-');
    const date = new Date(year, month - 1, 1);
    return new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' }).format(date);
};
</script>

<template>
  <div class="p-8 max-w-full mx-auto my-10">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">📊 Laporan Laba Rugi (Profit & Loss)</h1>

    <div class="bg-white shadow-lg p-6 rounded-xl mb-8 border border-gray-100">
        <h2 class="text-xl font-semibold mb-4 text-indigo-700">Filter Periode</h2>
        <form @submit.prevent="fetchReport" class="flex flex-wrap items-end gap-4">
            
            <div class="flex-1 min-w-[200px]">
                <label for="start_date" class="block text-sm font-medium text-gray-700">Periode Mulai</label>
                <input
                    id="start_date"
                    v-model="reportDates.start_date"
                    type="date"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2"
                >
            </div>

            <div class="flex-1 min-w-[200px]">
                <label for="end_date" class="block text-sm font-medium text-gray-700">Periode Akhir</label>
                <input
                    id="end_date"
                    v-model="reportDates.end_date"
                    type="date"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2"
                >
            </div>
            
            <button
                type="submit"
                :disabled="isLoading"
                class="px-6 py-2 bg-indigo-600 text-white font-medium rounded-lg shadow-md hover:bg-indigo-700 transition duration-150 disabled:opacity-50 h-[42px]"
            >
                {{ isLoading ? 'Memuat...' : 'Tampilkan Laporan' }}
            </button>
        </form>
    </div>

    <div v-if="fetchError" class="p-4 bg-red-100 text-red-700 rounded-lg">{{ fetchError }}</div>
    <div v-else-if="isLoading && !reportData" class="p-4 text-center text-gray-500">Memuat data laporan...</div>
    <div v-else-if="reportData && reportData.length === 0" class="p-4 bg-yellow-100 text-yellow-700 rounded-lg">Tidak ada data transaksi dalam rentang tanggal ini.</div>

    <div v-else-if="reportData && reportData.length > 0" class="bg-white shadow-xl rounded-xl overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 sticky top-0">
                <tr>
                    <th rowspan="2" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50 z-10 w-40">Kategori COA</th>
                    
                    <th 
                        v-for="month in dynamicMonths" 
                        :key="month" 
                        colspan="1" 
                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-l border-gray-200"
                    >
                        {{ formatMonthName(month) }}
                    </th>
                    
                    <th rowspan="2" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-32 border-l border-gray-200">Total Periode</th>
                </tr>
            </thead>
            
            <tbody class="divide-y divide-gray-200">
                
                <tr v-for="(catName, catId) in uniqueCategories" :key="catId" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 sticky left-0 bg-white z-10">
                        {{ catName }}
                    </td>
                    
                    <td 
                        v-for="month in dynamicMonths" 
                        :key="`${catId}-${month}`" 
                        class="px-6 py-4 whitespace-nowrap text-sm text-right border-l border-gray-100"
                        :class="{'text-green-600 font-medium': getCategoryValueByMonth(catId, month) > 0, 'text-red-600': getCategoryValueByMonth(catId, month) < 0}"
                    >
                        {{ formatCurrency(getCategoryValueByMonth(catId, month)) }}
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-right border-l border-gray-200 bg-gray-50">
                        {{ formatCurrency(
                            dynamicMonths.reduce((sum, month) => sum + getCategoryValueByMonth(catId, month), 0)
                        ) }}
                    </td>
                </tr>
                
                <tr class="bg-indigo-50 font-bold border-t-2 border-indigo-200">
                    <td class="px-6 py-3 text-left sticky left-0 bg-indigo-50 z-10">TOTAL NET INCOME</td>
                    
                    <td 
                        v-for="month in dynamicMonths" 
                        :key="`total-${month}`" 
                        class="px-6 py-3 text-right border-l border-indigo-200"
                    >
                        {{ formatCurrency(
                            Object.keys(uniqueCategories).reduce((sum, catId) => sum + getCategoryValueByMonth(Number(catId), month), 0)
                        ) }}
                    </td>
                    
                    <td class="px-6 py-3 text-right border-l border-indigo-200">
                        {{ reportSummary ? formatCurrency(reportSummary.net_income) : 'N/A' }}
                    </td>
                </tr>
            </tbody>
        </table>
        
        <div class="p-6 border-t mt-4">
            <h3 class="text-xl font-bold mb-3">Ringkasan Periode</h3>
            <div class="grid grid-cols-3 gap-4 text-center">
                <div class="p-3 bg-green-50 rounded-lg">
                    <p class="text-sm text-gray-600">Total Pemasukan (Debit)</p>
                    <p class="text-lg font-bold text-green-700">{{ formatCurrency(reportSummary.total_income) }}</p>
                </div>
                <div class="p-3 bg-red-50 rounded-lg">
                    <p class="text-sm text-gray-600">Total Pengeluaran (Kredit)</p>
                    <p class="text-lg font-bold text-red-700">{{ formatCurrency(reportSummary.total_expense) }}</p>
                </div>
                <div class="p-3 rounded-lg" :class="reportSummary.net_income >= 0 ? 'bg-indigo-100' : 'bg-red-200'">
                    <p class="text-sm text-gray-600">NET INCOME / (LOSS)</p>
                    <p class="text-xl font-extrabold" :class="reportSummary.net_income >= 0 ? 'text-indigo-800' : 'text-red-900'">
                        {{ formatCurrency(reportSummary.net_income) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
  </div>
</template>