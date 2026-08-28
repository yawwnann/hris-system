<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetDescription, SheetFooter } from '@/components/ui/sheet';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from 'vue-sonner';
import api from '@/lib/axios';

const props = defineProps<{
  modelValue: boolean,
  employeeToEdit: any | null
}>();

const emit = defineEmits(['update:modelValue', 'saved']);

const isOpen = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
});

const isEditing = computed(() => !!props.employeeToEdit);

const divisions = ref<any[]>([]);
const positions = ref<any[]>([]);
const shifts = ref<any[]>([]);
const loading = ref(false);

const form = ref({
  name: '',
  email: '',
  password: '',
  nik: '',
  phone: '',
  role: 'employee',
  status: 'active',
  division_id: null as null | string,
  position_id: null as null | string,
  shift_id: null as null | string,
});

const resetForm = () => {
  if (props.employeeToEdit) {
    form.value = {
      name: props.employeeToEdit.name || '',
      email: props.employeeToEdit.email || '',
      password: '',
      nik: props.employeeToEdit.nik || '',
      phone: props.employeeToEdit.phone || '',
      role: props.employeeToEdit.role || 'employee',
      status: props.employeeToEdit.status || 'active',
      division_id: props.employeeToEdit.division_id ? String(props.employeeToEdit.division_id) : null,
      position_id: props.employeeToEdit.position_id ? String(props.employeeToEdit.position_id) : null,
      shift_id: props.employeeToEdit.shift_id ? String(props.employeeToEdit.shift_id) : null,
    };
  } else {
    form.value = {
      name: '',
      email: '',
      password: '',
      nik: '',
      phone: '',
      role: 'employee',
      status: 'active',
      division_id: null,
      position_id: null,
      shift_id: null,
    };
  }
};

watch(() => props.modelValue, (val) => {
  if (val) {
    resetForm();
    if (divisions.value.length === 0) fetchOptions();
  }
});

const fetchOptions = async () => {
  try {
    const [divRes, posRes, shiftRes] = await Promise.all([
      api.get('/divisions', { params: { paginate: false } }),
      api.get('/positions', { params: { paginate: false } }),
      api.get('/shifts', { params: { paginate: false } })
    ]);
    divisions.value = divRes.data;
    positions.value = posRes.data;
    shifts.value = shiftRes.data;
  } catch (error) {
    toast.error('Failed to load division/position options');
  }
};

const saveEmployee = async () => {
  try {
    loading.value = true;
    
    const payload = { ...form.value };
    if (isEditing.value && !payload.password) {
      delete payload.password;
    }
    
    if (isEditing.value) {
      await api.put(`/users/${props.employeeToEdit.id}`, payload);
      toast.success('Successfully updated employee data');
    } else {
      await api.post('/users', payload);
      toast.success('Successfully added new employee');
    }
    
    isOpen.value = false;
    emit('saved');
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'An error occurred while saving data');
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <Sheet v-model:open="isOpen">
    <SheetContent class="sm:max-w-xl w-[90vw] overflow-y-auto bg-white dark:bg-zinc-950 border-l border-gray-200 dark:border-zinc-800">
      <SheetHeader class="mb-6">
        <SheetTitle class="text-gray-900 dark:text-gray-100">{{ isEditing ? 'Ubah Karyawan' : 'Tambah Karyawan Baru' }}</SheetTitle>
        <SheetDescription class="text-gray-500 dark:text-gray-400">
          Silakan isi formulir di bawah ini dengan data karyawan yang benar.
        </SheetDescription>
      </SheetHeader>
      
      <form @submit.prevent="saveEmployee" class="space-y-6 pb-20">
        <div class="space-y-4">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-zinc-300 border-b pb-2 border-gray-100 dark:border-zinc-800">Informasi Pribadi</h3>
          
          <div class="space-y-1">
            <Label class="text-gray-700 dark:text-gray-300">Nama Lengkap</Label>
            <Input v-model="form.name" required placeholder="John Doe" class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-gray-100" />
          </div>
          
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1">
              <Label class="text-gray-700 dark:text-gray-300">Email</Label>
              <Input v-model="form.email" type="email" required placeholder="john@example.com" class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-gray-100" />
            </div>
            <div class="space-y-1">
              <Label class="text-gray-700 dark:text-gray-300">Nomor Induk (NIK)</Label>
              <Input v-model="form.nik" placeholder="EMP-001" class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-gray-100" />
            </div>
          </div>
          
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1">
              <Label class="text-gray-700 dark:text-gray-300">Kata Sandi</Label>
              <Input v-model="form.password" type="password" :required="!isEditing" placeholder="******" class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-gray-100" />
              <p v-if="isEditing" class="text-[10px] text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah kata sandi.</p>
            </div>
            <div class="space-y-1">
              <Label class="text-gray-700 dark:text-gray-300">Nomor Telepon</Label>
              <Input v-model="form.phone" placeholder="+62 8..." class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-gray-100" />
            </div>
          </div>
        </div>
        
        <div class="space-y-4">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-zinc-300 border-b pb-2 border-gray-100 dark:border-zinc-800">Pekerjaan & Status</h3>
          
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1">
              <Label class="text-gray-700 dark:text-gray-300">Divisi</Label>
              <Select v-model="form.division_id">
                <SelectTrigger class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300">
                  <SelectValue placeholder="Pilih Divisi" />
                </SelectTrigger>
                <SelectContent class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800">
                  <SelectItem v-for="div in divisions" :key="div.id" :value="String(div.id)" class="text-gray-700 dark:text-gray-300 focus:bg-gray-100 dark:focus:bg-zinc-800">{{ div.name }}</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-1">
              <Label class="text-gray-700 dark:text-gray-300">Posisi/Jabatan</Label>
              <Select v-model="form.position_id">
                <SelectTrigger class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300">
                  <SelectValue placeholder="Pilih Posisi" />
                </SelectTrigger>
                <SelectContent class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800">
                  <SelectItem v-for="pos in positions" :key="pos.id" :value="String(pos.id)" class="text-gray-700 dark:text-gray-300 focus:bg-gray-100 dark:focus:bg-zinc-800">{{ pos.name }}</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
          
          <div class="grid grid-cols-2 gap-4">
             <div class="space-y-1">
              <Label class="text-gray-700 dark:text-gray-300">Shift Kerja</Label>
              <Select v-model="form.shift_id">
                <SelectTrigger class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300">
                  <SelectValue placeholder="Pilih Shift" />
                </SelectTrigger>
                <SelectContent class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800">
                  <SelectItem v-for="shift in shifts" :key="shift.id" :value="String(shift.id)" class="text-gray-700 dark:text-gray-300 focus:bg-gray-100 dark:focus:bg-zinc-800">{{ shift.name }} ({{ shift.start_time }} - {{ shift.end_time }})</SelectItem>
                </SelectContent>
              </Select>
            </div>
             <div class="space-y-1">
              <Label class="text-gray-700 dark:text-gray-300">Hak Akses Sistem</Label>
              <Select v-model="form.role">
                <SelectTrigger class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300">
                  <SelectValue placeholder="Pilih Hak Akses" />
                </SelectTrigger>
                <SelectContent class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800">
                  <SelectItem value="employee" class="text-gray-700 dark:text-gray-300 focus:bg-gray-100 dark:focus:bg-zinc-800">Karyawan</SelectItem>
                  <SelectItem value="admin" class="text-gray-700 dark:text-gray-300 focus:bg-gray-100 dark:focus:bg-zinc-800">Super Admin</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
          
          <div class="space-y-1">
            <Label class="text-gray-700 dark:text-gray-300">Status Keaktifan</Label>
            <Select v-model="form.status">
              <SelectTrigger class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300">
                <SelectValue placeholder="Pilih Status" />
              </SelectTrigger>
              <SelectContent class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800">
                <SelectItem value="active" class="text-green-600 dark:text-green-400 focus:bg-green-50 dark:focus:bg-green-900/20">Aktif Bekerja</SelectItem>
                <SelectItem value="inactive" class="text-red-600 dark:text-red-400 focus:bg-red-50 dark:focus:bg-red-900/20">Tidak Aktif / Keluar</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <SheetFooter class="absolute bottom-0 left-0 right-0 p-4 bg-white dark:bg-zinc-950 border-t border-gray-200 dark:border-zinc-800">
          <div class="flex justify-end space-x-2 w-full">
            <Button type="button" variant="outline" @click="isOpen = false" class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300">Batal</Button>
            <Button type="submit" :disabled="loading" class="bg-orange-600 hover:bg-orange-700 text-white min-w-[120px]">
              {{ loading ? 'Menyimpan...' : 'Simpan Karyawan' }}
            </Button>
          </div>
        </SheetFooter>
      </form>
    </SheetContent>
  </Sheet>
</template>
