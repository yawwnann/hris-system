<script setup lang="ts">
import { ref } from "vue";
import { useRouter } from "vue-router";
import api from "@/lib/axios";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import {
  AlertCircle,
  ArrowLeft,
  CheckCircle2,
  Eye,
  EyeOff,
  KeyRound,
  Loader2,
  Lock,
  Mail,
} from "lucide-vue-next";
import AuthShell from "./AuthShell.vue";
import {
  authEnterClass,
  authErrorClass,
  authEyeButtonClass,
  authHeadingClass,
  authIconChipClass,
  authInputClass,
  authLabelClass,
  authLeadClass,
  authLinkClass,
  authPrimaryButtonClass,
  authSuccessChipClass,
} from "./auth-ui";

const router = useRouter();

// Steps: 1 = Email, 2 = OTP, 3 = Reset Password, 4 = Success
const step = ref(1);
const loading = ref(false);
const error = ref("");

// Form Data
const email = ref("");
const otp = ref("");
const resetToken = ref("");
const newPassword = ref("");
const confirmPassword = ref("");
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const stepLabels = ["Email", "Kode OTP", "Sandi baru"];

const handleSendEmail = async () => {
  if (!email.value) return;
  loading.value = true;
  error.value = "";
  try {
    await api.post("/forgot-password/request-otp", { email: email.value });
    step.value = 2; // Go to OTP
  } catch (err: any) {
    error.value =
      err.response?.data?.message || "Gagal mengirim kode OTP. Silakan coba lagi.";
  } finally {
    loading.value = false;
  }
};

const handleVerifyOTP = async () => {
  if (otp.value.length < 6) {
    error.value = "Kode OTP harus 6 digit.";
    return;
  }
  loading.value = true;
  error.value = "";
  try {
    const { data } = await api.post("/forgot-password/verify-otp", {
      email: email.value,
      otp: otp.value,
    });
    resetToken.value = data.reset_token;
    step.value = 3; // Go to Reset Password
  } catch (err: any) {
    error.value =
      err.response?.data?.errors?.otp?.[0] ||
      err.response?.data?.message ||
      "Verifikasi OTP gagal.";
  } finally {
    loading.value = false;
  }
};

const handleResetPassword = async () => {
  if (newPassword.value !== confirmPassword.value) {
    error.value = "Konfirmasi kata sandi tidak cocok.";
    return;
  }
  if (newPassword.value.length < 6) {
    error.value = "Kata sandi minimal 6 karakter.";
    return;
  }

  loading.value = true;
  error.value = "";
  try {
    await api.post("/forgot-password/reset", {
      email: email.value,
      reset_token: resetToken.value,
      password: newPassword.value,
      password_confirmation: confirmPassword.value,
    });
    step.value = 4; // Go to Success
  } catch (err: any) {
    error.value =
      err.response?.data?.errors?.password?.[0] ||
      err.response?.data?.message ||
      "Gagal memperbarui kata sandi.";
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
    <!-- Conditional on the <template> itself so the shell renders no header row on the success step -->
    <template v-if="step !== 4" #header-action>
      <router-link
        to="/login"
        :class="[authLinkClass, 'inline-flex items-center gap-1.5 text-sm']"
      >
        <ArrowLeft class="size-4" aria-hidden="true" />
        Kembali ke Login
      </router-link>
    </template>

    <!-- Step indicator -->
    <div v-if="step !== 4" class="mb-6 flex items-center justify-between">
      <ol class="flex items-center gap-2" :aria-label="`Langkah ${step} dari ${stepLabels.length}`">
        <li
          v-for="(label, index) in stepLabels"
          :key="label"
          :aria-label="label"
          :aria-current="step === index + 1 ? 'step' : undefined"
          class="h-1.5 w-10 rounded-full transition-colors motion-reduce:transition-none"
          :class="
            step > index + 1
              ? 'bg-emerald-600'
              : step === index + 1
                ? 'bg-orange-500'
                : 'bg-gray-200 dark:bg-zinc-800'
          "
        ></li>
      </ol>
      <span class="text-xs font-medium text-gray-400 dark:text-zinc-500">
        Langkah {{ step }} dari {{ stepLabels.length }}
      </span>
    </div>

    <!-- Error Alert -->
    <div v-if="error" role="alert" :class="[authErrorClass, 'mb-6']">
      <AlertCircle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
      <span>{{ error }}</span>
    </div>

    <!-- STEP 1: Enter Email -->
    <div v-if="step === 1" :class="authEnterClass">
      <div :class="authIconChipClass">
        <Mail class="size-6" aria-hidden="true" />
      </div>
      <h1 :class="authHeadingClass">Lupa Kata Sandi?</h1>
      <p :class="[authLeadClass, 'mb-8']">
        Jangan khawatir! Masukkan alamat email yang terdaftar, dan kami akan mengirimkan kode verifikasi (OTP).
      </p>

      <form @submit.prevent="handleSendEmail" class="space-y-5">
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

        <Button type="submit" :class="[authPrimaryButtonClass, 'mt-2']" :disabled="loading">
          <Loader2 v-if="loading" class="animate-spin" aria-hidden="true" />
          {{ loading ? "Mengirim..." : "Kirim Kode OTP" }}
        </Button>
      </form>
    </div>

    <!-- STEP 2: Enter OTP -->
    <div v-else-if="step === 2" class="animate-in fade-in slide-in-from-right-8 duration-500 motion-reduce:animate-none">
      <div :class="authIconChipClass">
        <KeyRound class="size-6" aria-hidden="true" />
      </div>
      <h1 :class="authHeadingClass">Verifikasi OTP</h1>
      <p :class="[authLeadClass, 'mb-8']">
        Kami telah mengirimkan kode 6-digit ke
        <span class="font-medium text-gray-900 dark:text-gray-200">{{ email }}</span>.
      </p>

      <form @submit.prevent="handleVerifyOTP" class="space-y-5">
        <div class="space-y-1.5">
          <Label for="otp" :class="authLabelClass">Kode OTP</Label>
          <Input
            id="otp"
            type="text"
            inputmode="numeric"
            autocomplete="one-time-code"
            placeholder="000000"
            required
            maxlength="6"
            v-model="otp"
            :class="[authInputClass, 'h-12 text-center text-xl font-bold tracking-[0.5em]']"
          />
        </div>

        <Button type="submit" :class="[authPrimaryButtonClass, 'mt-2']" :disabled="loading">
          <Loader2 v-if="loading" class="animate-spin" aria-hidden="true" />
          {{ loading ? "Memverifikasi..." : "Verifikasi Kode" }}
        </Button>

        <div class="mt-6 text-center">
          <button type="button" @click="step = 1; otp = ''" :class="[authLinkClass, 'text-sm']">
            Salah email? Kembali
          </button>
        </div>
      </form>
    </div>

    <!-- STEP 3: Reset Password -->
    <div v-else-if="step === 3" class="animate-in fade-in slide-in-from-right-8 duration-500 motion-reduce:animate-none">
      <div :class="authIconChipClass">
        <Lock class="size-6" aria-hidden="true" />
      </div>
      <h1 :class="authHeadingClass">Buat Sandi Baru</h1>
      <p :class="[authLeadClass, 'mb-8']">
        Sandi baru Anda harus unik dan berbeda dari kata sandi sebelumnya.
      </p>

      <form @submit.prevent="handleResetPassword" class="space-y-5">
        <div class="space-y-1.5">
          <Label for="newPassword" :class="authLabelClass">Kata Sandi Baru</Label>
          <div class="relative">
            <Input
              id="newPassword"
              :type="showNewPassword ? 'text' : 'password'"
              placeholder="Minimal 6 karakter"
              autocomplete="new-password"
              required
              v-model="newPassword"
              :class="[authInputClass, 'pr-12']"
            />
            <button
              type="button"
              @click="showNewPassword = !showNewPassword"
              :class="authEyeButtonClass"
              :aria-label="showNewPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
              :aria-pressed="showNewPassword"
            >
              <Eye v-if="!showNewPassword" class="size-5" aria-hidden="true" />
              <EyeOff v-else class="size-5" aria-hidden="true" />
            </button>
          </div>
        </div>

        <div class="space-y-1.5">
          <Label for="confirmPassword" :class="authLabelClass">Konfirmasi Sandi Baru</Label>
          <div class="relative">
            <Input
              id="confirmPassword"
              :type="showConfirmPassword ? 'text' : 'password'"
              placeholder="Ulangi kata sandi baru"
              autocomplete="new-password"
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

        <Button type="submit" :class="[authPrimaryButtonClass, 'mt-4']" :disabled="loading">
          <Loader2 v-if="loading" class="animate-spin" aria-hidden="true" />
          {{ loading ? "Menyimpan..." : "Simpan Sandi Baru" }}
        </Button>
      </form>
    </div>

    <!-- STEP 4: Success -->
    <div v-else-if="step === 4" class="animate-in zoom-in-95 fade-in duration-500 text-center motion-reduce:animate-none">
      <div :class="authSuccessChipClass">
        <CheckCircle2 class="size-10" aria-hidden="true" />
      </div>
      <h1 :class="authHeadingClass">Sandi Diperbarui!</h1>
      <p :class="[authLeadClass, 'mb-8']">
        Kata sandi Anda telah berhasil diubah. Silakan gunakan kata sandi baru Anda untuk masuk ke sistem.
      </p>

      <Button @click="goToLogin" :class="authPrimaryButtonClass">Kembali ke Login</Button>
    </div>
  </AuthShell>
</template>
