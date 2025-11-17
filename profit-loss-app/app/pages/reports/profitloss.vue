<script setup>
import { ref, reactive, computed, onMounted } from "vue"; // Pastikan onMounted diimpor

const config = useRuntimeConfig();
const API_BASE = config.public.apiBase;

// --- STATE ---
const reportDates = reactive({
    // Set default ke awal dan akhir bulan lalu
    start_date: new Date(new Date().getFullYear(), new Date().getMonth() - 1, 1)
        .toISOString()
        .substring(0, 10),
    end_date: new Date(new Date().getFullYear(), new Date().getMonth(), 0)
        .toISOString()
        .substring(0, 10),
});

const reportData = ref(null); // Data detail per kategori/bulan, dikelompokkan: { Income: {...}, Expense: {...} }
const reportSummary = ref(null); // Data Total Income, Expense, Net Income
const isLoading = ref(false);
const fetchError = ref(null);

// --- FETCHING LOGIC ---

const fetchReport = async () => {
    if (!reportDates.start_date || !reportDates.end_date) {
        alert("Mohon lengkapi Periode Mulai dan Akhir.");
        return;
    }

    isLoading.value = true;
    fetchError.value = null;
    reportData.value = null;
    reportSummary.value = null;

    try {
        const response = await $fetch("/reports/profitloss", {
            method: "GET",
            baseURL: API_BASE,
            params: reportDates,
        });

        // Pastikan response.data memiliki keys 'Income' dan 'Expense'
        reportData.value = response.data;
        reportSummary.value = response.summary;
    } catch (err) {
        console.error("Laporan API Error:", err);
        fetchError.value =
            "Gagal memuat laporan. Pastikan API berjalan dan rentang tanggal valid.";
    } finally {
        isLoading.value = false;
    }
};

const exportReport = () => {
    if (!reportDates.start_date || !reportDates.end_date) {
        alert("Mohon lengkapi Periode Mulai dan Akhir sebelum export.");
        return;
    }

    const url = `${API_BASE}/reports/profitloss/export?start_date=${reportDates.start_date}&end_date=${reportDates.end_date}`;
    window.location.href = url;
};

// Panggil laporan saat halaman pertama dimuat
onMounted(() => {
    fetchReport();
});

// --- COMPUTED PROPERTIES & HELPERS ---

// Menghasilkan daftar unik bulan (kolom dinamis)
const dynamicMonths = computed(() => {
    if (!reportData.value || Object.keys(reportData.value).length === 0) return [];

    const monthSet = new Set();
    
    // Looping melalui grup Income dan Expense untuk mendapatkan semua bulan
    for (const typeKey in reportData.value) {
        for (const catId in reportData.value[typeKey]) {
            for (const monthKey in reportData.value[typeKey][catId].data_by_month) {
                // 🎯 KUNCI KRITIS: Filter kunci yang tidak valid (0-00)
                if (monthKey !== '0-00' && monthKey !== '0-0' && monthKey !== '0-1') {
                    monthSet.add(monthKey);
                }
            }
        }
    }
    return Array.from(monthSet).sort(); 
});

/**
 * Mengambil nilai (Income Value atau Expense Value) kategori tertentu pada bulan tertentu.
 * @param {string} type - 'Income' atau 'Expense'
 */

const getCategoryValue = (type, categoryId, monthYear) => {
    if (!reportData.value || !reportData.value[type] || !reportData.value[type][categoryId]) {
        return 0;
    }
    const value = reportData.value[type][categoryId].data_by_month[monthYear];
    
    // 🎯 PASTIKAN: Selalu kembalikan Number.
    return Number(value) || 0; 
};

/**
 * Hitung Total Pemasukan/Pengeluaran untuk kolom 'Total Periode' per baris kategori.
 */
// pages/reports/profitloss.vue
const calculateRowTotal = (category) => {
    let total = 0;
    for (const month in category.data_by_month) {
        // 🎯 PERBAIKAN: Gunakan Number() pada nilai sebelum penjumlahan
        total += Number(category.data_by_month[month]) || 0; 
    }
    return total;
};

// Helper untuk format mata uang
const formatCurrency = (value) => {
    if (value === 0 || value === undefined || value === null) return "0";
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 2,
    }).format(value);
};

// Helper untuk format nama bulan (Misal: 2024-01 -> Januari 2024)
const formatMonthName = (monthYear) => {
    const [year, month] = monthYear.split("-");
    const date = new Date(year, month - 1, 1);
    return new Intl.DateTimeFormat("id-ID", {
        month: "long",
        year: "numeric",
    }).format(date);
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
                <input id="start_date" v-model="reportDates.start_date" type="date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label for="end_date" class="block text-sm font-medium text-gray-700">Periode Akhir</label>
                <input id="end_date" v-model="reportDates.end_date" type="date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2">
            </div>
            <button type="submit" :disabled="isLoading" class="px-6 py-2 bg-indigo-600 text-white font-medium rounded-lg shadow-md hover:bg-indigo-700 transition duration-150 disabled:opacity-50 h-[42px]">
                {{ isLoading ? 'Memuat...' : 'Tampilkan Laporan' }}
            </button>
            <button type="button" @click="exportReport" class="px-6 py-2 bg-green-600 text-white font-medium rounded-lg shadow-md hover:bg-green-700 transition duration-150 h-[42px]" :disabled="isLoading">
                Export Excel
            </button>
        </form>
    </div>
    
    <div v-if="fetchError" class="p-4 bg-red-100 text-red-700 rounded-lg">{{ fetchError }}</div>
    <div v-else-if="isLoading && !reportData" class="p-4 text-center text-gray-500">Memuat data laporan...</div>
    <div v-else-if="reportData && (Object.keys(reportData.Income || {}).length === 0 && Object.keys(reportData.Expense || {}).length === 0)" class="p-4 bg-yellow-100 text-yellow-700 rounded-lg">Tidak ada data transaksi Income atau Expense dalam rentang tanggal ini.</div>
    
    <div v-else-if="reportData && Object.keys(reportData).length > 0" class="bg-white shadow-xl rounded-xl overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 sticky top-0">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50 z-10 w-40">Kategori COA</th>
                    
                    <th 
                        v-for="month in dynamicMonths" 
                        :key="month" 
                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-l border-gray-200"
                    >
                        {{ formatMonthName(month) }}
                    </th>
                    
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-32 border-l border-gray-200">Total Periode</th>
                </tr>
            </thead>
            
            <tbody class="divide-y divide-gray-200">

                <tr class="bg-green-50">
                    <td colspan="100%" class="px-6 py-2 text-left text-sm font-bold text-green-800 sticky left-0 z-10">
                        PENDAPATAN (INCOME)
                    </td>
                </tr>
                <template v-if="reportData.Income">
                    <tr v-for="cat in Object.values(reportData.Income)" :key="cat.id" class="hover:bg-gray-50">
                        <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-900 sticky left-0 bg-white z-10">
                            {{ cat.name }}
                        </td>
                        <td 
                            v-for="month in dynamicMonths" 
                            :key="`${cat.id}-${month}`" 
                            class="px-6 py-3 whitespace-nowrap text-sm text-right border-l border-gray-100"
                        >
                            {{ formatCurrency(getCategoryValue('Income', cat.id, month)) }}
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap text-sm font-semibold text-right border-l border-gray-200 bg-gray-50">
                            {{ formatCurrency(calculateRowTotal(cat)) }}
                        </td>
                    </tr>
                </template>

                <tr class="bg-green-100 font-bold border-t border-green-200">
                    <td class="px-6 py-3 text-left sticky left-0 bg-green-100 z-10">TOTAL INCOME</td>
                    <td 
                        v-for="month in dynamicMonths" 
                        :key="`total-income-${month}`" 
                        class="px-6 py-3 text-right border-l border-green-200"
                    >
                        {{ formatCurrency(
                            Object.values(reportData.Income || {}).reduce((sum, cat) => sum + getCategoryValue('Income', cat.id, month), 0)
                        ) }}
                    </td>
                    <td class="px-6 py-3 text-right border-l border-green-200">
                        {{ reportSummary ? formatCurrency(reportSummary.total_income) : 'N/A' }}
                    </td>
                </tr>

                <tr class="bg-red-50">
                    <td colspan="100%" class="px-6 py-2 text-left text-sm font-bold text-red-800 sticky left-0 z-10">
                        BEBAN (EXPENSE)
                    </td>
                </tr>
                 <template v-if="reportData.Expense">
                    <tr v-for="cat in Object.values(reportData.Expense)" :key="cat.id" class="hover:bg-gray-50">
                        <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-900 sticky left-0 bg-white z-10">
                            {{ cat.name }}
                        </td>
                        <td 
                            v-for="month in dynamicMonths" 
                            :key="`${cat.id}-${month}`" 
                            class="px-6 py-3 whitespace-nowrap text-sm text-right border-l border-gray-100"
                        >
                            {{ formatCurrency(getCategoryValue('Expense', cat.id, month)) }}
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap text-sm font-semibold text-right border-l border-gray-200 bg-gray-50">
                            {{ formatCurrency(calculateRowTotal(cat)) }}
                        </td>
                    </tr>
                </template>
                
                <tr class="bg-red-100 font-bold border-t border-red-200">
                    <td class="px-6 py-3 text-left sticky left-0 bg-red-100 z-10">TOTAL EXPENSE</td>
                    <td 
                        v-for="month in dynamicMonths" 
                        :key="`total-expense-${month}`" 
                        class="px-6 py-3 text-right border-l border-red-200"
                    >
                        {{ formatCurrency(
                            Object.values(reportData.Expense || {}).reduce((sum, cat) => sum + getCategoryValue('Expense', cat.id, month), 0)
                        ) }}
                    </td>
                    <td class="px-6 py-3 text-right border-l border-red-200">
                        {{ reportSummary ? formatCurrency(reportSummary.total_expense) : 'N/A' }}
                    </td>
                </tr>

                <tr class="bg-indigo-200 font-extrabold border-t-2 border-indigo-500">
                    <td class="px-6 py-3 text-left sticky left-0 bg-indigo-200 z-10">NET INCOME / (LOSS)</td>
                    <td 
                        v-for="month in dynamicMonths" 
                        :key="`net-income-${month}`" 
                        class="px-6 py-3 text-right border-l border-indigo-500"
                    >
                        {{ formatCurrency(
                            (Object.values(reportData.Income || {}).reduce((sum, cat) => sum + getCategoryValue('Income', cat.id, month), 0))
                            - 
                            (Object.values(reportData.Expense || {}).reduce((sum, cat) => sum + getCategoryValue('Expense', cat.id, month), 0))
                        ) }}
                    </td>
                    <td class="px-6 py-3 text-right border-l border-indigo-500">
                        {{ reportSummary ? formatCurrency(reportSummary.net_income) : 'N/A' }}
                    </td>
                </tr>
            </tbody>
        </table>
        
        </div>
</div>
</template>