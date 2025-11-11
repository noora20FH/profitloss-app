<script setup>
// 1. Import tools from Vue for reactivity
import { ref, computed } from 'vue'

// --- State Management (The Data) ---

// 'ref' makes a variable *reactive*. When 'revenue' changes, 
// any part of the template using it will automatically update.
// We start them at 0.
const revenue = ref(0)
const expenses = ref(0)


// --- Calculation Logic ---

// 'computed' automatically recalculates its value whenever 
// the reactive variables (revenue or expenses) inside it change.
const profitOrLoss = computed(() => {
  // .value is how you access/change the value inside a 'ref'
  return revenue.value - expenses.value
})


// --- Display Logic ---

// This computed property generates the final display text.
const resultText = computed(() => {
    const value = profitOrLoss.value
    if (value > 0) {
        return `Profit: $${value.toFixed(2)}`
    } else if (value < 0) {
        // Math.abs() turns a negative number into a positive one for display
        return `Loss: $${Math.abs(value).toFixed(2)}`
    } else {
        return 'Break Even: $0.00'
    }
})

// This computed property assigns a CSS class for coloring (Green for Profit, Red for Loss).
const resultClass = computed(() => {
    const value = profitOrLoss.value
    if (value > 0) return 'text-green-600 font-bold'
    if (value < 0) return 'text-red-600 font-bold'
    return 'text-gray-500' // Neutral color for break-even
})
</script>

<template>
  <div class="p-8 max-w-lg mx-auto my-12 bg-white shadow-2xl rounded-xl border border-gray-100">
    <h1 class="text-3xl font-extrabold text-center text-blue-600 mb-8">
      💰 Simple P&L Calculator
    </h1>
    
    <div class="mb-5">
      <label for="revenue" class="block text-sm font-medium text-gray-700 mb-1">
        Total Revenue ($)
      </label>
      <input
        id="revenue"
        v-model.number="revenue"
        type="number"
        min="0"
        placeholder="e.g., 15000"
        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3 border text-lg"
      >
    </div>

    <div class="mb-8">
      <label for="expenses" class="block text-sm font-medium text-gray-700 mb-1">
        Total Expenses ($)
      </label>
      <input
        id="expenses"
        v-model.number="expenses"
        type="number"
        min="0"
        placeholder="e.g., 8000"
        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3 border text-lg"
      >
    </div>

    <div class="text-center p-6 border-t-2 border-dashed border-gray-200">
      <h2 class="text-xl font-semibold text-gray-800 mb-3">P&L Result:</h2>
      <p :class="resultClass" class="text-5xl tracking-tight">
        {{ resultText }}
      </p>
    </div>
  </div>
</template>