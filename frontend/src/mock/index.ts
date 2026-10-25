import type { AxiosInstance, AxiosRequestConfig, AxiosResponse } from 'axios';
import { adminDashboardMock, employeeDashboardMock, usersMock } from './data/dashboard';
import { 
  mockDivisions, 
  mockPositions, 
  mockShifts, 
  mockAttendance, 
  mockAnnouncements, 
  mockLeaves, 
  mockOvertimes, 
  mockCalendarEvents 
} from './data/other';

const paginate = (mockSource: any, config: AxiosRequestConfig) => {
  // If explicitly paginate=false, return unwrapped array
  if (config.params?.paginate === false || config.params?.paginate === 'false') {
    if (Array.isArray(mockSource)) return mockSource;
    if (mockSource?.data && Array.isArray(mockSource.data)) return mockSource.data;
    return mockSource;
  }

  if (!config.params || !config.params.page || !config.params.per_page) {
    return mockSource;
  }
  
  const page = parseInt(config.params.page) || 1;
  const perPage = parseInt(config.params.per_page) || 10;
  const start = (page - 1) * perPage;
  const end = start + perPage;
  
  if (Array.isArray(mockSource)) {
    const paginated = mockSource.slice(start, end);
    return { data: paginated, total: mockSource.length, last_page: Math.ceil(mockSource.length / perPage) };
  }
  
  if (mockSource.data && Array.isArray(mockSource.data)) {
    const paginatedData = mockSource.data.slice(start, end);
    return { ...mockSource, data: paginatedData, last_page: Math.ceil(mockSource.data.length / perPage) };
  }
  
  return mockSource;
};

// In-memory mock documents store for employee detail
const mockDocuments: Record<number, any[]> = {
  1: [
    { id: 1, type: 'ktp', file_path: 'mock/ktp_admin.pdf', original_name: 'KTP_Admin.pdf', file_size: 1048576, created_at: '2024-01-10' },
    { id: 2, type: 'contract', file_path: 'mock/sk_direktur.pdf', original_name: 'SK_Pengangkatan_Direktur.pdf', file_size: 2097152, created_at: '2024-01-10' }
  ],
  2: [
    { id: 3, type: 'ktp', file_path: 'mock/ktp_employee.pdf', original_name: 'KTP_Karyawan.pdf', file_size: 1024000, created_at: '2024-02-15' },
    { id: 4, type: 'contract', file_path: 'mock/kontrak_kerja.pdf', original_name: 'Kontrak_Kerja_2024.pdf', file_size: 1536000, created_at: '2024-02-15' },
    { id: 5, type: 'ijazah', file_path: 'mock/ijazah_s1.pdf', original_name: 'Ijazah_S1.pdf', file_size: 3145728, created_at: '2024-02-15' }
  ]
};

export const setupMockAdapter = (api: AxiosInstance) => {
  if (import.meta.env.VITE_INTEGRATION_MODE === 'false') {
    console.log('[MOCK MODE] Mock adapter activated');
    
    api.defaults.adapter = async (config: AxiosRequestConfig): Promise<AxiosResponse> => {
      const url = config.url || '';
      const method = config.method?.toLowerCase() || 'get';
      let data: any = {};
      let status = 200;

      console.log(`[MOCK API] ${method.toUpperCase()} ${url}`);

      // 1. Auth & Session Endpoints
      if (url.includes('/login') && method === 'post') {
        const payload = JSON.parse(config.data || '{}');
        const role = payload.email?.includes('employee') ? 'employee' : 'admin';
        const user = usersMock.data.find(u => u.role === role) || usersMock.data[0];
        
        return {
          data: {
            access_token: 'mock-token-' + role,
            user: user
          },
          status: 200,
          statusText: 'OK',
          headers: {},
          config,
          request: {}
        } as AxiosResponse;
      }

      if (url.includes('/register') && method === 'post') {
        const payload = JSON.parse(config.data || '{}');
        return {
          data: {
            message: 'Registrasi berhasil',
            access_token: 'mock-token-employee',
            user: {
              id: 999,
              name: payload.name || 'Pengguna Baru',
              email: payload.email || 'user@lazizmu.org',
              role: 'employee',
              position: { id: 2, name: 'Staf Pelaksana' },
              division: { id: 1, name: 'Penyaluran & Pendayagunaan' }
            }
          },
          status: 201,
          statusText: 'Created',
          headers: {},
          config,
          request: {}
        } as AxiosResponse;
      }

      if (url.includes('/logout') && method === 'post') {
        return {
          data: { message: 'Berhasil keluar' },
          status: 200,
          statusText: 'OK',
          headers: {},
          config,
          request: {}
        } as AxiosResponse;
      }

      if (url.includes('/forgot-password/request-otp') && method === 'post') {
        return {
          data: { message: 'Jika email terdaftar, kode OTP telah dikirimkan.' },
          status: 200,
          statusText: 'OK',
          headers: {},
          config,
          request: {}
        } as AxiosResponse;
      }

      if (url.includes('/forgot-password/verify-otp') && method === 'post') {
        return {
          data: { message: 'OTP berhasil diverifikasi.', reset_token: 'mock-reset-token-123' },
          status: 200,
          statusText: 'OK',
          headers: {},
          config,
          request: {}
        } as AxiosResponse;
      }

      if (url.includes('/forgot-password/reset') && method === 'post') {
        return {
          data: { message: 'Kata sandi berhasil diperbarui.' },
          status: 200,
          statusText: 'OK',
          headers: {},
          config,
          request: {}
        } as AxiosResponse;
      }

      if (url.includes('/me')) {
        const token = localStorage.getItem('access_token') || '';
        const roleMatch = token.includes('employee') || window.location.pathname.startsWith('/employee')
          ? 'employee'
          : 'admin';
        data = usersMock.data.find(u => u.role === roleMatch) || usersMock.data[0];
      }

      // 2. Dashboards
      else if (url.includes('/dashboard/admin')) {
        data = adminDashboardMock;
      } else if (url.includes('/dashboard/employee')) {
        data = employeeDashboardMock;
      }

      // 3. Employee Documents
      else if (url.match(/\/users\/\d+\/documents/)) {
        const empId = parseInt(url.split('/users/')[1].split('/documents')[0]) || 1;
        if (method === 'post') {
          data = {
            message: 'Dokumen berhasil diunggah',
            document: {
              id: Date.now(),
              type: 'other',
              file_path: 'mock/dokumen_baru.pdf',
              original_name: 'Dokumen_Upload.pdf',
              file_size: 1048576,
              created_at: new Date().toISOString()
            }
          };
        } else {
          data = mockDocuments[empId] || [
            { id: 101, type: 'ktp', file_path: 'mock/ktp.pdf', original_name: 'Scan_KTP.pdf', file_size: 1048576, created_at: '2024-01-15' },
            { id: 102, type: 'contract', file_path: 'mock/kontrak.pdf', original_name: 'Perjanjian_Kerja.pdf', file_size: 2097152, created_at: '2024-01-15' }
          ];
        }
      } else if (url.match(/\/employee-documents\/\d+/)) {
        data = { message: 'Dokumen berhasil dihapus' };
      }

      // 4. Employee Detail & User CRUD
      else if (url.match(/\/users\/\d+$/) && method === 'get') {
        const empId = parseInt(url.split('/users/')[1].split('?')[0]) || 1;
        const user = usersMock.data.find(u => u.id === empId) || usersMock.data[0];
        data = {
          user: {
            ...user,
            phone: user.phone || '081234567890',
            address: 'Gedung Pusat Dakwah Muhammadiyah, Jl. Menteng Raya No. 62, Jakarta Pusat',
            join_date: user.join_date || '2023-01-15',
            employment_status: user.employment_status || 'Tetap',
            division: user.division || { id: 1, name: 'Penyaluran & Pendayagunaan' },
            position: user.position || { id: 1, name: 'Staf Pelaksana' }
          },
          stats: {
            present_count: 22,
            late_count: 2,
            absent_count: 0,
            leave_count: 1
          },
          recent_attendances: [
            { id: 1, date: new Date().toISOString().split('T')[0], time_in: '07:55:00', time_out: '17:05:00', total_hours: 9, status: 'present' },
            { id: 2, date: new Date(Date.now() - 86400000).toISOString().split('T')[0], time_in: '08:12:00', time_out: '17:00:00', total_hours: 8.8, status: 'late' },
            { id: 3, date: new Date(Date.now() - 172800000).toISOString().split('T')[0], time_in: '07:50:00', time_out: '17:00:00', total_hours: 9.1, status: 'present' }
          ]
        };
      } else if (url.match(/\/users(\/\d+)?/) && (method === 'post' || method === 'put' || method === 'delete')) {
        data = { message: method === 'delete' ? 'Karyawan berhasil dihapus' : 'Data karyawan berhasil disimpan' };
      } else if (url.includes('/users')) {
        data = paginate(usersMock, config);
      }

      // 5. Divisions / Departments
      else if (url.includes('/divisions') && (method === 'post' || method === 'put' || method === 'delete')) {
        data = { message: method === 'delete' ? 'Bidang berhasil dihapus' : 'Bidang berhasil disimpan' };
      } else if (url.includes('/divisions')) {
        data = paginate(mockDivisions, config);
      }

      // 6. Positions & Shifts
      else if (url.includes('/positions')) {
        data = paginate(mockPositions, config);
      } else if (url.includes('/shifts')) {
        data = paginate(mockShifts, config);
      }

      // 7. Attendance
      else if (url.includes('/attendance/today')) {
        data = { 
          clock_in_time: '08:00:00', 
          clock_out_time: null, 
          time_in: '08:00:00', 
          time_out: null, 
          status: 'present' 
        };
      } else if (url.includes('/attendance/check-in') && method === 'post') {
        data = { message: 'Presensi masuk berhasil dicatat!', time_in: '08:00:00' };
      } else if (url.includes('/attendance/check-out') && method === 'post') {
        data = { message: 'Presensi pulang berhasil dicatat!', time_out: '17:00:00' };
      } else if (url.includes('/attendance')) {
        data = paginate(mockAttendance, config);
      }

      // 8. Leave Requests
      else if (url.includes('/leave-requests') && (method === 'post' || method === 'put' || method === 'delete')) {
        data = { 
          message: method === 'delete' 
            ? 'Pengajuan cuti berhasil dihapus' 
            : method === 'put' 
              ? 'Status cuti berhasil diperbarui' 
              : 'Pengajuan cuti berhasil diajukan' 
        };
      } else if (url.includes('/leave-requests')) {
        data = paginate(mockLeaves, config);
      }

      // 9. Overtime Requests
      else if (url.includes('/overtime-requests') && (method === 'post' || method === 'put' || method === 'delete')) {
        const isOutput = url.includes('/output');
        data = { 
          message: method === 'delete' 
            ? 'Pengajuan lembur berhasil dihapus' 
            : isOutput 
              ? 'Output lembur berhasil disimpan' 
              : method === 'put' 
                ? 'Status lembur berhasil diperbarui' 
                : 'Pengajuan lembur berhasil diajukan' 
        };
      } else if (url.includes('/overtime-requests')) {
        data = paginate(mockOvertimes, config);
      }

      // 10. Calendar Events
      else if (url.includes('/calendar-events') && (method === 'post' || method === 'put' || method === 'delete')) {
        data = { message: method === 'delete' ? 'Agenda kalender berhasil dihapus' : 'Agenda kalender berhasil disimpan' };
      } else if (url.includes('/calendar-events')) {
        data = config.params?.page ? paginate(mockCalendarEvents, config) : { data: mockCalendarEvents };
      }

      // 11. Announcements
      else if (url.includes('/announcements') && (method === 'post' || method === 'put' || method === 'delete')) {
        data = { message: method === 'delete' ? 'Pengumuman berhasil dihapus' : 'Pengumuman berhasil disimpan' };
      } else if (url.includes('/announcements')) {
        data = paginate(mockAnnouncements, config);
      }

      // 12. Settings
      else if (url.includes('/settings') && method === 'post') {
        data = { message: 'Pengaturan kantor berhasil disimpan' };
      } else if (url.includes('/settings')) {
        data = {
          company_name: 'Lazizmu Pusat',
          office_location: 'Gedung Pusat Dakwah Muhammadiyah, Jl. Menteng Raya No. 62, Jakarta Pusat',
          office_lat: '-6.191200',
          office_long: '106.840200',
          attendance_radius: 50,
          default_time_in: '08:00:00',
          default_time_out: '17:00:00',
          timezone: 'Asia/Jakarta'
        };
      }

      // 13. Reports Export
      else if (url.includes('/reports/export')) {
        data = new Blob(['mock data export lazizmu'], { type: 'text/csv' });
      }

      // 14. Fallback
      else {
        data = { message: 'Operasi berhasil (Mock response fallback)' };
      }

      return {
        data,
        status,
        statusText: 'OK',
        headers: {},
        config,
        request: {}
      } as AxiosResponse;
    };
  }
};
