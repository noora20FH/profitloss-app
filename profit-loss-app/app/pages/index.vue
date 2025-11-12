<template>
  <div>

    <main class="py-10 bg-gray-50 min-h-screen">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h1 class="text-4xl font-extrabold text-gray-900 mb-8 px-4 sm:px-0">
          Selamat Datang, {{ userName }}! 👋
        </h1>

        <!-- Bagian Aksi Cepat -->
        <div class="bg-white shadow-xl rounded-xl p-8 mb-8 px-4 sm:px-0">
          <h2 class="text-2xl font-semibold text-gray-800 mb-6">Aksi Cepat ⚡</h2>
          
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <NuxtLink 
                to="/transactions/create" 
                class="flex flex-col items-center justify-center p-6 bg-indigo-500 text-white rounded-lg shadow-lg hover:bg-indigo-600 transition duration-300 transform hover:scale-105"
            >
                <svg class="h-8 w-8 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span class="text-lg font-bold">Tambah Transaksi Baru</span>
            </NuxtLink>

            <NuxtLink 
                to="/categories" 
                class="flex flex-col items-center justify-center p-6 bg-green-500 text-white rounded-lg shadow-lg hover:bg-green-600 transition duration-300 transform hover:scale-105"
            >
                <svg class="h-8 w-8 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                <span class="text-lg font-bold">Kelola Kategori COA</span>
            </NuxtLink>

            <NuxtLink 
                to="/reports/profitloss" 
                class="flex flex-col items-center justify-center p-6 bg-purple-500 text-white rounded-lg shadow-lg hover:bg-purple-600 transition duration-300 transform hover:scale-105"
            >
                <svg class="h-8 w-8 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0h6"/></svg>
                <span class="text-lg font-bold">Cek Laporan Laba Rugi</span>
            </NuxtLink>
          </div>
        </div>

        <!-- Tabel Semua Transaksi -->
        <div class="bg-white shadow-xl rounded-xl overflow-hidden px-4 sm:px-0">
            <h2 class="text-2xl font-semibold text-gray-800 p-6 border-b">Semua Transaksi (Terbaru ke Terlama)</h2>
            
            <!-- Loading State -->
            <div v-if="transactionsLoading" class="p-6 text-center text-gray-500">
                <svg class="animate-spin h-5 w-5 text-indigo-500 inline-block mr-3" viewBox="0 0 24 24"></svg>
                Memuat semua transaksi...
            </div>

            <!-- Empty State -->
            <div v-else-if="recentTransactions.length === 0" class="p-6 text-center text-gray-500">
                Tidak ada transaksi yang ditemukan.
            </div>

            <!-- Data Table -->
            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi (COA)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="tx in recentTransactions" :key="tx.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ tx.date }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ tx.description }} ({{ tx.coa_name }})</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="['px-2 inline-flex text-xs leading-5 font-semibold rounded-full', tx.type === 'Income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800']">
                                    {{ tx.type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold" :class="tx.type === 'Income' ? 'text-green-700' : 'text-red-700'">
                                {{ formatCurrency(tx.amount) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t text-right">
                 <NuxtLink to="/transactions/create" class="text-indigo-600 hover:text-indigo-800 font-medium">
                    Kelola Transaksi Lengkap &rarr;
                 </NuxtLink>
            </div>
        </div>

      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
// Asumsi AppNavbar tersedia
import AppNavbar from '@/components/AppNavbar.vue'; 
// Asumsi formatCurrency tersedia di utils/helpers
import { formatCurrency } from '@/utils/helpers'; 

// 1. Menggunakan runtimeConfig
const config = useRuntimeConfig();

const userName = ref('Admin Keuangan');
const recentTransactions = ref([]);
const transactionsLoading = ref(true);

const fetchAllTransactions = async () => {
    transactionsLoading.value = true;
    
    // 2. Mengambil URL API dari runtimeConfig
    const API_URL = `${config.public.apiBase}/transactions`; 

    try {
        console.log(`Mengambil data dari: ${API_URL}`);
        
        const response = await fetch(API_URL); 
        
        if (!response.ok) {
            // Jika status bukan 200 OK, lempar error
            throw new Error(`HTTP error! Status: ${response.status}. URL: ${API_URL}`);
        }
        
        const data = await response.json();
        recentTransactions.value = data;
        
        console.log('Data transaksi berhasil dimuat:', data);
    } catch (error) {
        console.error('Gagal mengambil semua transaksi.', error);
        // Penting: Jika ini adalah error CORS/Network, pastikan server Laravel berjalan
        // dan mengizinkan CORS dari domain Nuxt Anda (misalnya http://localhost:3000).
    } finally {
        transactionsLoading.value = false;
    }
};

onMounted(() => {
    fetchAllTransactions();
});
</script>

<style scoped>
/* Styling Tambahan */
</style>