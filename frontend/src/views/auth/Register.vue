<script setup lang="ts">
import { ref } from "vue";
import { useAuthStore } from "@/stores/auth";
import { useRouter } from "vue-router";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import {
  AlertCircle,
  CheckCircle2,
  Eye,
  EyeOff,
  Loader2,
  UserPlus,
} from "lucide-vue-next";
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
  authSuccessChipClass,
} from "./auth-ui";

const authStore = useAuthStore();
const router = useRouter();

const name = ref("");
const email = ref("");
const password = ref("");
const confirmPassword = ref("");
const showPassword = ref(false);
const showConfirmPassword = ref(false);
const loading = ref(false);
const error = ref("");
const registered = ref(false);

const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

const validate = (): string => {
  if (!name.value.trim()) return "Nama lengkap wajib diisi.";
  if (!EMAIL_PATTERN.test(email.value.trim())) return "Format email tidak valid.";
  if (password.value.length < 6) return "Kata sandi minimal 6 karakter.";
  if (password.value !== confirmPassword.value) return "Konfirmasi kata sandi tidak cocok.";
  return "";
};

const handleRegister = async () => {
  error.value = validate();
  if (error.value) return;

  loading.value = true;
  try {
    await authStore.register({
      name: name.value.trim(),
      email: email.value.trim(),
      password: password.value,
      password_confirmation: confirmPassword.value,
    });
    if (authStore.user) {
      router.push("/");
    } else {
      registered.value = true;
    }
  } catch (err: any) {
    const data = err.response?.data;
    error.value =
      data?.errors?.email?.[0] ||
      data?.errors?.password?.[0] ||
      data?.errors?.name?.[0] ||
      (err.response?.status === 404
        ? "Layanan pendaftaran belum tersedia. Silakan hubungi administrator."
        : data?.message) ||
      "Pendaftaran gagal. Silakan coba lagi.";
  } finally {
    loading.value = false;
  }
};

const goToLogin = () => {
  router.push("/login");
};
</script>

<template>
  <AuthShell>
    <!-- Success state (backend did not sign the user in automatically) -->
    <div v-if="registered" class="animate-in zoom-in-95 fade-in duration-500 text-center motion-reduce:animate-none">
      <div :class="authSuccessChipClass">
        <CheckCircle2 class="size-10" aria-hidden="true" />
      </div>
      <h1 :class="authHeadingClass">Akun Berhasil Dibuat!</h1>
      <p :class="[authLeadClass, 'mb-8']">
        Pendaftaran Anda telah diterima. Silakan masuk menggunakan email dan kata sandi yang baru saja Anda buat.
      </p>
      <Button @click="goToLogin" :class="authPrimaryButtonClass">Masuk Sekarang</Button>
    </div>

    <!-- Registration form -->
    <div v-else :class="authEnterClass">
      <div class="mb-8">
        <h1 :class="authHeadingClass">Buat akun baru</h1>
        <p :class="authLeadClass">Lengkapi data di bawah ini untuk mulai menggunakan Lazizmu.</p>
      </div>

      <form @submit.prevent="handleRegister" class="space-y-5">
        <!-- Error Alert -->
        <div v-if="error" role="alert" :class="authErrorClass">
          <AlertCircle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
          <span>{{ error }}</span>
        </div>

        <!-- Full name -->
        <div class="space-y-1.5">
          <Label for="name" :class="authLabelClass">Nama Lengkap</Label>
          <Input
            id="name"
            type="text"
            placeholder="Nama sesuai identitas"
            autocomplete="name"
            required
            v-model="name"
            :class="authInputClass"
          />
        </div>

        <!-- Email -->
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

        <!-- Password -->
        <div class="space-y-1.5">
          <Label for="password" :class="authLabelClass">Kata Sandi</Label>
          <div class="relative">
            <Input
              id="password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="Minimal 6 karakter"
              autocomplete="new-password"
              minlength="6"
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

        <!-- Confirm password -->
        <div class="space-y-1.5">
          <Label for="confirmPassword" :class="authLabelClass">Konfirmasi Kata Sandi</Label>
          <div class="relative">
            <Input
              id="confirmPassword"
              :type="showConfirmPassword ? 'text' : 'password'"
              placeholder="Ulangi kata sandi"
              autocomplete="new-password"
              minlength="6"
              required
              v-model="confirmPassword"
              :class="[authInputClass, 'pr-12']"
            />
            <button
              type="button"
              @click="showConfirmPassword = !showConfirmPassword"
              :class="authEyeButtonClass"
              :aria-label="showConfirmPassword ? 'Sembunyikan konfirmasi kata sandi' : 'Tampilkan konfirmasi kata sandi'"
              :aria-pressed="showConfirmPassword"
            >
              <Eye v-if="!showConfirmPassword" class="size-5" aria-hidden="true" />
              <EyeOff v-else class="size-5" aria-hidden="true" />
            </button>
          </div>
        </div>

        <!-- Submit -->
        <Button type="submit" :class="[authPrimaryButtonClass, 'mt-2']" :disabled="loading">
          <Loader2 v-if="loading" class="animate-spin" aria-hidden="true" />
          <UserPlus v-else aria-hidden="true" />
          {{ loading ? "Mendaftarkan..." : "Buat Akun" }}
        </Button>
      </form>

      <p class="mt-8 text-center text-sm text-gray-500 dark:text-zinc-400">
        Sudah punya akun?
        <router-link to="/login" :class="authLinkClass">Masuk di sini</router-link>
      </p>
    </div>
  </AuthShell>
</template>
