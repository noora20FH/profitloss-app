<script setup>
import { ref, reactive, computed } from "vue";

const config = useRuntimeConfig();
const API_BASE = config.public.apiBase;

// --- STATE ---
const showModal = ref(false);
const isEditing = ref(false);
const selectedItem = ref(null);
const form = reactive({ id: null, category_id: null, code: "", name: "" });
const validationErrors = ref({});
const isSubmitting = ref(false);

// --- FETCH DATA MASTER COA (READ) ---
const {
  data: coas,
  pending: coaPending,
  error: coaError,
  refresh: refreshCoas,
} = await useFetch("/coa", {
  baseURL: API_BASE,
  default: () => [],
});

// --- FETCH DATA SUPPORT (CATEGORIES) ---
const {
  data: categories,
  pending: catPending,
  error: catError,
} = await useFetch("/categories", {
  baseURL: API_BASE,
  default: () => [],
});

// Computed options untuk dropdown Kategori
const categoryOptions = computed(() => {
  return Array.isArray(categories.value)
    ? categories.value.map((cat) => ({
        value: cat.id,
        label: cat.name,
      }))
    : [];
});

// --- MODAL ACTIONS ---
const openCreateModal = () => {
  isEditing.value = false;
  form.id = null;
  form.category_id = null;
  form.code = "";
  form.name = "";
  validationErrors.value = {};
  showModal.value = true;
};

const openEditModal = (item) => {
  isEditing.value = true;
  selectedItem.value = item;
  form.id = item.id;
  form.category_id = item.category_id;
  form.code = item.code;
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

  const url = isEditing.value ? `/coa/${form.id}` : "/coa";
  const method = isEditing.value ? "PUT" : "POST";

  try {
    await $fetch(url, {
      method: method,
      baseURL: API_BASE,
      body: {
        category_id: form.category_id,
        code: form.code,
        name: form.name,
      },
    });

    closeModal();
    await refreshCoas(); // Muat ulang data COA
    alert(`COA berhasil di${isEditing.value ? "perbarui" : "buat"}!`);
  } catch (err) {
    if (err.response && err.response.status === 422) {
      validationErrors.value = err.response._data.errors || {};
    } else {
      alert(`Gagal menyimpan data. Error: ${err.message}`);
    }
  } finally {
    isSubmitting.value = false;
  }
};
// --- DELETE ACTION ---
const handleDelete = async (item) => {
  if (
    !confirm(
      `Anda yakin ingin menghapus Akun COA: [${item.code}] ${item.name}?`
    )
  ) {
    return;
  }

  try {
    await $fetch(`/coa/${item.id}`, {
      method: "DELETE",
      baseURL: API_BASE,
    });

    await refreshCoas();
    alert("Akun COA berhasil dihapus!");
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
  <div class="p-8 max-w-5xl mx-auto my-10">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-green-700">
        Master Chart of Accounts (COA)
      </h1>
      <button
        @click="openCreateModal"
        :disabled="catPending || catError"
        class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-150 disabled:opacity-50"
      >
        + Tambah COA
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="coaPending || catPending" class="p-4 text-center text-gray-500">
      Memuat data...
    </div>
    <!-- Error State -->
    <div
      v-else-if="coaError || catError"
      class="p-4 bg-red-100 text-red-700 rounded-lg"
    >
      Gagal memuat data: {{ coaError?.message || catError?.message }}
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
              Kode
            </th>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Nama Akun
            </th>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Kategori
            </th>
            <th
              class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Aksi
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="item in coas" :key="item.id" class="hover:bg-gray-50">
            <td
              class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
            >
              {{ item.id }}
            </td>
            <td
              class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-semibold"
            >
              {{ item.code }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
              {{ item.name }}
            </td>
            <!-- Tampilkan nama Kategori dari relasi -->
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              <span
                :class="{ 'bg-blue-100 text-blue-800': item.category }"
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
              >
                {{ item.category?.name || "N/A" }}
              </span>
            </td>
            <td
              class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium"
            >
              <button
                @click="openEditModal(item)"
                class="text-green-600 hover:text-green-900 transition duration-150 p-1"
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
            {{ isEditing ? "Edit COA" : "Buat COA Baru" }}
          </h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
            &times;
          </button>
        </div>

        <form @submit.prevent="handleSubmit">
          <!-- Input Kategori ID -->
          <div class="mb-4">
            <label
              for="category_id"
              class="block text-sm font-medium text-gray-700"
              >Kategori COA</label
            >
            <select
              id="category_id"
              v-model="form.category_id"
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-green-500 focus:border-green-500"
              required
            >
              <option :value="null" disabled>-- Pilih Kategori --</option>
              <option
                v-for="option in categoryOptions"
                :key="option.value"
                :value="option.value"
              >
                {{ option.label }}
              </option>
            </select>
            <p
              v-if="validationErrors.category_id"
              class="mt-1 text-sm text-red-600"
            >
              {{ validationErrors.category_id[0] }}
            </p>
          </div>

          <!-- Input Kode -->
          <div class="mb-4">
            <label for="code" class="block text-sm font-medium text-gray-700"
              >Kode Akun (e.g., 401)</label
            >
            <input
              id="code"
              v-model="form.code"
              type="text"
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-green-500 focus:border-green-500"
              required
            />
            <p v-if="validationErrors.code" class="mt-1 text-sm text-red-600">
              {{ validationErrors.code[0] }}
            </p>
          </div>

          <!-- Input Nama -->
          <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700"
              >Nama Akun (e.g., Gaji Karyawan)</label
            >
            <input
              id="name"
              v-model="form.name"
              type="text"
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-green-500 focus:border-green-500"
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
                  ? 'bg-green-400'
                  : 'bg-green-600 hover:bg-green-700'
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
