<template>
  <nav class="bg-white shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <div class="flex-shrink-0">
          <NuxtLink to="/" class="text-2xl font-extrabold text-indigo-600 tracking-wider">
            Finance<span class="text-green-500">App</span> 📈
          </NuxtLink>
        </div>

        <div class="hidden md:flex md:space-x-8">
          <NuxtLink 
            to="/" 
            class="text-gray-900 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium transition duration-150"
          >
            Dashboard
          </NuxtLink>
          <NuxtLink 
            to="/categories" 
            class="text-gray-900 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium transition duration-150"
          >
            Kategori COA
          </NuxtLink>
          <NuxtLink 
            to="/transactions/create" 
            class="text-gray-900 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium transition duration-150"
          >
            Transaksi
          </NuxtLink>
          <NuxtLink 
            to="/reports/profitloss" 
            class="text-gray-900 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium transition duration-150"
          >
            Laporan P/L
          </NuxtLink>
        </div>

        <div class="md:hidden flex items-center">
          <button 
            @click="isOpen = !isOpen" 
            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500"
          >
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path v-if="!isOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <div v-if="isOpen" class="md:hidden">
      <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
        <NuxtLink 
          to="/" 
          class="block text-gray-900 hover:bg-indigo-50 hover:text-indigo-600 px-3 py-2 rounded-md text-base font-medium"
          @click="isOpen = false"
        >
          Dashboard
        </NuxtLink>
        <NuxtLink 
          to="/categories" 
          class="block text-gray-900 hover:bg-indigo-50 hover:text-indigo-600 px-3 py-2 rounded-md text-base font-medium"
          @click="isOpen = false"
        >
          Kategori COA
        </NuxtLink>
        <NuxtLink 
          to="/transactions" 
          class="block text-gray-900 hover:bg-indigo-50 hover:text-indigo-600 px-3 py-2 rounded-md text-base font-medium"
          @click="isOpen = false"
        >
          Transaksi
        </NuxtLink>
         <NuxtLink 
            to="/reports/profitloss" 
            class="block text-gray-900 hover:bg-indigo-50 hover:text-indigo-600 px-3 py-2 rounded-md text-base font-medium"
            @click="isOpen = false"
          >
            Laporan P/L
          </NuxtLink>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref } from 'vue';

const isOpen = ref(false); // State untuk menu mobile
</script>