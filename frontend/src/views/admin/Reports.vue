<script setup lang="ts">
import { ref } from "vue";
import { Download, FileBarChart } from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { ScrollArea } from "@/components/ui/scroll-area";
import AppSidebar from "@/components/layout/AppSidebar.vue";
import AppHeader from "@/components/layout/AppHeader.vue";
import AppBreadcrumb from "@/components/layout/AppBreadcrumb.vue";
import api from "@/lib/axios";
import { toast } from "vue-sonner";
import moment from "moment";

const isExporting = ref(false);

const formData = ref({
  type: "attendance",
  start_date: moment().startOf('month').format('YYYY-MM-DD'),
  end_date: moment().endOf('month').format('YYYY-MM-DD'),
});

const exportReport = async () => {
  isExporting.value = true;
  try {
    const response = await api.get("/reports/export", {
      params: formData.value,
      responseType: 'blob'
    });
    
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `${formData.value.type}_report_${formData.value.start_date}_to_${formData.value.end_date}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    toast.success("Report downloaded successfully");
  } catch (error) {
    console.error("Export error", error);
    toast.error("Failed to export report");
  } finally {
    isExporting.value = false;
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
              <FileBarChart class="w-6 h-6 mr-3 text-indigo-600 dark:text-indigo-400" />
              Reports & Export
            </h1>
            <p class="text-gray-500 dark:text-zinc-400 mt-1">
              Download recap of employee attendance, leave, and overtime data.
            </p>
          </div>
        </div>

        <div class="max-w-2xl">
          <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
            <div class="border-b border-gray-100 dark:border-zinc-800 bg-gray-50/50 dark:bg-zinc-950/50 px-6 py-4 flex items-center">
              <Download class="w-5 h-5 mr-2 text-gray-400 dark:text-zinc-500" />
              <h2 class="text-base font-semibold text-gray-800 dark:text-zinc-200">Export to CSV</h2>
            </div>
            
            <form @submit.prevent="exportReport" class="p-6 space-y-6">
              <div class="space-y-2">
                <Label class="text-gray-700 dark:text-gray-300">Report Type</Label>
                <Select v-model="formData.type">
                  <SelectTrigger class="bg-gray-50 dark:bg-zinc-950 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100">
                    <SelectValue placeholder="Select Report" />
                  </SelectTrigger>
                  <SelectContent class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800">
                    <SelectGroup>
                      <SelectItem value="attendance">Attendance Report</SelectItem>
                      <SelectItem value="leaves">Leave & Permission Report</SelectItem>
                      <SelectItem value="overtime">Overtime Report</SelectItem>
                    </SelectGroup>
                  </SelectContent>
                </Select>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <Label class="text-gray-700 dark:text-gray-300">From Date</Label>
                  <Input type="date" v-model="formData.start_date" required class="bg-gray-50 dark:bg-zinc-950 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100" />
                </div>
                <div class="space-y-2">
                  <Label class="text-gray-700 dark:text-gray-300">To Date</Label>
                  <Input type="date" v-model="formData.end_date" required class="bg-gray-50 dark:bg-zinc-950 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100" />
                </div>
              </div>

              <div class="pt-4 border-t border-gray-100 dark:border-zinc-800 flex justify-end">
                <Button type="submit" :disabled="isExporting" class="bg-indigo-600 hover:bg-indigo-700 text-white w-full md:w-auto">
                  <Download class="w-4 h-4 mr-2" /> {{ isExporting ? 'Processing...' : 'Download CSV' }}
                </Button>
              </div>
            </form>
          </div>
        </div>

      </ScrollArea>
    </main>
  </div>
</template>
