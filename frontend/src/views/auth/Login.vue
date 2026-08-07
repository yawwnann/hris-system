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
import { LogIn, Loader2 } from "lucide-vue-next";

const authStore = useAuthStore();
const router = useRouter();

const email = ref("admin@hris.com");
const password = ref("password");
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
      err.response?.data?.message || "Login failed. Please try again.";
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div
    class="min-h-screen flex flex-col items-center justify-center bg-muted/40 p-4"
  >
    <div class="w-full max-w-md">
      <div class="flex items-center justify-center space-x-2 mb-8">
        <div
          class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center"
        >
          <span class="text-primary-foreground font-bold text-lg">HR</span>
        </div>
        <h1 class="text-2xl font-bold tracking-tight">HRIS System</h1>
      </div>

      <Card>
        <CardHeader class="space-y-1 text-center">
          <CardTitle class="text-2xl font-bold">Welcome back</CardTitle>
          <CardDescription>
            Enter your email and password to sign in
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="handleLogin" class="space-y-4">
            <div
              v-if="error"
              class="p-3 text-sm text-destructive-foreground bg-destructive/10 border border-destructive/20 rounded-md"
            >
              {{ error }}
            </div>

            <div class="space-y-2">
              <Label for="email">Email address</Label>
              <Input
                id="email"
                type="email"
                placeholder="name@example.com"
                required
                v-model="email"
              />
            </div>

            <div class="space-y-2">
              <div class="flex items-center justify-between">
                <Label for="password">Password</Label>
              </div>
              <Input
                id="password"
                type="password"
                required
                v-model="password"
              />
            </div>

            <Button type="submit" class="w-full mt-6" :disabled="loading">
              <Loader2 v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
              <LogIn v-else class="w-4 h-4 mr-2" />
              {{ loading ? "Signing in..." : "Sign In" }}
            </Button>
          </form>
        </CardContent>
      </Card>

      <p class="text-center text-sm text-muted-foreground mt-8">
        &copy; 2026 PT HRIS Tech. All rights reserved.
      </p>
    </div>
  </div>
</template>
