<script setup lang="ts">
import { ref } from "vue";
import { useAuthStore } from "@/stores/auth";
import { useRouter } from "vue-router";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import { AlertCircle, Eye, EyeOff, Loader2, LogIn } from "lucide-vue-next";
import AuthShell from "./AuthShell.vue";
import {
  authEnterClass,
  authErrorClass,
  authEyeButtonClass,
  authHeadingClass,
  authInputClass,
  authLabelClass,
  authLeadClass,
  authLinkClass,
  authPrimaryButtonClass,
} from "./auth-ui";

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
  <AuthShell>
    <div :class="authEnterClass">
      <div class="mb-8">
        <h1 :class="authHeadingClass">Selamat datang kembali</h1>
        <p :class="authLeadClass">Masuk ke akun Lazizmu Anda untuk mengakses dasbor.</p>
      </div>

      <form @submit.prevent="handleLogin" class="space-y-5">
        <!-- Error Alert -->
        <div v-if="error" role="alert" :class="authErrorClass">
          <AlertCircle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
          <span>{{ error }}</span>
        </div>

        <!-- Email Input -->
        <div class="space-y-1.5">
          <Label for="email" :class="authLabelClass">Email Perusahaan</Label>
          <Input
            id="email"
            type="email"
            placeholder="nama@perusahaan.com"
            autocomplete="email"
            required
            v-model="email"
            :class="authInputClass"
          />
        </div>

        <!-- Password Input -->
        <div class="space-y-1.5">
          <div class="flex items-center justify-between">
            <Label for="password" :class="authLabelClass">Kata Sandi</Label>
            <router-link to="/forgot-password" :class="[authLinkClass, 'text-sm']">
              Lupa sandi?
            </router-link>
          </div>
          <div class="relative">
            <Input
              id="password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="••••••••"
              autocomplete="current-password"
              required
              v-model="password"
              :class="[authInputClass, 'pr-12']"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              :class="authEyeButtonClass"
              :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
              :aria-pressed="showPassword"
            >
              <Eye v-if="!showPassword" class="size-5" aria-hidden="true" />
              <EyeOff v-else class="size-5" aria-hidden="true" />
            </button>
          </div>
        </div>

        <!-- Submit Button -->
        <Button type="submit" :class="[authPrimaryButtonClass, 'mt-2']" :disabled="loading">
          <Loader2 v-if="loading" class="animate-spin" aria-hidden="true" />
          <LogIn v-else aria-hidden="true" />
          {{ loading ? "Sedang Memproses..." : "Masuk ke Dasbor" }}
        </Button>
      </form>

      <p class="mt-8 text-center text-sm text-gray-500 dark:text-zinc-400">
        Belum punya akun?
        <router-link to="/register" :class="authLinkClass">Daftar sekarang</router-link>
      </p>
    </div>
  </AuthShell>
</template>
