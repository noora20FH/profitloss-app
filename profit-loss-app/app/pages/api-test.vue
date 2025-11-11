<script setup>
// Tentukan URL dasar API Laravel Anda
// Ganti 8000 dengan port tempat server Laravel Anda berjalan (jika berbeda)
// Ambil konfigurasi Nuxt untuk baseURL
const config = useRuntimeConfig();
const LARAVEL_BASE_URL = config.public.apiBase;
// useFetch adalah fitur Nuxt yang menangani pengambilan data, loading, dan error state secara otomatis.
const { 
  data: categories, // data yang berhasil diambil akan disimpan di sini
  pending,         // boolean, true saat proses fetching berlangsung
  error,           // objek error jika terjadi kegagalan
  refresh          // fungsi untuk mengambil data ulang
} = await useFetch('/categories', {
  // Tentukan URL lengkap API
  baseURL: LARAVEL_BASE_URL,
});
</script>

<template>
  <div class="p-8 max-w-2xl mx-auto">
    <h1 class="text-3xl font-bold mb-6 text-indigo-600">
      ✅ Uji Koneksi API Laravel
    </h1>

    <div class="space-y-4">
      <div v-if="pending" class="bg-blue-100 p-4 rounded-lg text-blue-700 font-semibold">
        📡 Menghubungi API Laravel...
      </div>

      <div v-else-if="error" class="bg-red-100 p-4 rounded-lg text-red-700">
        ❌ **Koneksi Gagal!** Pastikan server Laravel Anda berjalan dan konfigurasi CORS sudah benar. <br>
        Detail Error: 
        <pre class="mt-2 text-sm">{{ error }}</pre>
      </div>

      <div v-else-if="categories && categories.length > 0" class="bg-green-100 p-4 rounded-lg">
        🎉 **Koneksi Sukses!** API Laravel merespons dengan data Master Kategori COA.
        
        <h2 class="text-xl font-semibold mt-4 mb-2 text-green-700">Daftar Kategori:</h2>
        <ul class="list-disc pl-5">
            <li v-for="category in categories" :key="category.id" class="text-gray-800">
                ID: **{{ category.id }}** | Nama: **{{ category.name }}**
            </li>
        </ul>
      </div>
      
      <div v-else class="bg-yellow-100 p-4 rounded-lg text-yellow-700 font-semibold">
        ⚠️ Koneksi Sukses, tetapi daftar Kategori kosong.
      </div>
    </div>
    
    <button 
      @click="refresh" 
      class="mt-6 bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded transition duration-150"
    >
      Muat Ulang Data
    </button>

  </div>
</template>

<style scoped>
/* Anda bisa menambahkan styling sederhana di sini jika tidak menggunakan Tailwind CSS */
</style>