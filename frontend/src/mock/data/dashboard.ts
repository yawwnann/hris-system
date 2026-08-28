
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
  departments: departments.map(name => ({ name, count: Math.floor(Math.random() * 40) + 5 })),
  attendance_chart: (() => {
    const days = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
    return Array.from({ length: 7 }).map((_, i) => {
      const d = new Date();
      d.setDate(d.getDate() - (6 - i));
      const on_leave = Math.floor(Math.random() * 5);
      const late = Math.floor(Math.random() * 10);
      const absent = Math.floor(Math.random() * 8);
      const present = 150 - late - absent - on_leave;
      return {
        date: d.toISOString().split('T')[0],
        day: days[d.getDay() === 0 ? 6 : d.getDay() - 1],
        present,
        late,
        absent,
        on_leave
      };
    });
  })(),
  pending_approvals: [
    { id: 1, user: { name: 'Budi Santoso' }, type: 'Leave', reason: 'Cuti tahunan', date: new Date().toISOString().split('T')[0] },
    { id: 2, user: { name: 'Siti Aminah' }, type: 'Overtime', reason: 'Deadline proyek', date: new Date().toISOString().split('T')[0] },
    { id: 3, user: { name: 'Andi Wijaya' }, type: 'Leave', reason: 'Urusan keluarga', date: new Date(Date.now() - 86400000).toISOString().split('T')[0] }
  ]
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
  ],
  recent_attendances: Array.from({ length: 5 }).map((_, i) => ({
    id: i + 1,
    date: new Date(Date.now() - (i + 1) * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
    clock_in_time: '08:00',
    clock_out_time: '17:00',
    status: Math.random() > 0.1 ? 'present' : 'late'
  })),
  announcements: [
    { id: 1, title: 'Maintenance Server', content: 'Server HRIS akan mengalami downtime pada hari Sabtu pukul 00:00 - 04:00.', publish_date: new Date().toISOString() },
    { id: 2, title: 'Libur Nasional', content: 'Diingatkan kembali bahwa besok adalah hari libur nasional.', publish_date: new Date(Date.now() - 86400000).toISOString() }
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
