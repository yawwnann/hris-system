<script setup lang="ts">
import {
  LayoutDashboard,
  Users,
  Clock,
  CalendarIcon,
  FileText,
  Settings,
  UserCheck,
  X,
} from "lucide-vue-next";
import { onUnmounted, watch } from "vue";
import { useRoute } from "vue-router";
import { ScrollArea } from "@/components/ui/scroll-area";
import { useAuthStore } from "@/stores/auth";
import { useUiStore } from "@/stores/ui";
import { Button } from "@/components/ui/button";

const authStore = useAuthStore();
const uiStore = useUiStore();
const route = useRoute();

const closeMobileMenu = () => {
  uiStore.closeMobileMenu();
};

watch(() => route.fullPath, closeMobileMenu);

watch(() => uiStore.mobileMenuOpen, (isOpen) => {
  document.body.style.overflow = isOpen ? "hidden" : "";
});

onUnmounted(() => {
  document.body.style.overflow = "";
});

const getRoute = (path: string) => {
  const prefix = authStore.user?.role === 'employee' ? '/employee' : '';
  if (path === '/') return prefix ? `${prefix}/dashboard` : '/';
  return `${prefix}${path}`;
};
</script>

<template>
  <!-- Mobile Backdrop -->
  <div 
    v-if="uiStore.mobileMenuOpen" 
    @click="closeMobileMenu" 
    class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm transition-opacity md:hidden"
    aria-hidden="true"
  ></div>

  <aside 
    id="mobile-navigation"
    :class="[
      'fixed inset-y-0 left-0 z-50 flex h-screen w-72 max-w-[calc(100vw-1rem)] flex-col border-r-2 border-gray-200 bg-white shadow-2xl transition-transform duration-300 ease-in-out dark:border-zinc-800 dark:bg-zinc-950 md:sticky md:inset-auto md:top-0 md:z-20 md:h-screen md:w-64 md:max-w-none md:shadow-none',
      uiStore.mobileMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
    ]"
    aria-label="Navigasi utama"
  >
    <div class="flex h-16 min-h-16 items-center justify-between border-b-2 border-gray-100 px-6 dark:border-zinc-800">
      <img src="@/assets/logo.png" alt="Logo" class="h-auto w-[60%] object-contain py-2 dark:invert" />
      <Button 
        type="button"
        @click="closeMobileMenu" 
        variant="ghost" 
        size="icon" 
        aria-label="Tutup menu navigasi"
        class="h-8 w-8 text-gray-500 hover:bg-gray-100 dark:hover:bg-zinc-900 md:hidden"
      >
        <X class="h-5 w-5" />
      </Button>
    </div>

    <ScrollArea class="min-h-0 flex-1 px-4 py-6 overscroll-contain">
      <div class="space-y-8" @click="closeMobileMenu">
        <!-- MENU UTAMA -->
        <div>
          <div class="text-[11px] font-bold text-gray-400 dark:text-zinc-400 uppercase tracking-widest mb-3 px-3">Menu Utama</div>
          <div class="space-y-1">
            <router-link :to="getRoute('/')" class="flex items-center px-3 py-2.5 text-sm text-gray-500 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-900 rounded-lg transition-all duration-200 group [&.router-link-active]:bg-emerald-800 dark:[&.router-link-active]:bg-emerald-700 [&.router-link-active]:text-white">
              <LayoutDashboard class="w-4 h-4 mr-3 group-hover:scale-110 transition-transform" /> Dasbor
            </router-link>
            <router-link :to="getRoute('/attendance')" class="flex items-center px-3 py-2.5 text-sm text-gray-500 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-900 rounded-lg transition-all duration-200 group [&.router-link-active]:bg-emerald-800 dark:[&.router-link-active]:bg-emerald-700 [&.router-link-active]:text-white">
              <UserCheck class="w-4 h-4 mr-3 group-hover:scale-110 transition-transform" /> Absensi
            </router-link>
          </div>
        </div>

        <!-- MANAJEMEN KARYAWAN (Admin Only) -->
        <div v-if="authStore.user?.role === 'admin'">
          <div class="text-[11px] font-bold text-gray-400 dark:text-zinc-400 uppercase tracking-widest mb-3 px-3">Manajemen</div>
          <div class="space-y-1">
            <router-link :to="getRoute('/employees')" class="flex items-center px-3 py-2.5 text-sm text-gray-500 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-900 rounded-lg transition-all duration-200 group [&.router-link-active]:bg-emerald-800 dark:[&.router-link-active]:bg-emerald-700 [&.router-link-active]:text-white">
              <Users class="w-4 h-4 mr-3 group-hover:scale-110 transition-transform" /> Karyawan
            </router-link>
            <router-link :to="getRoute('/departments')" class="flex items-center px-3 py-2.5 text-sm text-gray-500 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-900 rounded-lg transition-all duration-200 group [&.router-link-active]:bg-emerald-800 dark:[&.router-link-active]:bg-emerald-700 [&.router-link-active]:text-white">
              <FileText class="w-4 h-4 mr-3 group-hover:scale-110 transition-transform" /> Departemen
            </router-link>
          </div>
        </div>

        <!-- INFORMASI / KOMUNIKASI -->
        <div>
          <div class="text-[11px] font-bold text-gray-400 dark:text-zinc-400 uppercase tracking-widest mb-3 px-3">Informasi</div>
          <div class="space-y-1">
            <router-link :to="getRoute('/calendar')" class="flex items-center px-3 py-2.5 text-sm text-gray-500 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-900 rounded-lg transition-all duration-200 group [&.router-link-active]:bg-emerald-800 dark:[&.router-link-active]:bg-emerald-700 [&.router-link-active]:text-white">
              <CalendarIcon class="w-4 h-4 mr-3 group-hover:scale-110 transition-transform" /> Manajemen Kalender
            </router-link>
            <router-link :to="getRoute('/announcements')" class="flex items-center px-3 py-2.5 text-sm text-gray-500 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-900 rounded-lg transition-all duration-200 group [&.router-link-active]:bg-emerald-800 dark:[&.router-link-active]:bg-emerald-700 [&.router-link-active]:text-white">
              <FileText class="w-4 h-4 mr-3 group-hover:scale-110 transition-transform" /> Pengumuman
            </router-link>
            <router-link v-if="authStore.user?.role === 'admin'" :to="getRoute('/reports')" class="flex items-center px-3 py-2.5 text-sm text-gray-500 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-900 rounded-lg transition-all duration-200 group [&.router-link-active]:bg-emerald-800 dark:[&.router-link-active]:bg-emerald-700 [&.router-link-active]:text-white">
              <FileText class="w-4 h-4 mr-3 group-hover:scale-110 transition-transform" /> Laporan & Ekspor
            </router-link>
          </div>
        </div>

        <!-- PENGAJUAN -->
        <div>
          <div class="text-[11px] font-bold text-gray-400 dark:text-zinc-400 uppercase tracking-widest mb-3 px-3">Pengajuan</div>
          <div class="space-y-1">
            <router-link :to="getRoute('/leaves')" class="flex items-center px-3 py-2.5 text-sm text-gray-500 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-900 rounded-lg transition-all duration-200 group [&.router-link-active]:bg-emerald-800 dark:[&.router-link-active]:bg-emerald-700 [&.router-link-active]:text-white">
              <CalendarIcon class="w-4 h-4 mr-3 group-hover:scale-110 transition-transform" /> Cuti
            </router-link>
            <router-link :to="getRoute('/overtime')" class="flex items-center px-3 py-2.5 text-sm text-gray-500 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-900 rounded-lg transition-all duration-200 group [&.router-link-active]:bg-emerald-800 dark:[&.router-link-active]:bg-emerald-700 [&.router-link-active]:text-white">
              <Clock class="w-4 h-4 mr-3 group-hover:scale-110 transition-transform" /> Lembur
            </router-link>
          </div>
        </div>

        <!-- PENGATURAN -->
        <div v-if="authStore.user?.role === 'admin'">
          <div class="text-[11px] font-bold text-gray-400 dark:text-zinc-400 uppercase tracking-widest mb-3 px-3">Sistem</div>
          <div class="space-y-1">
            <router-link :to="getRoute('/settings')" class="flex items-center px-3 py-2.5 text-sm text-gray-500 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-900 rounded-lg transition-all duration-200 group [&.router-link-active]:bg-emerald-800 dark:[&.router-link-active]:bg-emerald-700 [&.router-link-active]:text-white">
              <Settings class="w-4 h-4 mr-3 group-hover:scale-110 transition-transform" /> Pengaturan
            </router-link>
          </div>
        </div>
      </div>
    </ScrollArea>

    <!-- USER PROFILE FOOTER -->
    <div class="p-4 border-t-2 border-gray-100 dark:border-zinc-800">
      <div class="flex items-center p-2 rounded-lg">
        <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-zinc-800 overflow-hidden flex-shrink-0 border border-gray-300 dark:border-zinc-700">
          <img :src="authStore.user?.photo ? 'http://localhost:8000/' + authStore.user.photo : `https://ui-avatars.com/api/?name=${authStore.user?.name}&background=random`" alt="Profile" class="w-full h-full object-cover" />
        </div>
        <div class="ml-3 flex-1 overflow-hidden">
          <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ authStore.user?.name || 'Administrator' }}</p>
          <p class="text-[11px] text-gray-500 dark:text-zinc-400 truncate">{{ authStore.user?.role === 'admin' ? 'Admin Super' : 'Karyawan' }}</p>
        </div>
      </div>
    </div>
  </aside>
</template>
