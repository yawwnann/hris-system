<script setup lang="ts">
import { Bell, Moon, Sun, Search, LogOut } from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { useAuthStore } from "@/stores/auth";
import { useRouter } from "vue-router";
import { useDark, useToggle } from "@vueuse/core";

const authStore = useAuthStore();
const router = useRouter();

const isDark = useDark();
const toggleDark = useToggle(isDark);

const handleLogout = () => {
  authStore.logout();
  router.push("/login");
};
</script>

<template>
  <header
    class="h-16 bg-white dark:bg-zinc-950 border-b-2 border-gray-100 dark:border-zinc-800 flex items-center justify-between px-8 sticky top-0 z-10 transition-colors"
  >
    <div class="flex items-center flex-1">
      <div class="relative w-96">
        <Search
          class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 dark:text-zinc-400"
        />
        <Input
          class="pl-9 bg-gray-50 dark:bg-zinc-900 border-none w-full focus-visible:ring-0 text-gray-900 dark:text-gray-100"
          placeholder="Search..."
        />
      </div>
    </div>
    <div class="flex items-center space-x-2">
      <Button variant="ghost" size="icon" class="relative text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-800 rounded-full h-9 w-9">
        <Bell class="w-5 h-5" />
        <span class="absolute top-1 right-1.5 w-2 h-2 bg-red-50 dark:bg-red-900/200 rounded-full border border-white dark:border-zinc-900"></span>
      </Button>
      
      <Button @click="toggleDark()" variant="ghost" size="icon" class="text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-800 rounded-full h-9 w-9">
        <Moon v-if="!isDark" class="w-5 h-5" />
        <Sun v-else class="w-5 h-5" />
      </Button>
      
      <div class="w-px h-6 bg-gray-200 dark:bg-zinc-800 mx-2"></div>
      
      <Button @click="handleLogout" variant="ghost" size="icon" class="text-red-500 hover:text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-full h-9 w-9" title="Logout">
        <LogOut class="w-4 h-4" />
      </Button>
    </div>
  </header>
</template>
