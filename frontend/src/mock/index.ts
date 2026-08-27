import type { AxiosInstance, AxiosRequestConfig, AxiosResponse } from 'axios';
import { adminDashboardMock, employeeDashboardMock, usersMock } from './data/dashboard';
import { mockDivisions, mockPositions, mockShifts, mockAttendance, mockAnnouncements, mockLeaves, mockOvertimes, mockCalendarEvents } from './data/other';



const paginate = (mockSource: any, config: AxiosRequestConfig) => {
  if (!config.params || !config.params.page || !config.params.per_page) {
    return mockSource;
  }
  
  const page = parseInt(config.params.page);
  const perPage = parseInt(config.params.per_page);
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

export const setupMockAdapter = (api: AxiosInstance) => {

  if (import.meta.env.VITE_INTEGRATION_MODE === 'false') {
    console.log('[MOCK MODE] Mock adapter activated');
    
    api.defaults.adapter = async (config: AxiosRequestConfig): Promise<AxiosResponse> => {
      const url = config.url || '';
      const method = config.method?.toLowerCase();
      let data: any = {};
      let status = 200;

      console.log(`[MOCK API] ${method?.toUpperCase()} ${url}`);

      if (url.includes('/dashboard/admin')) {
        data = adminDashboardMock;
      } else if (url.includes('/dashboard/employee')) {
        data = employeeDashboardMock;
      } else if (url.includes('/users')) {
        data = paginate(usersMock, config);
      } else if (url.includes('/me')) {
        const roleMatch = window.location.pathname.startsWith('/employee') ? 'employee' : 'admin';
        data = usersMock.data.find(u => u.role === roleMatch) || usersMock.data[0];
      } else if (url.includes('/divisions')) {
        data = paginate(mockDivisions, config);
      } else if (url.includes('/positions')) {
        data = paginate(mockPositions, config);
      } else if (url.includes('/shifts')) {
        data = paginate(mockShifts, config);
      } else if (url.includes('/attendance/today')) {
        data = { clock_in_time: '08:00', clock_out_time: null, status: 'Present' };
      } else if (url.includes('/attendance')) {
        data = paginate(mockAttendance, config);
      } else if (url.includes('/announcements')) {
        data = paginate(mockAnnouncements, config);
      } else if (url.includes('/leave-requests')) {
        data = paginate(mockLeaves, config);
      } else if (url.includes('/overtime-requests')) {
        data = paginate(mockOvertimes, config);
      } else if (url.includes('/calendar-events')) {
        data = config.params?.page ? paginate(mockCalendarEvents, config) : { data: mockCalendarEvents };
      } else if (url.includes('/settings')) {
        data = { company_name: 'Mock Company', work_start_time: '08:00', work_end_time: '17:00' };
      } else if (url.includes('/reports/export')) {
        data = new Blob(['mock data'], { type: 'text/csv' });
      } else {
        data = { message: 'Mock response fallback' };
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
