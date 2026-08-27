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
          <div class="text-[11px] font-bold text-gray-400 dark:text-gray-500 dark:text-zinc-400 uppercase tracking-widest mb-3 px-3">Main Menu</div>
          <div class="space-y-1">
            <router-link to="/" class="flex items-center px-3 py-2.5 text-sm text-gray-500 dark:text-gray-400 dark:text-zinc-500 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-50 dark:hover:bg-zinc-900 rounded-lg transition-all duration-200 group" active-class="bg-gray-900 dark:bg-zinc-100 text-white dark:text-zinc-900 hover:text-white dark:hover:text-zinc-900 hover:bg-gray-900 dark:hover:bg-zinc-100">
              <LayoutDashboard class="w-4 h-4 mr-3 group-hover:scale-110 transition-transform" /> Dashboard
            </router-link>
            <router-link to="/attendance" class="flex items-center px-3 py-2.5 text-sm text-gray-500 dark:text-gray-400 dark:text-zinc-500 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-50 dark:hover:bg-zinc-900 rounded-lg transition-all duration-200 group" active-class="bg-gray-900 dark:bg-zinc-100 text-white dark:text-zinc-900 hover:text-white dark:hover:text-zinc-900 hover:bg-gray-900 dark:hover:bg-zinc-100">
              <UserCheck class="w-4 h-4 mr-3 group-hover:scale-110 transition-transform" /> Attendance
            </router-link>
          </div>
        </div>

        <!-- MANAJEMEN KARYAWAN (Admin Only) -->
        <div v-if="authStore.user?.role === 'admin'">
          <div class="text-[11px] font-bold text-gray-400 dark:text-gray-500 dark:text-zinc-400 uppercase tracking-widest mb-3 px-3">Management</div>
          <div class="space-y-1">
            <router-link to="/employees" class="flex items-center px-3 py-2.5 text-sm text-gray-500 dark:text-gray-400 dark:text-zinc-500 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-50 dark:hover:bg-zinc-900 rounded-lg transition-all duration-200 group" active-class="bg-gray-900 dark:bg-zinc-100 text-white dark:text-zinc-900 hover:text-white dark:hover:text-zinc-900 hover:bg-gray-900 dark:hover:bg-zinc-100">
              <Users class="w-4 h-4 mr-3 group-hover:scale-110 transition-transform" /> Employees
            </router-link>
            <router-link to="/departments" class="flex items-center px-3 py-2.5 text-sm text-gray-500 dark:text-gray-400 dark:text-zinc-500 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-50 dark:hover:bg-zinc-900 rounded-lg transition-all duration-200 group" active-class="bg-gray-900 dark:bg-zinc-100 text-white dark:text-zinc-900 hover:text-white dark:hover:text-zinc-900 hover:bg-gray-900 dark:hover:bg-zinc-100">
              <FileText class="w-4 h-4 mr-3 group-hover:scale-110 transition-transform" /> Departments
            </router-link>
          </div>
        </div>

        <!-- INFORMASI / KOMUNIKASI -->
        <div>
          <div class="text-[11px] font-bold text-gray-400 dark:text-gray-500 dark:text-zinc-400 uppercase tracking-widest mb-3 px-3">Information</div>
          <div class="space-y-1">
            <router-link to="/calendar" class="flex items-center px-3 py-2.5 text-sm text-gray-500 dark:text-gray-400 dark:text-zinc-500 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-50 dark:hover:bg-zinc-900 rounded-lg transition-all duration-200 group" active-class="bg-gray-900 dark:bg-zinc-100 text-white dark:text-zinc-900 hover:text-white dark:hover:text-zinc-900 hover:bg-gray-900 dark:hover:bg-zinc-100">
              <CalendarIcon class="w-4 h-4 mr-3 group-hover:scale-110 transition-transform" /> Calendar Management
            </router-link>
            <router-link to="/announcements" class="flex items-center px-3 py-2.5 text-sm text-gray-500 dark:text-gray-400 dark:text-zinc-500 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-50 dark:hover:bg-zinc-900 rounded-lg transition-all duration-200 group" active-class="bg-gray-900 dark:bg-zinc-100 text-white dark:text-zinc-900 hover:text-white dark:hover:text-zinc-900 hover:bg-gray-900 dark:hover:bg-zinc-100">
              <FileText class="w-4 h-4 mr-3 group-hover:scale-110 transition-transform" /> Announcements
            </router-link>
            <router-link v-if="authStore.user?.role === 'admin'" to="/reports" class="flex items-center px-3 py-2.5 text-sm text-gray-500 dark:text-gray-400 dark:text-zinc-500 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-50 dark:hover:bg-zinc-900 rounded-lg transition-all duration-200 group" active-class="bg-gray-900 dark:bg-zinc-100 text-white dark:text-zinc-900 hover:text-white dark:hover:text-zinc-900 hover:bg-gray-900 dark:hover:bg-zinc-100">
              <FileText class="w-4 h-4 mr-3 group-hover:scale-110 transition-transform" /> Reports & Export
            </router-link>
          </div>
        </div>

        <!-- PENGAJUAN -->
        <div>
          <div class="text-[11px] font-bold text-gray-400 dark:text-gray-500 dark:text-zinc-400 uppercase tracking-widest mb-3 px-3">Requests</div>
          <div class="space-y-1">
            <router-link to="/leaves" class="flex items-center px-3 py-2.5 text-sm text-gray-500 dark:text-gray-400 dark:text-zinc-500 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-50 dark:hover:bg-zinc-900 rounded-lg transition-all duration-200 group" active-class="bg-gray-900 dark:bg-zinc-100 text-white dark:text-zinc-900 hover:text-white dark:hover:text-zinc-900 hover:bg-gray-900 dark:hover:bg-zinc-100">
              <CalendarIcon class="w-4 h-4 mr-3 group-hover:scale-110 transition-transform" /> Leaves
            </router-link>
            <router-link to="/overtime" class="flex items-center px-3 py-2.5 text-sm text-gray-500 dark:text-gray-400 dark:text-zinc-500 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-50 dark:hover:bg-zinc-900 rounded-lg transition-all duration-200 group" active-class="bg-gray-900 dark:bg-zinc-100 text-white dark:text-zinc-900 hover:text-white dark:hover:text-zinc-900 hover:bg-gray-900 dark:hover:bg-zinc-100">
              <Clock class="w-4 h-4 mr-3 group-hover:scale-110 transition-transform" /> Overtime
            </router-link>
          </div>
        </div>

        <!-- PENGATURAN -->
        <div v-if="authStore.user?.role === 'admin'">
          <div class="text-[11px] font-bold text-gray-400 dark:text-gray-500 dark:text-zinc-400 uppercase tracking-widest mb-3 px-3">System</div>
          <div class="space-y-1">
            <router-link to="/settings" class="flex items-center px-3 py-2.5 text-sm text-gray-500 dark:text-gray-400 dark:text-zinc-500 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-50 dark:hover:bg-zinc-900 rounded-lg transition-all duration-200 group" active-class="bg-gray-900 dark:bg-zinc-100 text-white dark:text-zinc-900 hover:text-white dark:hover:text-zinc-900 hover:bg-gray-900 dark:hover:bg-zinc-100">
              <Settings class="w-4 h-4 mr-3 group-hover:scale-110 transition-transform" /> Settings
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
