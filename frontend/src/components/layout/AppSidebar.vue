<script setup lang="ts">
import {
  ChevronDown,
  ChevronRight,
  LayoutDashboard,
  Users,
  Clock,
  CalendarIcon,
  FileText,
  Settings,
  UserCheck,
} from "lucide-vue-next";
import { ScrollArea } from "@/components/ui/scroll-area";
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();

const getRoute = (path: string) => {
  if (import.meta.env.VITE_INTEGRATION_MODE === 'false') {
    const role = authStore.user?.role === 'admin' ? '/admin' : '/employee';
    if (path === '/') return `${role}/dashboard`;
    return `${role}${path}`;
  }
  return path;
};

</script>

<template>
  <aside class="w-64 bg-white dark:bg-zinc-950 border-r-2 border-gray-200 dark:border-zinc-800 flex flex-col hidden md:flex sticky top-0 h-screen transition-colors">
    <div class="px-6  flex items-center justify-center border-b-2 border-gray-100 dark:border-zinc-800">
      <img src="@/assets/logo.png" alt="Logo" class="w-[70%] h-auto py-3 object-contain dark:invert" />
    </div>

    <ScrollArea class="flex-1 px-4 py-6">
      <div class="space-y-8">
        <!-- MENU UTAMA -->
        <div>
          <div class="text-[11px] font-bold text-gray-400 dark:text-gray-500 dark:text-zinc-400 uppercase tracking-widest mb-3 px-3">Menu Utama</div>
          <div class="space-y-1">
            <router-link :to="getRoute('/')"  class="flex items-center px-3 py-2.5 text-sm text-gray-500 dark:text-gray-400 dark:text-zinc-500 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-900 rounded-lg transition-all duration-200 group [&.router-link-active]:bg-emerald-800 dark:[&.router-link-active]:bg-emerald-700 [&.router-link-active]:text-white dark:[&.router-link-active]:text-zinc-100 [&.router-link-active]:hover:bg-emerald-900 dark:[&.router-link-active]:hover:bg-emerald-600 [&.router-link-active]:hover:text-white">
              <LayoutDashboard class="w-4 h-4 mr-3 group-hover:scale-110 transition-transform" /> Dasbor
            </router-link>
            <router-link :to="getRoute('/attendance')"  class="flex items-center px-3 py-2.5 text-sm text-gray-500 dark:text-gray-400 dark:text-zinc-500 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-900 rounded-lg transition-all duration-200 group [&.router-link-active]:bg-emerald-800 dark:[&.router-link-active]:bg-emerald-700 [&.router-link-active]:text-white dark:[&.router-link-active]:text-zinc-100 [&.router-link-active]:hover:bg-emerald-900 dark:[&.router-link-active]:hover:bg-emerald-600 [&.router-link-active]:hover:text-white">
              <UserCheck class="w-4 h-4 mr-3 group-hover:scale-110 transition-transform" /> Absensi
            </router-link>
          </div>
        </div>

        <!-- MANAJEMEN KARYAWAN (Admin Only) -->
        <div v-if="authStore.user?.role === 'admin'">
          <div class="text-[11px] font-bold text-gray-400 dark:text-gray-500 dark:text-zinc-400 uppercase tracking-widest mb-3 px-3">Manajemen</div>
          <div class="space-y-1">
            <router-link :to="getRoute('/employees')"  class="flex items-center px-3 py-2.5 text-sm text-gray-500 dark:text-gray-400 dark:text-zinc-500 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-900 rounded-lg transition-all duration-200 group [&.router-link-active]:bg-emerald-800 dark:[&.router-link-active]:bg-emerald-700 [&.router-link-active]:text-white dark:[&.router-link-active]:text-zinc-100 [&.router-link-active]:hover:bg-emerald-900 dark:[&.router-link-active]:hover:bg-emerald-600 [&.router-link-active]:hover:text-white">
              <Users class="w-4 h-4 mr-3 group-hover:scale-110 transition-transform" /> Karyawan
            </router-link>
            <router-link :to="getRoute('/departments')"  class="flex items-center px-3 py-2.5 text-sm text-gray-500 dark:text-gray-400 dark:text-zinc-500 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-900 rounded-lg transition-all duration-200 group [&.router-link-active]:bg-emerald-800 dark:[&.router-link-active]:bg-emerald-700 [&.router-link-active]:text-white dark:[&.router-link-active]:text-zinc-100 [&.router-link-active]:hover:bg-emerald-900 dark:[&.router-link-active]:hover:bg-emerald-600 [&.router-link-active]:hover:text-white">
              <FileText class="w-4 h-4 mr-3 group-hover:scale-110 transition-transform" /> Departemen
            </router-link>
          </div>
        </div>

        <!-- INFORMASI / KOMUNIKASI -->
        <div>
          <div class="text-[11px] font-bold text-gray-400 dark:text-gray-500 dark:text-zinc-400 uppercase tracking-widest mb-3 px-3">Informasi</div>
          <div class="space-y-1">
            <router-link :to="getRoute('/calendar')"  class="flex items-center px-3 py-2.5 text-sm text-gray-500 dark:text-gray-400 dark:text-zinc-500 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-900 rounded-lg transition-all duration-200 group [&.router-link-active]:bg-emerald-800 dark:[&.router-link-active]:bg-emerald-700 [&.router-link-active]:text-white dark:[&.router-link-active]:text-zinc-100 [&.router-link-active]:hover:bg-emerald-900 dark:[&.router-link-active]:hover:bg-emerald-600 [&.router-link-active]:hover:text-white">
              <CalendarIcon class="w-4 h-4 mr-3 group-hover:scale-110 transition-transform" /> Manajemen Kalender
            </router-link>
            <router-link :to="getRoute('/announcements')"  class="flex items-center px-3 py-2.5 text-sm text-gray-500 dark:text-gray-400 dark:text-zinc-500 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-900 rounded-lg transition-all duration-200 group [&.router-link-active]:bg-emerald-800 dark:[&.router-link-active]:bg-emerald-700 [&.router-link-active]:text-white dark:[&.router-link-active]:text-zinc-100 [&.router-link-active]:hover:bg-emerald-900 dark:[&.router-link-active]:hover:bg-emerald-600 [&.router-link-active]:hover:text-white">
              <FileText class="w-4 h-4 mr-3 group-hover:scale-110 transition-transform" /> Pengumuman
            </router-link>
            <router-link v-if="authStore.user?.role === 'admin'" :to="getRoute('/reports')"  class="flex items-center px-3 py-2.5 text-sm text-gray-500 dark:text-gray-400 dark:text-zinc-500 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-900 rounded-lg transition-all duration-200 group [&.router-link-active]:bg-emerald-800 dark:[&.router-link-active]:bg-emerald-700 [&.router-link-active]:text-white dark:[&.router-link-active]:text-zinc-100 [&.router-link-active]:hover:bg-emerald-900 dark:[&.router-link-active]:hover:bg-emerald-600 [&.router-link-active]:hover:text-white">
              <FileText class="w-4 h-4 mr-3 group-hover:scale-110 transition-transform" /> Laporan & Ekspor
            </router-link>
          </div>
        </div>

        <!-- PENGAJUAN -->
        <div>
          <div class="text-[11px] font-bold text-gray-400 dark:text-gray-500 dark:text-zinc-400 uppercase tracking-widest mb-3 px-3">Pengajuan</div>
          <div class="space-y-1">
            <router-link :to="getRoute('/leaves')"  class="flex items-center px-3 py-2.5 text-sm text-gray-500 dark:text-gray-400 dark:text-zinc-500 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-900 rounded-lg transition-all duration-200 group [&.router-link-active]:bg-emerald-800 dark:[&.router-link-active]:bg-emerald-700 [&.router-link-active]:text-white dark:[&.router-link-active]:text-zinc-100 [&.router-link-active]:hover:bg-emerald-900 dark:[&.router-link-active]:hover:bg-emerald-600 [&.router-link-active]:hover:text-white">
              <CalendarIcon class="w-4 h-4 mr-3 group-hover:scale-110 transition-transform" /> Cuti
            </router-link>
            <router-link :to="getRoute('/overtime')"  class="flex items-center px-3 py-2.5 text-sm text-gray-500 dark:text-gray-400 dark:text-zinc-500 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-900 rounded-lg transition-all duration-200 group [&.router-link-active]:bg-emerald-800 dark:[&.router-link-active]:bg-emerald-700 [&.router-link-active]:text-white dark:[&.router-link-active]:text-zinc-100 [&.router-link-active]:hover:bg-emerald-900 dark:[&.router-link-active]:hover:bg-emerald-600 [&.router-link-active]:hover:text-white">
              <Clock class="w-4 h-4 mr-3 group-hover:scale-110 transition-transform" /> Lembur
            </router-link>
          </div>
        </div>

        <!-- PENGATURAN -->
        <div v-if="authStore.user?.role === 'admin'">
          <div class="text-[11px] font-bold text-gray-400 dark:text-gray-500 dark:text-zinc-400 uppercase tracking-widest mb-3 px-3">Sistem</div>
          <div class="space-y-1">
            <router-link :to="getRoute('/settings')"  class="flex items-center px-3 py-2.5 text-sm text-gray-500 dark:text-gray-400 dark:text-zinc-500 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-900 rounded-lg transition-all duration-200 group [&.router-link-active]:bg-emerald-800 dark:[&.router-link-active]:bg-emerald-700 [&.router-link-active]:text-white dark:[&.router-link-active]:text-zinc-100 [&.router-link-active]:hover:bg-emerald-900 dark:[&.router-link-active]:hover:bg-emerald-600 [&.router-link-active]:hover:text-white">
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
          <p class="text-[11px] text-gray-500 dark:text-gray-400 dark:text-zinc-500 truncate">{{ authStore.user?.role === 'admin' ? 'Super Admin' : 'Employee' }}</p>
        </div>
      </div>
    </div>
  </aside>
</template>
