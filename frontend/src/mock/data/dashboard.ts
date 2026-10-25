const getRandom = (arr: any[]) => arr[Math.floor(Math.random() * arr.length)];
const positions = [
  'Direktur Eksekutif',
  'Manajer Program',
  'Manajer Penghimpunan',
  'Staf Penyaluran',
  'Staf Keuangan & Akuntansi',
  'Staf SDM & Legal',
  'Staf IT & Digitalisasi',
  'Staf Media & Komunikasi'
];
const departments = [
  'Penyaluran & Pendayagunaan',
  'Penghimpunan & Kemitraan',
  'Keuangan & Operasional',
  'SDM & Umum',
  'Media & Digital'
];

export const adminDashboardMock = {
  total_employees: 150,
  present_today: 142,
  late_today: 8,
  absent_today: 5,
  on_leave_today: 3,
  pending_requests: 16,
  pending_leaves: 12,
  pending_overtimes: 4,
  monthly_attendance_rate: 95,
  recent_activities: Array.from({ length: 8 }).map((_, i) => ({
    id: i + 1,
    text: `Pengguna ${i + 1} melakukan absensi`,
    time: `0${8 + (i % 2)}:${(10 * i) % 60 < 10 ? '0' : ''}${(10 * i) % 60} WIB`
  })),
  departments: departments.map(name => ({ name, count: Math.floor(Math.random() * 25) + 15 })),
  attendance_chart: (() => {
    const days = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
    return Array.from({ length: 7 }).map((_, i) => {
      const d = new Date();
      d.setDate(d.getDate() - (6 - i));
      const on_leave = Math.floor(Math.random() * 4) + 1;
      const late = Math.floor(Math.random() * 8) + 2;
      const absent = Math.floor(Math.random() * 5) + 1;
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
    { id: 1, user: { name: 'Budi Santoso' }, type: 'Leave', reason: 'Cuti tahunan keperluan keluarga', date: new Date().toISOString().split('T')[0] },
    { id: 2, user: { name: 'Siti Aminah' }, type: 'Overtime', reason: 'Penyusunan Laporan Penyaluran Q3', date: new Date().toISOString().split('T')[0] },
    { id: 3, user: { name: 'Andi Wijaya' }, type: 'Leave', reason: 'Izin urusan keluarga mendesak', date: new Date(Date.now() - 86400000).toISOString().split('T')[0] }
  ],
  division_stats: [
    { division: { name: 'Penyaluran & Pendayagunaan' }, count: 45 },
    { division: { name: 'Penghimpunan & Kemitraan' }, count: 35 },
    { division: { name: 'Keuangan & Operasional' }, count: 25 },
    { division: { name: 'SDM & Umum' }, count: 25 },
    { division: { name: 'Media & Digital' }, count: 20 }
  ],
  upcoming_events: [
    {
      id: 1,
      type: 'MEETING',
      title: 'Rapat Pleno Lazizmu',
      description: 'Rapat Koordinasi Mingguan',
      date: '2026-10-26T10:00:00',
      total_participants: 6,
      divisions: [{ name: 'IT' }, { name: 'HR' }],
      users: [{ name: 'Budi Santoso' }, { name: 'Siti Aminah' }]
    },
    {
      id: 2,
      type: 'TRAINING',
      title: 'Pelatihan ZISWAF',
      description: 'Pelatihan Pengelolaan ZIS',
      date: '2026-10-26T13:30:00',
      total_participants: 4,
      divisions: [{ name: 'Finance' }]
    }
  ],
  away_today: [
    { id: 1, user: { name: 'Ahmad Fauzi' }, type: 'Cuti Tahunan', is_sick: false },
    { id: 2, user: { name: 'Dewi Sartika' }, type: 'Sakit', is_sick: true }
  ],
  celebrations: [
    { id: 1, user: { name: 'Rian Pratama' }, type: 'Birthday (Hari Ini)' },
    { id: 2, user: { name: 'Mega Utami' }, type: 'Work Anniversary (2 Tahun)' }
  ]
};

export const employeeDashboardMock = {
  today_attendance: {
    clock_in_time: '2026-08-27T08:05:00',
    clock_out_time: null,
    time_in: '08:05:00',
    time_out: null,
    status: 'present'
  },
  leave_quota: 10,
  total_leave_quota: 12,
  leaves_used: 2,
  overtime_this_month: 5,
  pending_overtime_this_month: 2,
  upcoming_events: [
    { id: 1, type: 'meeting', title: 'Daily Standup Lazizmu', description: 'Daring', date: '2026-08-27T10:00:00' },
    { id: 2, type: 'other', title: 'Penyaluran Bantuan Sembako', description: 'Lapangan', date: '2026-08-27T14:00:00' },
    { id: 3, type: 'meeting', title: 'Sinkronisasi Tim Pendistribusian', description: 'Ruang Rapat A', date: '2026-08-28T11:00:00' }
  ],
  recent_attendances: Array.from({ length: 5 }).map((_, i) => ({
    id: i + 1,
    date: new Date(Date.now() - (i + 1) * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
    clock_in_time: '08:00',
    clock_out_time: '17:00',
    time_in: '08:00:00',
    time_out: '17:00:00',
    status: i === 1 ? 'late' : 'present'
  })),
  announcements: [
    { id: 1, title: 'Pemeliharaan Server HRIS Lazizmu', content: 'Sistem HRIS akan melakukan optimalisasi berkala pada Sabtu pukul 00:00 - 04:00 WIB.', publish_date: new Date().toISOString() },
    { id: 2, title: 'Sosialisasi Program Peduli Ummat', content: 'Seluruh divisi diharapkan mengikuti rapat sosialisasi kampanye Ramadhan.', publish_date: new Date(Date.now() - 86400000).toISOString() }
  ]
};

export const usersMock = {
  data: [
    {
      id: 1,
      name: 'Mock Admin',
      email: 'admin@mock.com',
      role: 'admin',
      nik: 'LZM-001',
      phone: '081234567890',
      join_date: '2022-01-15',
      employment_status: 'Tetap',
      position: { id: 1, name: 'Direktur Eksekutif' },
      division: { id: 1, name: 'Penyaluran & Pendayagunaan' },
      department: { name: 'Penyaluran & Pendayagunaan' }
    },
    {
      id: 2,
      name: 'Mock Employee',
      email: 'employee@mock.com',
      role: 'employee',
      nik: 'LZM-002',
      phone: '089876543210',
      join_date: '2023-03-01',
      employment_status: 'Tetap',
      position: { id: 4, name: 'Staf Penyaluran' },
      division: { id: 1, name: 'Penyaluran & Pendayagunaan' },
      department: { name: 'Penyaluran & Pendayagunaan' }
    },
    ...Array.from({ length: 48 }).map((_, i) => {
      const divName = getRandom(departments);
      const posName = getRandom(positions);
      return {
        id: i + 3,
        name: `Karyawan ${i + 3}`,
        email: `karyawan${i + 3}@lazizmu.org`,
        role: 'employee',
        nik: `LZM-${(i + 3).toString().padStart(3, '0')}`,
        phone: `081${Math.floor(10000000 + Math.random() * 90000000)}`,
        join_date: new Date(Date.now() - Math.floor(Math.random() * 800) * 86400000).toISOString().split('T')[0],
        employment_status: (i % 3 === 0) ? 'Kontrak' : 'Tetap',
        position: { id: (i % 8) + 1, name: posName },
        division: { id: (i % 5) + 1, name: divName },
        department: { name: divName }
      };
    })
  ],
  total: 50,
  last_page: 5
};
