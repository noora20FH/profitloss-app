<script setup>
import { ref, reactive, computed } from 'vue';

// Ambil konfigurasi Nuxt untuk baseURL
const config = useRuntimeConfig();
const API_BASE = config.public.apiBase;

// 1. STATE MANAGEMENT
const form = reactive({
    coa_id: null,
    date: new Date().toISOString().substring(0, 10), 
    desc: '',
    debit: 0.00,
    credit: 0.00,
});

// Ganti 'coas' menjadi hasil langsung dari useFetch
const validationErrors = ref({}); 
const isSubmitting = ref(false);
const submitStatus = ref(null); 


// 2. FETCH DATA MASTER (COA) MENGGUNAKAN DESTRUCTURING NUXT
// useFetch akan mengembalikan data yang sudah di-unwrap dari JSON response.
const { 
    data: fetchedCoas, 
    pending: coaLoading, 
    error: coaError 
} = await useFetch('/coa', {
    baseURL: API_BASE,
});

// Pastikan data yang diambil adalah array, jika tidak, set ke array kosong.
// Jika Laravel merespons dengan array, fetchedCoas.value akan berisi array tersebut.
const coas = computed(() => Array.isArray(fetchedCoas.value) ? fetchedCoas.value : []);


// 3. LOGIKA FORM
// Pastikan melakukan pengecekan coas.value sebelum .map()
const coaOptions = computed(() => {
    // Tambahkan pengecekan if (coas.value) agar .map tidak dieksekusi pada null/undefined
    if (!coas.value || coas.value.length === 0) {
        return [];
    }
    
    return coas.value.map(coa => ({
        value: coa.id,
        // Gunakan coa.category.name karena Controller Anda menggunakan with('category')
        label: `[${coa.code}] ${coa.name} (${coa.category?.name || 'N/A'})`
    }));
});

// Function untuk memastikan hanya salah satu (Debit atau Credit) yang terisi
const handleAmountChange = (fieldChanged) => {
    if (fieldChanged === 'debit' && form.debit > 0) {
        form.credit = 0.00;
    }
    if (fieldChanged === 'credit' && form.credit > 0) {
        form.debit = 0.00;
    }
};

// Function utama untuk mengirim data ke Laravel API
const submitTransaction = async () => {
    // ... (Logika validasi dan submit tetap sama) ...
    if (!form.coa_id || (form.debit <= 0 && form.credit <= 0)) {
        alert('Mohon lengkapi COA dan nilai transaksi.');
        return;
    }

    isSubmitting.value = true;
    validationErrors.value = {}; 
    submitStatus.value = null;

    try {
        await $fetch('/transactions', {
            method: 'POST',
            baseURL: API_BASE,
            body: form,
        });

        // Sukses
        submitStatus.value = 'success';
        Object.assign(form, {
            coa_id: null,
            date: new Date().toISOString().substring(0, 10),
            desc: '',
            debit: 0.00,
            credit: 0.00,
        });

    } catch (error) {
        submitStatus.value = 'error';
        if (error.response && error.response.status === 422) {
            validationErrors.value = error.response._data.errors || {};
        } else {
            console.error('API Error:', error);
            validationErrors.value.general = ['Terjadi kesalahan koneksi atau server internal.'];
        }
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<template>
  <div class="p-8 max-w-xl mx-auto my-10 bg-white shadow-2xl rounded-lg">
    <h1 class="text-3xl font-bold text-center text-green-700 mb-8">
      📝 Input Transaksi Baru
    </h1>

    <div v-if="coaLoading" class="p-3 bg-indigo-100 text-indigo-700 rounded text-center">
      Memuat Master COA...
    </div>
    
    <div v-else-if="coaError" class="p-3 bg-red-100 text-red-700 rounded">
      Gagal memuat COA. Cek koneksi API: {{ coaError.message }}
    </div>

    <div v-if="submitStatus === 'success'" class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg font-semibold">
      Transaksi berhasil dicatat!
    </div>
    <div v-if="submitStatus === 'error' && !Object.keys(validationErrors).length" class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg font-semibold">
        Terjadi kesalahan saat memproses data. Coba lagi.
    </div>
    <div v-if="validationErrors.general" class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
        {{ validationErrors.general[0] }}
    </div>


    <form @submit.prevent="submitTransaction" v-if="!coaLoading && !coaError">
        
        <div class="mb-5">
            <label for="coa_id" class="block text-sm font-medium text-gray-700">Akun COA</label>
            <select
                id="coa_id"
                v-model="form.coa_id"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 p-3"
            >
                <option :value="null" disabled>-- Pilih Akun --</option>
                <option v-for="option in coaOptions" :key="option.value" :value="option.value">
                    {{ option.label }}
                </option>
            </select>
            <p v-if="validationErrors.coa_id" class="mt-1 text-sm text-red-600">{{ validationErrors.coa_id[0] }}</p>
        </div>
        
        <div class="mb-5">
            <label for="date" class="block text-sm font-medium text-gray-700">Tanggal Transaksi</label>
            <input
                id="date"
                v-model="form.date"
                type="date"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 p-3"
            >
             <p v-if="validationErrors.date" class="mt-1 text-sm text-red-600">{{ validationErrors.date[0] }}</p>
        </div>

        <div class="mb-5">
            <label for="desc" class="block text-sm font-medium text-gray-700">Deskripsi</label>
            <input
                id="desc"
                v-model="form.desc"
                type="text"
                placeholder="Detail transaksi"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 p-3"
            >
             <p v-if="validationErrors.desc" class="mt-1 text-sm text-red-600">{{ validationErrors.desc[0] }}</p>
        </div>

        <div class="flex space-x-4 mb-6">
            <div class="flex-1">
                <label for="debit" class="block text-sm font-medium text-gray-700">Debit</label>
                <input
                    id="debit"
                    v-model.number="form.debit"
                    @input="handleAmountChange('debit')"
                    type="number"
                    min="0"
                    step="0.01"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 p-3 bg-red-50"
                >
                <p v-if="validationErrors.debit" class="mt-1 text-sm text-red-600">{{ validationErrors.debit[0] }}</p>
            </div>
            
            <div class="flex-1">
                <label for="credit" class="block text-sm font-medium text-gray-700">Credit</label>
                <input
                    id="credit"
                    v-model.number="form.credit"
                    @input="handleAmountChange('credit')"
                    type="number"
                    min="0"
                    step="0.01"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 p-3 bg-green-50"
                >
                <p v-if="validationErrors.credit" class="mt-1 text-sm text-red-600">{{ validationErrors.credit[0] }}</p>
            </div>
        </div>

        <button
            type="submit"
            :disabled="isSubmitting || coaLoading || coaError"
            class="w-full py-3 px-4 border border-transparent rounded-md shadow-sm text-lg font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150 disabled:opacity-50"
        >
            {{ isSubmitting ? 'Menyimpan...' : 'Catat Transaksi' }}
        </button>
    </form>
  </div>
</template>