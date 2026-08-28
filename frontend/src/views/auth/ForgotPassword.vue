<script setup lang="ts">
import { ref } from "vue";
import { useRouter } from "vue-router";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import { Loader2, Mail, KeyRound, CheckCircle2, ArrowLeft, Lock } from "lucide-vue-next";

const router = useRouter();

// Steps: 1 = Email, 2 = OTP, 3 = Reset Password, 4 = Success
const step = ref(1);
const loading = ref(false);
const error = ref("");

// Form Data
const email = ref("");
const otp = ref("");
const newPassword = ref("");
const confirmPassword = ref("");
const showPassword = ref(false);

const handleSendEmail = async () => {
  if (!email.value) return;
  loading.value = true;
  error.value = "";
  // Simulate API Call
  setTimeout(() => {
    loading.value = false;
    if (!email.value.includes("@")) {
      error.value = "Format email tidak valid.";
      return;
    }
    step.value = 2; // Go to OTP
  }, 1000);
};

const handleVerifyOTP = async () => {
  if (otp.value.length < 4) return;
  loading.value = true;
  error.value = "";
  // Simulate API Call
  setTimeout(() => {
    loading.value = false;
    if (otp.value !== "123456" && otp.value !== "000000") { // Just accept any 6 digit for mock, or specific ones. Actually let's just accept anything >= 4 chars for demo
      // step.value = 3;
    }
    step.value = 3; // Go to Reset Password
  }, 1000);
};

const handleResetPassword = async () => {
  if (newPassword.value !== confirmPassword.value) {
    error.value = "Kata sandi tidak cocok.";
    return;
  }
  if (newPassword.value.length < 6) {
    error.value = "Kata sandi minimal 6 karakter.";
    return;
  }
  
  loading.value = true;
  error.value = "";
  // Simulate API Call
  setTimeout(() => {
    loading.value = false;
    step.value = 4; // Go to Success
  }, 1500);
};

const goToLogin = () => {
  router.push("/login");
};
</script>

<template>
  <div class="min-h-screen w-full flex bg-gray-50 dark:bg-zinc-950 font-sans">
    
    <!-- Left Section - Same Branding as Login -->
    <div class="hidden lg:flex w-1/2 bg-orange-500 relative flex-col justify-between overflow-hidden">
      <!-- Decorative pattern -->
      <div class="absolute top-0 left-0 w-full h-full overflow-hidden opacity-10 pointer-events-none">
        <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full border-[40px] border-white"></div>
        <div class="absolute top-1/2 right-12 w-64 h-64 rounded-full border-[20px] border-white"></div>
        <div class="absolute -bottom-32 -right-32 w-128 h-128 rounded-full border-[60px] border-white"></div>
      </div>

      <div class="relative z-10 p-12 mt-12">
        <div class="flex items-center space-x-3 mb-10 cursor-pointer" @click="goToLogin">
          <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-lg">
            <span class="text-orange-600 font-extrabold text-2xl tracking-tighter">HR</span>
          </div>
          <span class="text-white text-3xl font-bold tracking-tight">HRIS System</span>
        </div>
        
        <h1 class="text-white text-5xl font-bold leading-tight mb-6">
          Keamanan Akun<br />Terjamin.
        </h1>
        <p class="text-orange-100 text-lg max-w-md leading-relaxed">
          Kami memprioritaskan keamanan data Anda. Ikuti langkah mudah untuk memulihkan akses ke akun Anda.
        </p>
      </div>

      <div class="relative z-10 p-12">
        <div class="text-white text-sm font-medium opacity-80">
          Sistem Informasi Manajemen SDM &copy; 2026
        </div>
      </div>
    </div>

    <!-- Right Section - Forms -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 lg:p-12 relative bg-white dark:bg-zinc-950">
      
      <!-- Mobile Back Button -->
      <button @click="goToLogin" class="absolute top-8 left-8 flex items-center text-sm font-medium text-gray-500 hover:text-gray-900 dark:hover:text-white transition-colors lg:hidden">
        <ArrowLeft class="w-4 h-4 mr-2" />
        Kembali ke Login
      </button>

      <!-- Desktop Back Button (Optional, as they can use browser back, but good UX) -->
      <button v-if="step === 1" @click="goToLogin" class="absolute top-12 right-12 hidden lg:flex items-center text-sm font-medium text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
        <ArrowLeft class="w-4 h-4 mr-2" />
        Kembali ke Login
      </button>

      <div class="w-full max-w-md">
        
        <!-- Error Alert -->
        <div
          v-if="error"
          class="mb-6 p-4 text-sm text-red-600 bg-red-50 dark:bg-red-900/20 dark:text-red-400 border border-red-100 dark:border-red-900/50 rounded-xl flex items-start"
        >
          <span>{{ error }}</span>
        </div>

        <!-- STEP 1: Enter Email -->
        <div v-if="step === 1" class="animate-in fade-in slide-in-from-bottom-4 duration-500">
          <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-500 rounded-2xl flex items-center justify-center mb-6 shadow-inner">
            <Mail class="w-6 h-6" />
          </div>
          <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Lupa Kata Sandi?</h2>
          <p class="text-gray-500 dark:text-zinc-400 mb-8">Jangan khawatir! Masukkan alamat email yang terdaftar, dan kami akan mengirimkan kode verifikasi (OTP).</p>

          <form @submit.prevent="handleSendEmail" class="space-y-5">
            <div class="space-y-1.5">
              <Label for="email" class="text-sm font-medium text-gray-700 dark:text-gray-300">Email Perusahaan</Label>
              <Input
                id="email"
                type="email"
                placeholder="nama@perusahaan.com"
                required
                v-model="email"
                class="h-12 px-4 bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 focus:border-orange-500 focus:ring-orange-500/20 rounded-xl transition-all"
              />
            </div>
            
            <Button 
              type="submit" 
              class="w-full h-12 mt-2 text-base font-semibold bg-orange-600 hover:bg-orange-700 text-white rounded-xl shadow-lg shadow-orange-500/30 transition-all active:scale-[0.98]" 
              :disabled="loading"
            >
              <Loader2 v-if="loading" class="w-5 h-5 mr-2 animate-spin" />
              {{ loading ? "Mengirim..." : "Kirim Kode OTP" }}
            </Button>
          </form>
        </div>

        <!-- STEP 2: Enter OTP -->
        <div v-else-if="step === 2" class="animate-in fade-in slide-in-from-right-8 duration-500">
          <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-500 rounded-2xl flex items-center justify-center mb-6 shadow-inner">
            <KeyRound class="w-6 h-6" />
          </div>
          <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Verifikasi OTP</h2>
          <p class="text-gray-500 dark:text-zinc-400 mb-8">
            Kami telah mengirimkan kode 6-digit ke <span class="font-medium text-gray-900 dark:text-gray-200">{{ email }}</span>.
          </p>

          <form @submit.prevent="handleVerifyOTP" class="space-y-5">
            <div class="space-y-1.5">
              <Label for="otp" class="text-sm font-medium text-gray-700 dark:text-gray-300">Kode OTP</Label>
              <Input
                id="otp"
                type="text"
                placeholder="000000"
                required
                maxlength="6"
                v-model="otp"
                class="h-12 px-4 text-center tracking-[0.5em] text-xl font-bold bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 focus:border-orange-500 focus:ring-orange-500/20 rounded-xl transition-all"
              />
            </div>
            
            <Button 
              type="submit" 
              class="w-full h-12 mt-2 text-base font-semibold bg-orange-600 hover:bg-orange-700 text-white rounded-xl shadow-lg shadow-orange-500/30 transition-all active:scale-[0.98]" 
              :disabled="loading"
            >
              <Loader2 v-if="loading" class="w-5 h-5 mr-2 animate-spin" />
              {{ loading ? "Memverifikasi..." : "Verifikasi Kode" }}
            </Button>
            
            <div class="text-center mt-6">
              <button type="button" @click="step = 1; otp = ''" class="text-sm font-medium text-orange-600 hover:text-orange-700 dark:text-orange-500 transition-colors">
                Salah email? Kembali
              </button>
            </div>
          </form>
        </div>

        <!-- STEP 3: Reset Password -->
        <div v-else-if="step === 3" class="animate-in fade-in slide-in-from-right-8 duration-500">
          <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-500 rounded-2xl flex items-center justify-center mb-6 shadow-inner">
            <Lock class="w-6 h-6" />
          </div>
          <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Buat Sandi Baru</h2>
          <p class="text-gray-500 dark:text-zinc-400 mb-8">Sandi baru Anda harus unik dan berbeda dari kata sandi sebelumnya.</p>

          <form @submit.prevent="handleResetPassword" class="space-y-5">
            <div class="space-y-1.5">
              <Label for="newPassword" class="text-sm font-medium text-gray-700 dark:text-gray-300">Kata Sandi Baru</Label>
              <Input
                id="newPassword"
                type="password"
                placeholder="Minimal 6 karakter"
                required
                v-model="newPassword"
                class="h-12 px-4 bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 focus:border-orange-500 focus:ring-orange-500/20 rounded-xl transition-all"
              />
            </div>
            
            <div class="space-y-1.5">
              <Label for="confirmPassword" class="text-sm font-medium text-gray-700 dark:text-gray-300">Konfirmasi Sandi Baru</Label>
              <Input
                id="confirmPassword"
                type="password"
                placeholder="Ulangi kata sandi baru"
                required
                v-model="confirmPassword"
                class="h-12 px-4 bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 focus:border-orange-500 focus:ring-orange-500/20 rounded-xl transition-all"
              />
            </div>
            
            <Button 
              type="submit" 
              class="w-full h-12 mt-4 text-base font-semibold bg-orange-600 hover:bg-orange-700 text-white rounded-xl shadow-lg shadow-orange-500/30 transition-all active:scale-[0.98]" 
              :disabled="loading"
            >
              <Loader2 v-if="loading" class="w-5 h-5 mr-2 animate-spin" />
              {{ loading ? "Menyimpan..." : "Simpan Sandi Baru" }}
            </Button>
          </form>
        </div>

        <!-- STEP 4: Success -->
        <div v-else-if="step === 4" class="animate-in zoom-in-95 fade-in duration-500 text-center">
          <div class="w-20 h-20 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
            <CheckCircle2 class="w-10 h-10" />
          </div>
          <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Sandi Diperbarui!</h2>
          <p class="text-gray-500 dark:text-zinc-400 mb-8 leading-relaxed">
            Kata sandi Anda telah berhasil diubah. Silakan gunakan kata sandi baru Anda untuk masuk ke sistem.
          </p>
          
          <Button 
            @click="goToLogin" 
            class="w-full h-12 text-base font-semibold bg-orange-600 hover:bg-orange-700 text-white rounded-xl shadow-lg shadow-orange-500/30 transition-all active:scale-[0.98]"
          >
            Kembali ke Login
          </Button>
        </div>

      </div>
    </div>
  </div>
</template>
