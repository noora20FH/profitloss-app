<script setup>
import { ref, reactive, computed } from "vue";

const config = useRuntimeConfig();
const API_BASE = config.public.apiBase;

// --- STATE ---
const showModal = ref(false);
const isEditing = ref(false);
const selectedItem = ref(null); // Item yang sedang di-edit
const form = reactive({ id: null, name: "" });
const validationErrors = ref({});
const isSubmitting = ref(false);

// --- FETCH DATA (READ) ---
const {
  data: categories,
  pending,
  error,
  refresh: refreshData,
} = await useFetch("/categories", {
  baseURL: API_BASE,
  // Pastikan categories selalu array
  default: () => [],
});

// --- MODAL ACTIONS ---
const openCreateModal = () => {
  isEditing.value = false;
  form.id = null;
  form.name = "";
  validationErrors.value = {};
  showModal.value = true;
};

const openEditModal = (item) => {
  isEditing.value = true;
  selectedItem.value = item;
  form.id = item.id;
  form.name = item.name;
  validationErrors.value = {};
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};
// --- SUBMIT (CREATE & UPDATE) ---
const handleSubmit = async () => {
  isSubmitting.value = true;
  validationErrors.value = {};

  const url = isEditing.value ? `/categories/${form.id}` : "/categories";
  const method = isEditing.value ? "PUT" : "POST";

  try {
    await $fetch(url, {
      method: method,
      baseURL: API_BASE,
      body: { name: form.name },
    });

    // SUCCESS:
    closeModal();
    await refreshData();
    // Anda bisa menggunakan toast/notifikasi yang lebih baik, tapi alert untuk saat ini
    alert(`Kategori berhasil di${isEditing.value ? "perbarui" : "buat"}!`);
  } catch (err) {
    // ERROR:
    console.error("API Submission Error:", err); // Log error di konsol browser

    if (err.response && err.response.status === 422) {
      // Error Validasi Laravel
      validationErrors.value = err.response._data.errors || {};
      alert(`Gagal menyimpan: ${err.response._data.message}. Cek input Anda.`);
    } else {
      // Error Umum (Koneksi, 500 Internal Server Error, dll.)
      alert(
        `Gagal terhubung ke server atau terjadi kesalahan internal: ${err.message}`
      );
    }
  } finally {
    // Block FINALLY memastikan isSubmitting selalu menjadi false, baik berhasil maupun gagal.
    isSubmitting.value = false;
  }
};
// --- DELETE ACTION ---
const handleDelete = async (item) => {
  if (
    !confirm(
      `Anda yakin ingin menghapus Kategori: ${item.name} (ID: ${item.id})?`
    )
  ) {
    return;
  }

  try {
    await $fetch(`/categories/${item.id}`, {
      method: "DELETE",
      baseURL: API_BASE,
    });

    await refreshData();
    alert("Kategori berhasil dihapus!");
  } catch (err) {
    // Tangkap error 409 (Conflict) dari Laravel jika ada relasi
    const errorMessage =
      err.response?._data?.message ||
      "Gagal menghapus. Periksa konsol untuk detail.";
    alert(errorMessage);
    console.error("Delete Error:", err);
  }
};
// Fungsi utility untuk format tanggal
const formatDate = (dateString) => {
  const options = { year: "numeric", month: "short", day: "numeric" };
  return new Date(dateString).toLocaleDateString("id-ID", options);
};
</script>

<template>
  <div class="p-8 max-w-4xl mx-auto my-10">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-indigo-700">Master Kategori COA</h1>
      <button
        @click="openCreateModal"
        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-150"
      >
        + Tambah Kategori
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="pending" class="p-4 text-center text-gray-500">
      Memuat data...
    </div>
    <!-- Error State -->
    <div v-else-if="error" class="p-4 bg-red-100 text-red-700 rounded-lg">
      Gagal memuat data: {{ error.message }}
    </div>

    <!-- Tabel Data (READ) -->
    <div v-else class="bg-white shadow-xl rounded-xl overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              ID
            </th>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Nama Kategori
            </th>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Dibuat
            </th>
            <th
              class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Aksi
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr
            v-for="item in categories"
            :key="item.id"
            class="hover:bg-gray-50"
          >
            <td
              class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
            >
              {{ item.id }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
              {{ item.name }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ formatDate(item.created_at) }}
            </td>
            <td
              class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium"
            >
              <button
                @click="openEditModal(item)"
                class="text-indigo-600 hover:text-indigo-900 transition duration-150 p-1"
              >
                Edit
              </button>
              <button
                @click="handleDelete(item)"
                class="text-red-600 hover:text-red-900 transition duration-150 p-1"
              >
                Hapus
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- MODAL (CREATE & EDIT) -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center z-50 transition-opacity"
    >
      <div
        class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6 transform transition-all duration-300 scale-100"
      >
        <div class="flex justify-between items-start border-b pb-3 mb-4">
          <h3 class="text-xl font-semibold text-gray-800">
            {{ isEditing ? "Edit Kategori" : "Buat Kategori Baru" }}
          </h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
            &times;
          </button>
        </div>

        <form @submit.prevent="handleSubmit">
          <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700"
              >Nama Kategori</label
            >
            <input
              id="name"
              v-model="form.name"
              type="text"
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500"
              required
            />
            <p v-if="validationErrors.name" class="mt-1 text-sm text-red-600">
              {{ validationErrors.name[0] }}
            </p>
          </div>

          <div class="flex justify-end space-x-3">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="px-4 py-2 text-white rounded-lg transition"
              :class="
                isSubmitting
                  ? 'bg-indigo-400'
                  : 'bg-indigo-600 hover:bg-indigo-700'
              "
            >
              {{
                isSubmitting
                  ? "Menyimpan..."
                  : isEditing
                  ? "Perbarui"
                  : "Simpan"
              }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
