<script setup lang="ts">
import { ref } from "vue";
import { useAuthStore } from "@/stores/auth";
import { useRouter } from "vue-router";
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import { LogIn, Loader2, Eye, EyeOff, ShieldCheck } from "lucide-vue-next";

const authStore = useAuthStore();
const router = useRouter();

const email = ref("");
const password = ref("");
const showPassword = ref(false);
const loading = ref(false);
const error = ref("");

const handleLogin = async () => {
  loading.value = true;
  error.value = "";
  try {
    await authStore.login({ email: email.value, password: password.value });
    router.push("/");
  } catch (err: any) {
    error.value =
      err.response?.data?.message || "Login gagal. Silakan coba lagi.";
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="min-h-screen w-full flex bg-gray-50 dark:bg-zinc-950 font-sans">
    
    <!-- Left Section - Branding (Hidden on mobile) -->
    <div class="hidden lg:flex w-1/2 bg-orange-500 relative flex-col justify-between overflow-hidden">
      <!-- Decorative pattern / shapes -->
      <div class="absolute top-0 left-0 w-full h-full overflow-hidden opacity-10">
        <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full border-[40px] border-white"></div>
        <div class="absolute top-1/2 right-12 w-64 h-64 rounded-full border-[20px] border-white"></div>
        <div class="absolute -bottom-32 -right-32 w-128 h-128 rounded-full border-[60px] border-white"></div>
      </div>

      <div class="relative z-10 p-12 mt-12">
        <div class="flex items-center space-x-3 mb-10">
          <span class="text-white text-3xl font-bold tracking-tight">HRIS System</span>
        </div>
        
        <h1 class="text-white text-5xl font-bold leading-tight mb-6">
          Kelola Karyawan<br />Lebih Efisien.
        </h1>
        <p class="text-orange-100 text-lg max-w-md leading-relaxed">
          Sistem Informasi Manajemen SDM terpadu untuk absensi, penggajian, dan performa tim Anda dalam satu platform yang mudah digunakan.
        </p>
      </div>

      <div class="relative z-10 p-12">
        <div class="text-white text-sm font-medium opacity-80">
          Sistem Informasi Manajemen SDM &copy; 2026
        </div>
      </div>
    </div>

    <!-- Right Section - Login Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 lg:p-12 relative bg-white dark:bg-zinc-950">
      
      <!-- Mobile Logo Header -->
      <div class="absolute top-8 left-8 flex items-center space-x-2 lg:hidden">
        <span class="text-gray-900 dark:text-white text-lg font-bold">HRIS System</span>
      </div>

      <div class="w-full max-w-md">
        <div class="mb-10">
          <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Selamat Datang 👋</h2>
          <p class="text-gray-500 dark:text-zinc-400">Masuk ke akun Anda untuk mengakses dasbor.</p>
        </div>

        <form @submit.prevent="handleLogin" class="space-y-5">
          <!-- Error Alert -->
          <div
            v-if="error"
            class="p-4 text-sm text-red-600 bg-red-50 dark:bg-red-900/20 dark:text-red-400 border border-red-100 dark:border-red-900/50 rounded-xl flex items-start"
          >
            <ShieldCheck class="w-5 h-5 mr-2 flex-shrink-0" />
            <span>{{ error }}</span>
          </div>

          <!-- Email Input -->
          <div class="space-y-1.5">
            <Label for="email" class="text-sm font-medium text-gray-700 dark:text-gray-300">Email Perusahaan</Label>
            <Input
              id="email"
              type="email"
              placeholder="nama@perusahaan.com"
              required
              v-model="email"
              class="h-11 px-4 bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 focus:border-orange-500 focus:ring-orange-500/20 rounded-xl transition-all"
            />
          </div>

          <!-- Password Input -->
          <div class="space-y-1.5">
            <div class="flex items-center justify-between">
              <Label for="password" class="text-sm font-medium text-gray-700 dark:text-gray-300">Kata Sandi</Label>
              <router-link to="/forgot-password" class="text-sm font-medium text-orange-600 hover:text-orange-700 dark:text-orange-500">Lupa sandi?</router-link>
            </div>
            <div class="relative">
              <Input
                id="password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="••••••••"
                required
                v-model="password"
                class="h-11 px-4 pr-12 bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 focus:border-orange-500 focus:ring-orange-500/20 rounded-xl transition-all"
              />
              <button 
                type="button" 
                @click="showPassword = !showPassword" 
                class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors focus:outline-none"
              >
                <Eye v-if="!showPassword" class="w-5 h-5" />
                <EyeOff v-else class="w-5 h-5" />
              </button>
            </div>
          </div>



          <!-- Submit Button -->
          <Button 
            type="submit" 
            class="w-full h-12 mt-6 text-base font-semibold bg-orange-600 hover:bg-orange-700 text-white rounded-xl  transition-all active:scale-[0.98]" 
            :disabled="loading"
          >
            <Loader2 v-if="loading" class="w-5 h-5 mr-2 animate-spin" />
            <LogIn v-else class="w-5 h-5 mr-2" />
            {{ loading ? "Sedang Memproses..." : "Masuk ke Dasbor" }}
          </Button>
        </form>

        <p class="text-center text-sm text-gray-500 dark:text-zinc-500 mt-10">
          &copy; 2026 PT HRIS Tech. Hak Cipta Dilindungi.
        </p>
      </div>
    </div>
  </div>
</template>
