
// Helper function to pick random items
const getRandom = (arr: any[]) => arr[Math.floor(Math.random() * arr.length)];

// Generate Divisions
export const mockDivisions = {
  data: [
    'Engineering', 'Human Resources', 'Marketing', 'Sales', 'Finance', 
    'Operations', 'Customer Support', 'Legal', 'Product', 'Design'
  ].map((name, i) => ({ id: i + 1, name })),
  total: 10,
  last_page: 1
};

// Generate Positions
export const mockPositions = {
  data: [
    'Manager', 'Senior Developer', 'Junior Developer', 'Staff', 
    'Lead Designer', 'HR Specialist', 'Accountant', 'Sales Exec'
  ].map((name, i) => ({ id: i + 1, name })),
  total: 8,
  last_page: 1
};

export const mockShifts = {
  data: [
    { id: 1, name: 'Morning Shift (08:00 - 17:00)' },
    { id: 2, name: 'Night Shift (20:00 - 05:00)' },
    { id: 3, name: 'Evening Shift (14:00 - 22:00)' }
  ],
  total: 3,
  last_page: 1
};

// Generate Attendance (50 records)
const statuses = ['present', 'present', 'present', 'late', 'absent'];
const mockUsers = ['Mock Admin', 'Mock Employee', 'Jane Smith', 'John Doe', 'Alice Wonderland'];

export const mockAttendance = {
  data: Array.from({ length: 50 }).map((_, i) => {
    const status = getRandom(statuses);
    const date = new Date(Date.now() - Math.floor(Math.random() * 30) * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
    
    let time_in = null;
    let time_out = null;
    let total_hours = null;

    if (status === 'present') {
      time_in = `0${Math.floor(Math.random() * 2) + 7}:${Math.floor(Math.random() * 59).toString().padStart(2, '0')}:00`;
      time_out = `1${Math.floor(Math.random() * 2) + 6}:${Math.floor(Math.random() * 59).toString().padStart(2, '0')}:00`;
      total_hours = 9;
    } else if (status === 'late') {
      time_in = `09:${Math.floor(Math.random() * 40 + 10).toString().padStart(2, '0')}:00`;
      time_out = `17:00:00`;
      total_hours = 7.5;
    }

    return {
      id: i + 1,
      date,
      time_in,
      time_out,
      total_hours,
      status,
      user: { name: getRandom(mockUsers) }
    };
  }).sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime()),
  total: 50,
  last_page: 5
};

// Generate Announcements (15 records)
export const mockAnnouncements = {
  data: Array.from({ length: 15 }).map((_, i) => {
    const isPublished = Math.random() > 0.3;
    const date = new Date(Date.now() - Math.floor(Math.random() * 60) * 24 * 60 * 60 * 1000).toISOString();
    return {
      id: i + 1,
      title: `Announcement Subject ${i + 1}`,
      content: `This is the detailed content for announcement number ${i + 1}. Please read carefully.`,
      status: isPublished ? 'published' : 'draft',
      publish_date: isPublished ? date : null,
      created_at: date
    };
  }),
  total: 15,
  last_page: 2
};

// Generate Leaves (20 records)
const leaveTypes = ['annual', 'sick', 'permission'];
const leaveStatuses = ['approved', 'pending', 'rejected'];

export const mockLeaves = {
  data: Array.from({ length: 20 }).map((_, i) => {
    const startDate = new Date(Date.now() + Math.floor(Math.random() * 30 - 15) * 24 * 60 * 60 * 1000);
    const endDate = new Date(startDate.getTime() + Math.floor(Math.random() * 3) * 24 * 60 * 60 * 1000);
    return {
      id: i + 1,
      type: getRandom(leaveTypes),
      start_date: startDate.toISOString().split('T')[0],
      end_date: endDate.toISOString().split('T')[0],
      reason: `Leave reason ${i + 1}`,
      status: getRandom(leaveStatuses),
      user: { name: getRandom(mockUsers) }
    };
  }),
  total: 20,
  last_page: 2
};

// Generate Overtimes (20 records)
export const mockOvertimes = {
  data: Array.from({ length: 20 }).map((_, i) => {
    const date = new Date(Date.now() - Math.floor(Math.random() * 30) * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
    const duration = Math.floor(Math.random() * 4) + 1;
    const startHour = 17 + Math.floor(Math.random() * 2);
    const start_time = `${startHour}:00:00`;
    const end_time = `${startHour + duration}:00:00`;
    return {
      id: i + 1,
      date,
      start_time,
      end_time,
      total_duration: duration,
      reason: `Overtime reason ${i + 1}`,
      status: getRandom(leaveStatuses),
      user: { name: getRandom(mockUsers) }
    };
  }),
  total: 20,
  last_page: 2
};

export const mockCalendarEvents = Array.from({ length: 15 }).map((_, i) => {
  const baseTime = Date.now() + Math.floor(Math.random() * 30 - 5) * 24 * 60 * 60 * 1000;
  const start_datetime = new Date(baseTime).toISOString().split('T')[0] + 'T09:00:00';
  const end_datetime = new Date(baseTime).toISOString().split('T')[0] + 'T11:00:00';
  return {
    id: i + 1,
    title: `Acara Perusahaan ${i + 1}`,
    description: `Deskripsi untuk acara ${i + 1}`,
    start_datetime,
    end_datetime,
    is_all_day: Math.random() > 0.8,
    type: Math.random() > 0.5 ? 'MEETING' : 'COMPANY_EVENT',
    divisions: [{ id: 1, name: 'IT' }, { id: 2, name: 'HR' }],
    users: [{ id: 1, name: 'John Doe' }],
    creator_id: 1,
    color: Math.random() > 0.5 ? '#ec4899' : '#3b82f6'
  };
});
