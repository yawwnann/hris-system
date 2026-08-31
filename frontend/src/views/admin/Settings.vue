<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, nextTick, computed, watch } from "vue";
import { Save, Settings as SettingsIcon, Building, MapPin, Clock, Search, Crosshair } from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { ScrollArea } from "@/components/ui/scroll-area";
import AppSidebar from "@/components/layout/AppSidebar.vue";
import AppHeader from "@/components/layout/AppHeader.vue";
import AppBreadcrumb from "@/components/layout/AppBreadcrumb.vue";
import api from "@/lib/axios";
import { toast } from "vue-sonner";
import L from "leaflet";
import "leaflet/dist/leaflet.css";

const loading = ref(true);
const isSubmitting = ref(false);

const formData = ref({
  company_name: "",
  office_location: "",
  office_lat: "-6.2088",
  office_long: "106.8456",
  attendance_radius: 50,
  default_time_in: "08:00:00",
  default_time_out: "17:00:00",
  timezone: "Asia/Jakarta"
});

const searchQuery = ref("");

// Leaflet map refs
const mapEl = ref<HTMLDivElement | null>(null);
let map: L.Map | null = null;
let marker: L.Marker | null = null;
let circle: L.Circle | null = null;

const mapFullUrl = computed(() => {
  const lat = parseFloat(formData.value.office_lat) || -6.2088;
  const lon = parseFloat(formData.value.office_long) || 106.8456;
  return `https://www.openstreetmap.org/?mlat=${lat}&mlon=${lon}#map=17/${lat}/${lon}`;
});

const getLat = () => parseFloat(formData.value.office_lat) || -6.2088;
const getLng = () => parseFloat(formData.value.office_long) || 106.8456;

const initMap = () => {
  if (!mapEl.value || map) return;
  const lat = getLat();
  const lon = getLng();

  map = L.map(mapEl.value, {
    center: [lat, lon],
    zoom: 16,
    scrollWheelZoom: true,
  });

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors',
  }).addTo(map);

  const icon = L.divIcon({
    className: "",
    html: `<div style="width:18px;height:18px;background:#f97316;border:3px solid #fff;border-radius:50%;box-shadow:0 0 0 4px rgba(249,115,22,0.3);"></div>`,
    iconSize: [18, 18],
    iconAnchor: [9, 9],
  });

  marker = L.marker([lat, lon], { icon, draggable: true }).addTo(map);
  marker.on("dragend", (e: any) => {
    const pos = e.target.getLatLng();
    formData.value.office_lat = pos.lat.toFixed(6);
    formData.value.office_long = pos.lng.toFixed(6);
    if (circle) circle.setLatLng(pos);
  });

  circle = L.circle([lat, lon], {
    radius: formData.value.attendance_radius || 50,
    color: "#f97316",
    fillColor: "#f97316",
    fillOpacity: 0.2,
    weight: 2,
  }).addTo(map);

  setTimeout(() => {
    if (map) map.invalidateSize();
  }, 100);
};

const updateMap = () => {
  if (!map || !marker || !circle) return;
  const lat = getLat();
  const lon = getLng();
  const ll: L.LatLngExpression = [lat, lon];
  marker.setLatLng(ll);
  circle.setLatLng(ll);
  circle.setRadius(formData.value.attendance_radius || 50);
  map.panTo(ll);
};

const searchLocation = async () => {
  if (!searchQuery.value.trim()) return;
  try {
    const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchQuery.value)}`);
    const data = await res.json();
    if (data && data.length > 0) {
      formData.value.office_lat = data[0].lat;
      formData.value.office_long = data[0].lon;
      if (data[0].display_name && !formData.value.office_location) {
        formData.value.office_location = data[0].display_name;
      }
      updateMap();
      toast.success("Location found and coordinates updated!");
    } else {
      toast.error("Location not found on map search.");
    }
  } catch (e) {
    console.error(e);
    toast.error("Failed to search location.");
  }
};

const getCurrentLocation = () => {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        formData.value.office_lat = position.coords.latitude.toFixed(6);
        formData.value.office_long = position.coords.longitude.toFixed(6);
        updateMap();
        toast.success("Current GPS coordinates acquired!");
      },
      (error) => {
        toast.error("Unable to retrieve your location: " + error.message);
      }
    );
  } else {
    toast.error("Geolocation is not supported by your browser");
  }
};

const fetchSettings = async () => {
  loading.value = true;
  try {
    const { data } = await api.get("/settings");
    if (data.id) {
      formData.value = {
        company_name: data.company_name || "",
        office_location: data.office_location || "",
        office_lat: data.office_lat || "-6.2088",
        office_long: data.office_long || "106.8456",
        attendance_radius: data.attendance_radius || 50,
        default_time_in: data.default_time_in || "08:00:00",
        default_time_out: data.default_time_out || "17:00:00",
        timezone: data.timezone || "Asia/Jakarta"
      };
    }
  } catch (error) {
    console.error("Failed to fetch settings", error);
    toast.error("Failed to load settings");
  } finally {
    loading.value = false;
    await nextTick();
    initMap();
  }
};

onMounted(() => {
  fetchSettings();
});

onBeforeUnmount(() => {
  if (map) map.remove();
  map = null;
  marker = null;
  circle = null;
});

watch(
  () => [formData.value.office_lat, formData.value.office_long, formData.value.attendance_radius],
  updateMap
);

const saveSettings = async () => {
  isSubmitting.value = true;
  try {
    const payload = {
      ...formData.value,
      default_time_in: formData.value.default_time_in.length === 5 ? formData.value.default_time_in + ':00' : formData.value.default_time_in,
      default_time_out: formData.value.default_time_out.length === 5 ? formData.value.default_time_out + ':00' : formData.value.default_time_out,
    };
    await api.post("/settings", payload);
    toast.success("Settings saved successfully");
  } catch (error: any) {
    console.error(error);
    toast.error(error.response?.data?.message || "Failed to save settings");
  } finally {
    isSubmitting.value = false;
  }
};
</script>
```vue
<template>

  <div class="min-h-screen flex bg-[#fbfbfb] dark:bg-zinc-950 text-sm transition-colors">

    <AppSidebar />

    <main class="flex-1 flex flex-col min-w-0">

      <AppHeader />

      <ScrollArea class="flex-1 p-8">

        <AppBreadcrumb />

        <!-- Header Halaman -->

        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">

          <div>

            <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-100 flex items-center">

              <SettingsIcon class="w-6 h-6 mr-3 text-orange-600 dark:text-orange-400" />

              Pengaturan Sistem

            </h1>

            <p class="text-gray-500 dark:text-zinc-400 mt-1">

              Konfigurasikan pengaturan utama aplikasi HRIS.

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

                <h2 class="text-base font-semibold text-gray-800 dark:text-zinc-200">
                  Profil Perusahaan
                </h2>

              </div>

              <div class="p-6 space-y-4">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                  <div class="space-y-2">

                    <Label class="text-gray-700 dark:text-gray-300">
                      Nama Perusahaan
                    </Label>

                    <Input
                      v-model="formData.company_name"
                      placeholder="Masukkan nama perusahaan"
                      class="bg-gray-50 dark:bg-zinc-950 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100"
                    />

                  </div>

                  <div class="space-y-2">

                    <Label class="text-gray-700 dark:text-gray-300">
                      Zona Waktu
                    </Label>

                    <Input
                      v-model="formData.timezone"
                      placeholder="Contoh: Asia/Jakarta"
                      class="bg-gray-50 dark:bg-zinc-950 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100"
                    />

                  </div>

                </div>

              </div>

            </div>

            <!-- Lokasi & Presensi -->

            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm mb-6 overflow-hidden">

              <div class="border-b border-gray-100 dark:border-zinc-800 bg-gray-50/50 dark:bg-zinc-950/50 px-6 py-4 flex items-center justify-between">

                <div class="flex items-center">

                  <MapPin class="w-5 h-5 mr-2 text-gray-400 dark:text-zinc-500" />

                  <h2 class="text-base font-semibold text-gray-800 dark:text-zinc-200">
                    Lokasi & Geofencing
                  </h2>

                </div>

                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  @click="getCurrentLocation"
                  class="text-xs"
                >

                  <Crosshair class="w-3.5 h-3.5 mr-1.5" />

                  Gunakan GPS Saya

                </Button>

              </div>

              <div class="p-6 space-y-4">

                <div class="space-y-2">

                  <Label class="text-gray-700 dark:text-gray-300">
                    Cari Lokasi / Alamat
                  </Label>

                  <div class="flex gap-2">

                    <Input
                      v-model="searchQuery"
                      placeholder="Ketik nama lokasi, contoh: Monas Jakarta, lalu tekan cari"
                      @keyup.enter.prevent="searchLocation"
                      class="bg-gray-50 dark:bg-zinc-950 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100 flex-1"
                    />

                    <Button
                      type="button"
                      variant="secondary"
                      @click="searchLocation"
                    >

                      <Search class="w-4 h-4 mr-1" />

                      Cari

                    </Button>

                  </div>

                </div>

                <div class="space-y-2">

                  <Label class="text-gray-700 dark:text-gray-300">
                    Alamat Kantor Pusat
                  </Label>

                  <Input
                    v-model="formData.office_location"
                    placeholder="Masukkan alamat lengkap kantor"
                    class="bg-gray-50 dark:bg-zinc-950 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100"
                  />

                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                  <div class="space-y-2">

                    <Label class="text-gray-700 dark:text-gray-300">
                      Lintang
                    </Label>

                    <Input
                      v-model="formData.office_lat"
                      placeholder="-6.2088"
                      class="bg-gray-50 dark:bg-zinc-950 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100"
                    />

                  </div>

                  <div class="space-y-2">

                    <Label class="text-gray-700 dark:text-gray-300">
                      Bujur
                    </Label>

                    <Input
                      v-model="formData.office_long"
                      placeholder="106.8456"
                      class="bg-gray-50 dark:bg-zinc-950 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100"
                    />

                  </div>

                  <div class="space-y-2">

                    <Label class="text-gray-700 dark:text-gray-300">
                      Radius Presensi (Meter)
                    </Label>

                    <Input
                      type="number"
                      v-model="formData.attendance_radius"
                      class="bg-gray-50 dark:bg-zinc-950 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100"
                    />

                  </div>

                </div>

                <!-- Pratinjau Peta Interaktif -->

                <div class="space-y-2 pt-2">

                  <div class="flex items-center justify-between">

                    <Label class="text-gray-700 dark:text-gray-300">
                      Pratinjau Peta & Radius Geofencing
                      ({{ formData.attendance_radius }} Meter)
                    </Label>

                  </div>

                  <div class="w-full min-h-[300px] h-72 rounded-lg overflow-hidden border border-gray-200 dark:border-zinc-800 bg-gray-100 dark:bg-zinc-950 relative z-0">

                    <div ref="mapEl" class="w-full h-full"></div>

                  </div>

                  <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">

                    * Geser penanda oranye untuk mengubah posisi kantor, atau gunakan pencarian lokasi / GPS di atas.
                    Lingkaran oranye menunjukkan radius geofencing tempat karyawan dapat melakukan presensi masuk dan keluar.

                  </p>

                </div>

              </div>

            </div>

            <!-- Pengaturan Jam Kerja Default -->

            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm mb-6 overflow-hidden">

              <div class="border-b border-gray-100 dark:border-zinc-800 bg-gray-50/50 dark:bg-zinc-950/50 px-6 py-4 flex items-center">

                <Clock class="w-5 h-5 mr-2 text-gray-400 dark:text-zinc-500" />

                <h2 class="text-base font-semibold text-gray-800 dark:text-zinc-200">
                  Jam Kerja Default
                </h2>

              </div>

              <div class="p-6 space-y-4">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                  <div class="space-y-2">

                    <Label class="text-gray-700 dark:text-gray-300">
                      Jam Masuk (Default)
                    </Label>

                    <Input
                      type="time"
                      step="1"
                      v-model="formData.default_time_in"
                      class="bg-gray-50 dark:bg-zinc-950 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100"
                    />

                  </div>

                  <div class="space-y-2">

                    <Label class="text-gray-700 dark:text-gray-300">
                      Jam Keluar (Default)
                    </Label>

                    <Input
                      type="time"
                      step="1"
                      v-model="formData.default_time_out"
                      class="bg-gray-50 dark:bg-zinc-950 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100"
                    />

                  </div>

                </div>

                <p class="text-xs text-gray-500 dark:text-zinc-400 mt-2">

                  * Digunakan apabila karyawan tidak memiliki jadwal kerja khusus.

                </p>

              </div>

            </div>

            <!-- Simpan -->

            <div class="flex justify-end pt-4">

              <Button
                type="submit"
                :disabled="isSubmitting"
                class="bg-orange-600 hover:bg-orange-700 text-white"
              >

                <Save class="w-4 h-4 mr-2" />

                {{ isSubmitting ? 'Menyimpan...' : 'Simpan Pengaturan' }}

              </Button>

            </div>

          </form>

        </div>

      </ScrollArea>

    </main>

  </div>

</template>
```
