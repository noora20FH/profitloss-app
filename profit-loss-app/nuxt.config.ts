// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  runtimeConfig: {
    public: {
      // Ganti dengan URL server Laravel Anda
      apiBase: 'http://localhost:8000/api', 
    }
  },
  // Pastikan array 'css' memuat file utama Anda
  css: [
    '~/assets/css/main.css', 
  ],
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  modules: ['@nuxtjs/tailwindcss']
})