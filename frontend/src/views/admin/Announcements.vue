<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, watch } from "vue";
import {
  Plus,
  Search,
  Edit,
  Trash2,
  Megaphone,
  CalendarDays,
  X,
  AlertTriangle,
} from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { ScrollArea } from "@/components/ui/scroll-area";
import AppSidebar from "@/components/layout/AppSidebar.vue";
import AppHeader from "@/components/layout/AppHeader.vue";
import AppBreadcrumb from "@/components/layout/AppBreadcrumb.vue";
import api from "@/lib/axios";
import { toast } from "vue-sonner";
import { useAuthStore } from "@/stores/auth";
import moment from "moment";

const authStore = useAuthStore();
const announcements = ref<any[]>([]);
const loading = ref(true);
const searchQuery = ref("");
const divisions = ref<any[]>([]);

// Pagination
const currentPage = ref(1);
const itemsPerPage = 9;
const totalPages = ref(1);
const totalItems = ref(0);

// Modal states
const isDialogOpen = ref(false);
const isSubmitting = ref(false);
const editId = ref<number | null>(null);

const isDeleteDialogOpen = ref(false);
const itemToDelete = ref<number | null>(null);
const isDeleting = ref(false);

const formData = ref({
  title: "",
  content: "",
  status: "published",
  publish_date: "",
  category: "Umum",
  division_id: "" as string,
});

const fetchDivisions = async () => {
  try {
    const { data } = await api.get("/divisions", { params: { paginate: false } });
    divisions.value = Array.isArray(data) ? data : data.data || [];
  } catch (error) {
    console.error("Gagal memuat divisi", error);
  }
};

const fetchAnnouncements = async () => {
  loading.value = true;
  try {
    const { data } = await api.get("/announcements", {
      params: {
        search: searchQuery.value,
        page: currentPage.value,
        per_page: itemsPerPage,
      },
    });
    announcements.value = data.data || [];
    totalPages.value = data.last_page || 1;
    totalItems.value = data.total || 0;
  } catch (error) {
    console.error("Gagal memuat data pengumuman", error);
    toast.error("Gagal memuat data pengumuman");
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchAnnouncements();
  if (authStore.user?.role === "admin") {
    fetchDivisions();
  }
  window.addEventListener("keydown", handleGlobalKeydown);
});

onBeforeUnmount(() => {
  window.removeEventListener("keydown", handleGlobalKeydown);
  document.body.style.overflow = "";
});

const handleGlobalKeydown = (e: KeyboardEvent) => {
  if (e.key === "Escape") {
    if (isDialogOpen.value) {
      closeDialog();
    } else if (isDeleteDialogOpen.value) {
      isDeleteDialogOpen.value = false;
    }
  }
};

watch(searchQuery, () => {
  currentPage.value = 1;
  fetchAnnouncements();
});

watch(currentPage, () => {
  fetchAnnouncements();
});

watch(isDialogOpen, (isOpen) => {
  if (isOpen) {
    document.body.style.overflow = "hidden";
  } else if (!isDeleteDialogOpen.value) {
    document.body.style.overflow = "";
  }
});

watch(isDeleteDialogOpen, (isOpen) => {
  if (isOpen) {
    document.body.style.overflow = "hidden";
  } else if (!isDialogOpen.value) {
    document.body.style.overflow = "";
  }
});

const openAddDialog = () => {
  editId.value = null;
  formData.value = {
    title: "",
    content: "",
    status: "published",
    publish_date: moment().format("YYYY-MM-DDTHH:mm"),
    category: "Umum",
    division_id: "",
  };
  isDialogOpen.value = true;
};

const openEditDialog = (item: any) => {
  editId.value = item.id;
  formData.value = {
    title: item.title || "",
    content: item.content || "",
    status: item.status || "published",
    publish_date: item.publish_date
      ? moment(item.publish_date).format("YYYY-MM-DDTHH:mm")
      : "",
    category: item.category || "Umum",
    division_id: item.division_id ? String(item.division_id) : "",
  };
  isDialogOpen.value = true;
};

const closeDialog = () => {
  isDialogOpen.value = false;
};

const submitAnnouncement = async () => {
  const trimmedTitle = formData.value.title.trim();
  const trimmedContent = formData.value.content.trim();

  if (!trimmedTitle || !trimmedContent) {
    toast.error("Harap isi judul dan konten pengumuman");
    return;
  }

  isSubmitting.value = true;
  try {
    const payload = {
      title: trimmedTitle,
      content: trimmedContent,
      status: formData.value.status,
      publish_date: formData.value.publish_date
        ? moment(formData.value.publish_date).format("YYYY-MM-DD HH:mm:ss")
        : null,
      category: formData.value.category || "Umum",
      division_id: formData.value.division_id ? Number(formData.value.division_id) : null,
    };

    if (editId.value) {
      await api.put(`/announcements/${editId.value}`, payload);
      toast.success("Pengumuman berhasil diperbarui");
    } else {
      await api.post("/announcements", payload);
      toast.success("Pengumuman berhasil dibuat");
    }

    closeDialog();
    fetchAnnouncements();
  } catch (error: any) {
    console.error("Submit error:", error);
    toast.error(error.response?.data?.message || "Gagal menyimpan pengumuman");
  } finally {
    isSubmitting.value = false;
  }
};

const confirmDelete = (id: number) => {
  itemToDelete.value = id;
  isDeleteDialogOpen.value = true;
};

const executeDelete = async () => {
  if (!itemToDelete.value) return;
  isDeleting.value = true;
  try {
    await api.delete(`/announcements/${itemToDelete.value}`);
    toast.success("Pengumuman berhasil dihapus");
    isDeleteDialogOpen.value = false;
    itemToDelete.value = null;
    fetchAnnouncements();
  } catch (error: any) {
    toast.error(error.response?.data?.message || "Gagal menghapus pengumuman");
  } finally {
    isDeleting.value = false;
  }
};

const getDivisionName = (divisionId: number | string | null) => {
  if (!divisionId) return null;
  const found = divisions.value.find((d) => String(d.id) === String(divisionId));
  return found ? found.name : null;
};

const formatDate = (dateString: string) =>
  moment(dateString).format("DD MMM YYYY, HH:mm");
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
              <Megaphone class="w-6 h-6 mr-3 text-orange-600 dark:text-orange-400" />
              Pengumuman
            </h1>
            <p class="text-gray-500 dark:text-zinc-400 mt-1">
              {{
                authStore.user?.role === "admin"
                  ? "Kelola informasi dan pengumuman untuk semua karyawan."
                  : "Informasi dan pengumuman terbaru dari perusahaan."
              }}
            </p>
          </div>

          <div class="flex items-center space-x-3">
            <div class="relative w-64 hidden md:block">
              <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500" />
              <input
                type="text"
                v-model="searchQuery"
                placeholder="Cari pengumuman..."
                class="w-full pl-9 pr-4 py-2 rounded-lg bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-zinc-500 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors"
              />
            </div>
            <Button
              v-if="authStore.user?.role === 'admin'"
              @click="openAddDialog"
              class="bg-orange-600 hover:bg-orange-700 text-white font-medium shadow-sm transition-all"
            >
              <Plus class="w-4 h-4 mr-2" /> Buat Pengumuman
            </Button>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center h-64">
          <div class="text-gray-500 dark:text-zinc-400 flex items-center gap-2">
            <div class="w-4 h-4 border-2 border-orange-500 border-t-transparent rounded-full animate-spin"></div>
            Memuat pengumuman...
          </div>
        </div>

        <!-- Empty State -->
        <div
          v-else-if="announcements.length === 0"
          class="flex flex-col items-center justify-center h-64 bg-white dark:bg-zinc-900 rounded-xl border border-dashed border-gray-300 dark:border-zinc-700 p-8 text-center"
        >
          <Megaphone class="w-12 h-12 text-gray-300 dark:text-zinc-600 mb-4" />
          <p class="text-gray-600 dark:text-zinc-300 font-medium">Belum ada pengumuman tersedia</p>
          <p class="text-xs text-gray-400 dark:text-zinc-500 mt-1">
            Pengumuman yang baru dibuat akan muncul di sini.
          </p>
        </div>

        <!-- Grid Container -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="item in announcements"
            :key="item.id"
            class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm hover:shadow-md transition-shadow flex flex-col overflow-hidden"
          >
            <div class="p-6 flex-1 flex flex-col">
              <div class="flex justify-between items-start mb-4">
                <Badge
                  :variant="item.status === 'published' ? 'default' : 'secondary'"
                  :class="
                    item.status === 'published'
                      ? 'bg-orange-50 dark:bg-orange-900/20 text-orange-700 dark:text-orange-400 hover:bg-orange-100'
                      : 'bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-400'
                  "
                >
                  {{ item.status === "published" ? "Dipublikasikan" : "Draf" }}
                </Badge>

                <div v-if="authStore.user?.role === 'admin'" class="flex items-center gap-1 -mr-2">
                  <button
                    type="button"
                    @click="openEditDialog(item)"
                    title="Ubah Pengumuman"
                    class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-orange-600 hover:text-orange-700 hover:bg-orange-50 dark:text-orange-400 dark:hover:bg-orange-900/20 transition-colors"
                  >
                    <Edit class="h-4 w-4" />
                  </button>
                  <button
                    type="button"
                    @click="confirmDelete(item.id)"
                    title="Hapus Pengumuman"
                    class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-red-600 hover:text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20 transition-colors"
                  >
                    <Trash2 class="h-4 w-4" />
                  </button>
                </div>
              </div>

              <div class="flex flex-wrap gap-2 mb-3">
                <Badge
                  variant="outline"
                  class="text-xs bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800"
                >
                  {{ item.category || "Umum" }}
                </Badge>
                <Badge
                  v-if="item.division?.name || getDivisionName(item.division_id)"
                  variant="outline"
                  class="text-xs bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/20 dark:text-purple-400 dark:border-purple-800"
                >
                  {{ item.division?.name || getDivisionName(item.division_id) }}
                </Badge>
              </div>

              <h3 class="text-lg font-semibold text-gray-900 dark:text-zinc-100 mb-2 leading-tight">
                {{ item.title }}
              </h3>

              <p class="text-gray-600 dark:text-zinc-400 line-clamp-4 leading-relaxed whitespace-pre-wrap flex-1">
                {{ item.content }}
              </p>
            </div>

            <div
              class="px-6 py-4 border-t border-gray-100 dark:border-zinc-800 bg-gray-50/50 dark:bg-zinc-950/50 flex items-center text-xs text-gray-500 dark:text-zinc-500 mt-auto"
            >
              <CalendarDays class="w-3.5 h-3.5 mr-1.5 shrink-0" />
              {{ item.publish_date ? formatDate(item.publish_date) : formatDate(item.created_at) }}
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div
          v-if="totalPages > 1"
          class="mt-8 flex items-center justify-between bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 px-6 py-4 rounded-xl shadow-sm"
        >
          <div class="text-sm text-gray-500 dark:text-zinc-400">
            Halaman <span class="font-medium text-gray-900 dark:text-zinc-100">{{ currentPage }}</span> dari
            <span class="font-medium text-gray-900 dark:text-zinc-100">{{ totalPages }}</span>
          </div>
          <div class="flex items-center space-x-2">
            <Button
              variant="outline"
              size="sm"
              @click="currentPage--"
              :disabled="currentPage === 1"
              class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300"
            >
              Previous
            </Button>
            <Button
              variant="outline"
              size="sm"
              @click="currentPage++"
              :disabled="currentPage >= totalPages"
              class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300"
            >
              Next
            </Button>
          </div>
        </div>
      </ScrollArea>
    </main>

    <!-- Modal Buat / Edit Pengumuman -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="isDialogOpen"
          class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
        >
          <!-- Backdrop Overlay -->
          <div
            class="fixed inset-0 transition-opacity"
            style="background-color: rgba(0, 0, 0, 0.7); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);"
            @click="closeDialog"
          />

          <!-- Modal Card -->
          <div
            class="relative w-full rounded-2xl bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 shadow-2xl z-10 overflow-hidden flex flex-col max-h-[90vh]"
            style="max-width: 600px;"
            @click.stop
          >
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-8 pt-8 pb-2">
              <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-zinc-100">
                  {{ editId ? "Edit Pengumuman" : "Buat Pengumuman Baru" }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1.5">
                  Pastikan isi pesan jelas dan mudah dipahami oleh semua karyawan.
                </p>
              </div>
              <button
                type="button"
                @click="closeDialog"
                class="rounded-lg p-2 text-gray-400 hover:text-gray-600 dark:hover:text-zinc-200 hover:bg-gray-100 dark:hover:bg-zinc-800 transition-colors -mr-2 -mt-4"
              >
                <X class="w-5 h-5" />
              </button>
            </div>

            <!-- Modal Form -->
            <form @submit.prevent="submitAnnouncement" class="flex flex-col flex-1 overflow-hidden">
              <div class="px-8 py-6 space-y-6 overflow-y-auto flex-1">
                <!-- Judul Pengumuman -->
                <div class="space-y-2.5">
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Judul Pengumuman <span class="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    v-model="formData.title"
                    placeholder="Contoh: Perubahan Jam Kerja..."
                    required
                    class="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100 placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors"
                  />
                </div>

                <!-- Konten Pengumuman -->
                <div class="space-y-2.5">
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Konten Pengumuman <span class="text-red-500">*</span>
                  </label>
                  <textarea
                    v-model="formData.content"
                    rows="5"
                    placeholder="Tulis detail pengumuman di sini..."
                    required
                    class="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100 placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors resize-none"
                  ></textarea>
                </div>

                <!-- Kategori & Target Divisi -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                  <div style="min-width: 0;" class="space-y-2.5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                      Kategori
                    </label>
                    <select
                      v-model="formData.category"
                      class="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors cursor-pointer"
                    >
                      <option value="Umum">Umum</option>
                      <option value="Rapat">Rapat</option>
                      <option value="Permintaan Divisi">Permintaan Divisi</option>
                    </select>
                  </div>

                  <div style="min-width: 0;" class="space-y-2.5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                      Target Divisi (Opsional)
                    </label>
                    <select
                      v-model="formData.division_id"
                      class="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors cursor-pointer"
                    >
                      <option value="">Semua Divisi (Global)</option>
                      <option
                        v-for="div in divisions"
                        :key="div.id"
                        :value="String(div.id)"
                      >
                        {{ div.name }}
                      </option>
                    </select>
                  </div>
                </div>

                <!-- Status & Tanggal Publikasi -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                  <div style="min-width: 0;" class="space-y-2.5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                      Status
                    </label>
                    <select
                      v-model="formData.status"
                      class="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors cursor-pointer"
                    >
                      <option value="published">Publikasi Sekarang</option>
                      <option value="draft">Simpan sebagai Draf</option>
                    </select>
                  </div>

                  <div style="min-width: 0;" class="space-y-2.5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                      Tanggal Publikasi (Opsional)
                    </label>
                    <input
                      type="datetime-local"
                      v-model="formData.publish_date"
                      class="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors"
                    />
                  </div>
                </div>
              </div>

              <!-- Modal Footer -->
              <div
                class="flex items-center justify-end gap-3 border-t border-gray-100/60 dark:border-zinc-800/60 mt-1"
                style="padding: 24px 32px 32px;"
              >
                <Button
                  type="button"
                  variant="outline"
                  @click="closeDialog"
                  class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300 px-5 py-2.5 h-auto"
                >
                  Batal
                </Button>
                <Button
                  type="submit"
                  :disabled="isSubmitting"
                  class="bg-orange-600 hover:bg-orange-700 text-white font-medium px-6 py-2.5 h-auto shadow-sm"
                >
                  {{ isSubmitting ? "Menyimpan..." : "Simpan Pengumuman" }}
                </Button>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Modal Konfirmasi Hapus -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="isDeleteDialogOpen"
          class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
        >
          <!-- Backdrop Overlay -->
          <div
            class="fixed inset-0 transition-opacity"
            style="background-color: rgba(0, 0, 0, 0.7); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);"
            @click="isDeleteDialogOpen = false"
          />

          <!-- Modal Card -->
          <div
            class="relative w-full rounded-2xl bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 shadow-2xl z-10 p-6 flex flex-col gap-4"
            style="max-width: 440px;"
            @click.stop
          >
            <div class="flex items-start gap-4">
              <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-950/50 flex items-center justify-center shrink-0 text-red-600 dark:text-red-400">
                <AlertTriangle class="w-5 h-5" />
              </div>
              <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-zinc-100">
                  Apakah Anda yakin?
                </h3>
                <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1 leading-relaxed">
                  Pengumuman ini akan dihapus secara permanen dan tidak dapat dikembalikan.
                </p>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
              <Button
                type="button"
                variant="outline"
                @click="isDeleteDialogOpen = false"
                class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300"
              >
                Batal
              </Button>
              <Button
                type="button"
                @click="executeDelete"
                :disabled="isDeleting"
                class="bg-red-600 hover:bg-red-700 text-white font-medium"
              >
                {{ isDeleting ? "Menghapus..." : "Hapus Pengumuman" }}
              </Button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>
