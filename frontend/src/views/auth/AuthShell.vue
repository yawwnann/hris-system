<script setup lang="ts">
// Simple centered shell shared by Login / Register / ForgotPassword:
// flat single-colour page background, the Lazizmu logo, one white card holding the
// page form, and a one-line copyright footer. Same layout on every breakpoint.
// The card tokens (rounded-xl, gray-200/zinc-800 border, shadow-sm) match the
// product's existing cards (e.g. admin Calendar) so the auth pages stay consistent.
</script>

<template>
  <div
    class="flex min-h-screen w-full flex-col items-center justify-center bg-gray-50 px-4 py-10 font-sans sm:px-6 dark:bg-zinc-950"
  >
    <div class="w-full max-w-md">
      <!-- Logo (the asset is an opaque white PNG, so a rounded white plate keeps it tidy on the dark page) -->
      <div class="mb-6 flex justify-center">
        <router-link
          to="/login"
          class="inline-flex rounded-xl bg-white p-1.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-500/40 focus-visible:ring-offset-2 focus-visible:ring-offset-gray-50 dark:focus-visible:ring-offset-zinc-950"
          aria-label="Lazizmu - ke halaman masuk"
        >
          <img src="@/assets/logo.png" alt="Lazizmu" class="h-16 w-auto object-contain" />
        </router-link>
      </div>

      <!-- Card -->
      <main
        class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8 dark:border-zinc-800 dark:bg-zinc-900"
      >
        <div v-if="$slots['header-action']" class="mb-6 flex items-center text-sm text-gray-500 dark:text-zinc-400">
          <slot name="header-action" />
        </div>

        <slot />
      </main>

      <footer class="mt-6 text-center text-xs text-gray-400 dark:text-zinc-500">
        &copy; 2026 Lazizmu. Hak Cipta Dilindungi.
      </footer>
    </div>
  </div>
</template>
