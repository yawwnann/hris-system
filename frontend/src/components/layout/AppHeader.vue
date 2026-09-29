<script setup lang="ts">
import { Moon, Sun, LogOut, Menu, X } from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { useAuthStore } from "@/stores/auth";
import { useUiStore } from "@/stores/ui";
import { useDark, useToggle } from "@vueuse/core";
import { ref } from "vue";

const authStore = useAuthStore();
const uiStore = useUiStore();

const isDark = useDark();
const toggleDark = useToggle(isDark);

const isLogoutDialogOpen = ref(false);

const executeLogout = async () => {
  await authStore.logout();
};
</script>

<template>
  <header
    class="sticky top-0 z-30 flex h-16 min-h-16 items-center justify-between border-b-2 border-gray-100 bg-white px-4 transition-colors dark:border-zinc-800 dark:bg-zinc-950 sm:px-6 md:px-8"
  >
    <div class="flex min-w-0 items-center gap-3">
      <Button 
        type="button"
        @click.stop="uiStore.toggleMobileMenu()" 
        variant="ghost" 
        size="icon" 
        :aria-expanded="uiStore.mobileMenuOpen"
        aria-controls="mobile-navigation"
        aria-label="Buka menu navigasi"
        class="relative z-50 h-9 w-9 shrink-0 cursor-pointer rounded-lg text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-zinc-800 md:hidden"
      >
        <X v-if="uiStore.mobileMenuOpen" class="pointer-events-none h-5 w-5" />
        <Menu v-else class="pointer-events-none h-5 w-5" />
      </Button>
      <span class="truncate text-base font-bold text-gray-900 dark:text-zinc-100 md:hidden">HRIS System</span>
      <span class="hidden truncate text-sm font-semibold text-gray-700 dark:text-zinc-200 md:inline">
        {{ authStore.user?.role === 'admin' ? 'Dasbor Admin' : 'Portal Karyawan' }}
      </span>
    </div>

    <div class="flex shrink-0 items-center gap-1 sm:gap-2">
      <Button @click="toggleDark()" variant="ghost" size="icon" aria-label="Ubah tema" class="h-9 w-9 rounded-full text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-zinc-800">
        <Moon v-if="!isDark" class="h-5 w-5" />
        <Sun v-else class="h-5 w-5" />
      </Button>
      
      <div class="mx-1 h-6 w-px bg-gray-200 dark:bg-zinc-800"></div>
      
      <Button @click="isLogoutDialogOpen = true" variant="ghost" size="icon" aria-label="Keluar" class="h-9 w-9 rounded-full text-red-500 hover:bg-red-50 hover:text-red-600 dark:text-red-400 dark:hover:bg-red-950/30" title="Logout">
        <LogOut class="h-4 w-4" />
      </Button>
    </div>

    <AlertDialog v-model:open="isLogoutDialogOpen">
      <AlertDialogContent class="w-[calc(100%-2rem)] max-w-md border border-gray-200 bg-white dark:border-zinc-800 dark:bg-zinc-950">
        <AlertDialogHeader>
          <AlertDialogTitle class="text-gray-900 dark:text-zinc-100">Konfirmasi Logout</AlertDialogTitle>
          <AlertDialogDescription class="text-gray-500 dark:text-zinc-400">
            Apakah Anda yakin ingin keluar dari sesi ini? Anda harus login kembali untuk mengakses aplikasi.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter class="mt-6">
          <AlertDialogCancel class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800">Batal</AlertDialogCancel>
          <AlertDialogAction @click="executeLogout" class="bg-red-600 text-white hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-700 border-none">Keluar</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </header>
</template>
