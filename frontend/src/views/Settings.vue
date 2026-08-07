<script setup lang="ts">
import { ref, onMounted } from "vue";
import { Save, Settings as SettingsIcon, Building, MapPin, Clock } from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { ScrollArea } from "@/components/ui/scroll-area";
import AppSidebar from "@/components/layout/AppSidebar.vue";
import AppHeader from "@/components/layout/AppHeader.vue";
import AppBreadcrumb from "@/components/layout/AppBreadcrumb.vue";
import api from "@/lib/axios";
import { toast } from "vue-sonner";

const loading = ref(true);
const isSubmitting = ref(false);

const formData = ref({
  company_name: "",
  office_location: "",
  office_lat: "",
  office_long: "",
  attendance_radius: 50,
  default_time_in: "08:00:00",
  default_time_out: "17:00:00",
  timezone: "Asia/Jakarta"
});

const fetchSettings = async () => {
  loading.value = true;
  try {
    const { data } = await api.get("/settings");
    if (data.id) {
      formData.value = {
        company_name: data.company_name || "",
        office_location: data.office_location || "",
        office_lat: data.office_lat || "",
        office_long: data.office_long || "",
        attendance_radius: data.attendance_radius || 50,
        default_time_in: data.default_time_in || "08:00:00",
        default_time_out: data.default_time_out || "17:00:00",
        timezone: data.timezone || "Asia/Jakarta"
      };
    }
  } catch (error) {
    console.error("Failed to fetch settings", error);
    toast.error("Gagal memuat pengaturan");
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchSettings();
});

const saveSettings = async () => {
  isSubmitting.value = true;
  try {
    const payload = {
      ...formData.value,
      default_time_in: formData.value.default_time_in.length === 5 ? formData.value.default_time_in + ':00' : formData.value.default_time_in,
      default_time_out: formData.value.default_time_out.length === 5 ? formData.value.default_time_out + ':00' : formData.value.default_time_out,
    };
    await api.post("/settings", payload);
    toast.success("Pengaturan berhasil disimpan");
  } catch (error: any) {
    console.error(error);
    toast.error(error.response?.data?.message || "Gagal menyimpan pengaturan");
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
              <SettingsIcon class="w-6 h-6 mr-3 text-indigo-600 dark:text-indigo-400" />
              Pengaturan Sistem
            </h1>
            <p class="text-gray-500 dark:text-zinc-400 mt-1">
              Konfigurasi pengaturan utama aplikasi HRIS.
            </p>
          </div>
        </div>

        <div v-if="loading" class="flex justify-center items-center h-64">
           <div class="text-gray-500 dark:text-zinc-400">Memuat pengaturan...</div>
        </div>

        <div v-else class="max-w-4xl space-y-6">
          <form @submit.prevent="saveSettings">
            <!-- Profil Perusahaan -->
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm mb-6 overflow-hidden">
              <div class="border-b border-gray-100 dark:border-zinc-800 bg-gray-50/50 dark:bg-zinc-950/50 px-6 py-4 flex items-center">
                <Building class="w-5 h-5 mr-2 text-gray-400 dark:text-zinc-500" />
                <h2 class="text-base font-semibold text-gray-800 dark:text-zinc-200">Profil Perusahaan</h2>
              </div>
              <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div class="space-y-2">
                    <Label class="text-gray-700 dark:text-gray-300">Nama Perusahaan</Label>
                    <Input v-model="formData.company_name" placeholder="Masukkan nama perusahaan" class="bg-gray-50 dark:bg-zinc-950 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100" />
                  </div>
                  <div class="space-y-2">
                    <Label class="text-gray-700 dark:text-gray-300">Zona Waktu</Label>
                    <Input v-model="formData.timezone" placeholder="e.g. Asia/Jakarta" class="bg-gray-50 dark:bg-zinc-950 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100" />
                  </div>
                </div>
              </div>
            </div>

            <!-- Lokasi & Presensi -->
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm mb-6 overflow-hidden">
              <div class="border-b border-gray-100 dark:border-zinc-800 bg-gray-50/50 dark:bg-zinc-950/50 px-6 py-4 flex items-center">
                <MapPin class="w-5 h-5 mr-2 text-gray-400 dark:text-zinc-500" />
                <h2 class="text-base font-semibold text-gray-800 dark:text-zinc-200">Lokasi & Geofencing</h2>
              </div>
              <div class="p-6 space-y-4">
                <div class="space-y-2">
                  <Label class="text-gray-700 dark:text-gray-300">Alamat Kantor Pusat</Label>
                  <Input v-model="formData.office_location" placeholder="Masukkan alamat lengkap kantor" class="bg-gray-50 dark:bg-zinc-950 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100" />
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                  <div class="space-y-2">
                    <Label class="text-gray-700 dark:text-gray-300">Latitude</Label>
                    <Input v-model="formData.office_lat" placeholder="-6.2088" class="bg-gray-50 dark:bg-zinc-950 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100" />
                  </div>
                  <div class="space-y-2">
                    <Label class="text-gray-700 dark:text-gray-300">Longitude</Label>
                    <Input v-model="formData.office_long" placeholder="106.8456" class="bg-gray-50 dark:bg-zinc-950 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100" />
                  </div>
                  <div class="space-y-2">
                    <Label class="text-gray-700 dark:text-gray-300">Radius Absen (Meter)</Label>
                    <Input type="number" v-model="formData.attendance_radius" class="bg-gray-50 dark:bg-zinc-950 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100" />
                  </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-zinc-400 mt-2">
                  * Karyawan hanya bisa melakukan absen jika berada dalam radius ini dari koordinat kantor.
                </p>
              </div>
            </div>

            <!-- Pengaturan Jam Default -->
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm mb-6 overflow-hidden">
              <div class="border-b border-gray-100 dark:border-zinc-800 bg-gray-50/50 dark:bg-zinc-950/50 px-6 py-4 flex items-center">
                <Clock class="w-5 h-5 mr-2 text-gray-400 dark:text-zinc-500" />
                <h2 class="text-base font-semibold text-gray-800 dark:text-zinc-200">Jam Kerja Default</h2>
              </div>
              <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div class="space-y-2">
                    <Label class="text-gray-700 dark:text-gray-300">Jam Masuk (Default)</Label>
                    <Input type="time" step="1" v-model="formData.default_time_in" class="bg-gray-50 dark:bg-zinc-950 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100" />
                  </div>
                  <div class="space-y-2">
                    <Label class="text-gray-700 dark:text-gray-300">Jam Keluar (Default)</Label>
                    <Input type="time" step="1" v-model="formData.default_time_out" class="bg-gray-50 dark:bg-zinc-950 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100" />
                  </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-zinc-400 mt-2">
                  * Digunakan jika karyawan belum memiliki shift khusus.
                </p>
              </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end pt-4">
              <Button type="submit" :disabled="isSubmitting" class="bg-indigo-600 hover:bg-indigo-700 text-white">
                <Save class="w-4 h-4 mr-2" /> {{ isSubmitting ? 'Menyimpan...' : 'Simpan Pengaturan' }}
              </Button>
            </div>
          </form>
        </div>
      </ScrollArea>
    </main>
  </div>
</template>
