
const getRandom = (arr: any[]) => arr[Math.floor(Math.random() * arr.length)];
const positions = ['Manager', 'Staff', 'Senior Dev', 'HR Specialist'];
const departments = ['Engineering', 'HR', 'Marketing', 'Sales'];

export const adminDashboardMock = {
  total_employees: 150,
  present_today: 142,
  absent_today: 5,
  on_leave_today: 3,
  pending_leaves: 12,
  pending_overtimes: 4,
  monthly_attendance_rate: 95,
  recent_activities: Array.from({ length: 8 }).map((_, i) => ({
    id: i + 1,
    text: `User ${i+1} performed an action`,
    time: `0${8 + i}:00 AM`
  })),
  departments: departments.map(name => ({ name, count: Math.floor(Math.random() * 40) + 5 }))
};

export const employeeDashboardMock = {
  today_attendance: {
    clock_in_time: '2026-08-27T08:05:00',
    clock_out_time: null
  },
  leave_quota: 10,
  total_leave_quota: 12,
  leaves_used: 2,
  overtime_this_month: 5,
  pending_overtime_this_month: 2,
  upcoming_events: [
    { id: 1, type: 'meeting', title: 'Daily Standup', description: 'Online', date: '2026-08-27T10:00:00' },
    { id: 2, type: 'other', title: 'Code Review', description: 'Office', date: '2026-08-27T14:00:00' },
    { id: 3, type: 'meeting', title: 'Project Sync', description: 'Room A', date: '2026-08-28T11:00:00' }
  ]
};

export const usersMock = {
  data: [
    { id: 1, name: 'Mock Admin', email: 'admin@mock.com', role: 'admin', nik: 'EMP-001', position: { name: 'Director' }, department: { name: 'Management' } },
    { id: 2, name: 'Mock Employee', email: 'employee@mock.com', role: 'employee', nik: 'EMP-002', position: { name: 'Staff' }, department: { name: 'Engineering' } },
    ...Array.from({ length: 48 }).map((_, i) => ({
      id: i + 3,
      name: `User ${i + 3}`,
      email: `user${i + 3}@mock.com`,
      role: 'employee',
      nik: `EMP-${(i + 3).toString().padStart(3, '0')}`,
      position: { name: getRandom(positions) },
      department: { name: getRandom(departments) }
    }))
  ],
  total: 50,
  last_page: 5
};
