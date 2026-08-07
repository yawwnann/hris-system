<script setup lang="ts">
import { ref, onMounted } from "vue";
import { User, Lock, Phone, MapPin, Camera, Save } from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { ScrollArea } from "@/components/ui/scroll-area";
import AppSidebar from "@/components/layout/AppSidebar.vue";
import AppHeader from "@/components/layout/AppHeader.vue";
import AppBreadcrumb from "@/components/layout/AppBreadcrumb.vue";
import api from "@/lib/axios";
import { toast } from "vue-sonner";
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();
const isSubmitting = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);

const formData = ref({
  phone: "",
  address: "",
  password: "",
});
const selectedFile = ref<File | null>(null);
const previewImage = ref<string | null>(null);

onMounted(() => {
  if (authStore.user) {
    formData.value.phone = authStore.user.phone || "";
    formData.value.address = authStore.user.address || "";
    if (authStore.user.photo) {
      previewImage.value = import.meta.env.VITE_API_URL?.replace('/api', '') + '/' + authStore.user.photo;
    }
  }
});

const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    selectedFile.value = target.files[0];
    previewImage.value = URL.createObjectURL(selectedFile.value);
  }
};

const triggerFileInput = () => {
  fileInput.value?.click();
};

const saveProfile = async () => {
  isSubmitting.value = true;
  try {
    const data = new FormData();
    if (formData.value.phone) data.append("phone", formData.value.phone);
    if (formData.value.address) data.append("address", formData.value.address);
    if (formData.value.password) data.append("password", formData.value.password);
    if (selectedFile.value) {
      data.append("photo", selectedFile.value);
    }

    const response = await api.post("/me/profile", data, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    
    authStore.user = response.data;
    toast.success("Profil berhasil diperbarui");
    formData.value.password = "";
  } catch (error: any) {
    console.error(error);
    toast.error(error.response?.data?.message || "Gagal memperbarui profil");
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<template>
  <div class="min-h-screen flex bg-[#fbfbfb] dark:bg-zinc-950 text-sm transition-colors">
    <AppSidebar />
    
    <main class="flex-1 flex flex-col min-w-0">
      <AppHeader />
      
      <ScrollArea class="flex-1 p-8">
        <AppBreadcrumb />
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-100 flex items-center">
              <User class="w-6 h-6 mr-3 text-indigo-600 dark:text-indigo-400" />
              Profil Karyawan
            </h1>
            <p class="text-gray-500 dark:text-zinc-400 mt-1">
              Kelola informasi pribadi dan keamanan akun Anda.
            </p>
          </div>
        </div>

        <div class="max-w-4xl space-y-6">
          <form @submit.prevent="saveProfile">
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm mb-6 overflow-hidden flex flex-col md:flex-row">
              
              <!-- Photo Section -->
              <div class="p-8 md:w-1/3 border-b md:border-b-0 md:border-r border-gray-100 dark:border-zinc-800 flex flex-col items-center justify-center bg-gray-50/30 dark:bg-zinc-950/30">
                <div class="relative mb-4 group cursor-pointer" @click="triggerFileInput">
                  <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-white dark:border-zinc-800 shadow-lg bg-gray-100 dark:bg-zinc-800 flex items-center justify-center">
                    <img v-if="previewImage" :src="previewImage" class="w-full h-full object-cover" />
                    <User v-else class="w-12 h-12 text-gray-300 dark:text-zinc-600" />
                  </div>
                  <div class="absolute inset-0 bg-black/40 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                    <Camera class="w-8 h-8 text-white" />
                  </div>
                  <input type="file" ref="fileInput" accept="image/*" class="hidden" @change="handleFileChange" />
                </div>
                <h3 class="font-semibold text-gray-900 dark:text-zinc-100 text-lg">{{ authStore.user?.name }}</h3>
                <p class="text-gray-500 dark:text-zinc-400 text-sm">{{ authStore.user?.position?.name || 'Belum ada jabatan' }}</p>
                <div class="mt-4 px-3 py-1 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 text-xs rounded-full font-medium">
                  {{ authStore.user?.division?.name || 'Belum ada divisi' }}
                </div>
              </div>

              <!-- Form Section -->
              <div class="p-8 md:w-2/3 space-y-6">
                <div class="space-y-4">
                  <h3 class="text-base font-semibold text-gray-800 dark:text-zinc-200 border-b border-gray-100 dark:border-zinc-800 pb-2">Informasi Kontak</h3>
                  <div class="grid grid-cols-1 gap-4">
                    <div class="space-y-2">
                      <Label class="text-gray-700 dark:text-gray-300">Nomor Telepon</Label>
                      <div class="relative">
                        <Phone class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                        <Input v-model="formData.phone" placeholder="Contoh: 081234567890" class="pl-9 bg-gray-50 dark:bg-zinc-950 border-gray-200 dark:border-zinc-800" />
                      </div>
                    </div>
                    <div class="space-y-2">
                      <Label class="text-gray-700 dark:text-gray-300">Alamat Lengkap</Label>
                      <div class="relative">
                        <MapPin class="w-4 h-4 absolute left-3 top-3 text-gray-400" />
                        <Textarea v-model="formData.address" placeholder="Masukkan alamat lengkap" class="pl-9 min-h-[80px] bg-gray-50 dark:bg-zinc-950 border-gray-200 dark:border-zinc-800" />
                      </div>
                    </div>
                  </div>
                </div>

                <div class="space-y-4 pt-4">
                  <h3 class="text-base font-semibold text-gray-800 dark:text-zinc-200 border-b border-gray-100 dark:border-zinc-800 pb-2">Keamanan Akun</h3>
                  <div class="space-y-2">
                    <Label class="text-gray-700 dark:text-gray-300">Password Baru</Label>
                    <div class="relative">
                      <Lock class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                      <Input type="password" v-model="formData.password" placeholder="Biarkan kosong jika tidak ingin mengubah" class="pl-9 bg-gray-50 dark:bg-zinc-950 border-gray-200 dark:border-zinc-800" />
                    </div>
                    <p class="text-xs text-gray-500 mt-1">* Minimal 6 karakter.</p>
                  </div>
                </div>

                <div class="pt-6 flex justify-end">
                  <Button type="submit" :disabled="isSubmitting" class="bg-indigo-600 hover:bg-indigo-700 text-white w-full md:w-auto">
                    <Save class="w-4 h-4 mr-2" /> {{ isSubmitting ? 'Menyimpan...' : 'Simpan Perubahan' }}
                  </Button>
                </div>
              </div>

            </div>
          </form>
        </div>

      </ScrollArea>
    </main>
  </div>
</template>
