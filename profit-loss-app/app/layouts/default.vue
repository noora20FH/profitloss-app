<template>
  <div>
    <AppNavbar />
    <slot />
  </div>
</template>

<script setup>
import AppNavbar from '~/components/AppNavbar.vue';
</script>