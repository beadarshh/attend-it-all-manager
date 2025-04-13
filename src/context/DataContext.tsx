
import React, { createContext, useContext, useState, useEffect } from "react";
import { toast } from "sonner";

// Types
export type Student = {
  id: string;
  name: string;
  enrollmentNumber: string;
};

export type Class = {
  id: string;
  branch: string;
  year: string;
  subject: string;
  teacherId: string;
  teacherName: string;
  duration: string;
  days: string[];
  students: Student[];
};

export type AttendanceRecord = {
  id: string;
  date: string;
  classId: string;
  records: {
    studentId: string;
    status: "present" | "absent" | "leave";
  }[];
};

type DataContextType = {
  classes: Class[];
  attendanceRecords: AttendanceRecord[];
  isLoading: boolean;
  addClass: (classData: Omit<Class, "id">) => Promise<boolean>;
  getClassById: (id: string) => Class | undefined;
  getClassesByTeacherId: (teacherId: string) => Class[];
  markAttendance: (attendance: Omit<AttendanceRecord, "id">) => Promise<boolean>;
  getAttendanceByClassId: (classId: string) => AttendanceRecord[];
  getAttendanceByDate: (date: string) => AttendanceRecord[];
  updateAttendance: (id: string, records: { studentId: string; status: "present" | "absent" | "leave" }[]) => Promise<boolean>;
};

// Mock data for testing
const mockClasses: Class[] = [
  {
    id: "class-1",
    branch: "Computer Science",
    year: "2023",
    subject: "Web Development",
    teacherId: "teacher-123",
    teacherName: "John Doe",
    duration: "Jan 2023 - May 2023",
    days: ["Monday", "Wednesday", "Friday"],
    students: [
      { id: "student-1", name: "Alice Johnson", enrollmentNumber: "CS001" },
      { id: "student-2", name: "Bob Smith", enrollmentNumber: "CS002" },
      { id: "student-3", name: "Charlie Brown", enrollmentNumber: "CS003" },
      { id: "student-4", name: "Diana Prince", enrollmentNumber: "CS004" },
      { id: "student-5", name: "Edward Jones", enrollmentNumber: "CS005" }
    ]
  },
  {
    id: "class-2",
    branch: "Electrical Engineering",
    year: "2023",
    subject: "Circuit Analysis",
    teacherId: "teacher-123",
    teacherName: "John Doe",
    duration: "Jan 2023 - May 2023",
    days: ["Tuesday", "Thursday"],
    students: [
      { id: "student-6", name: "Frank Miller", enrollmentNumber: "EE001" },
      { id: "student-7", name: "Grace Lee", enrollmentNumber: "EE002" },
      { id: "student-8", name: "Henry Wilson", enrollmentNumber: "EE003" },
      { id: "student-9", name: "Iris Zhang", enrollmentNumber: "EE004" },
      { id: "student-10", name: "Jack Thompson", enrollmentNumber: "EE005" }
    ]
  }
];

const mockAttendanceRecords: AttendanceRecord[] = [
  {
    id: "att-1",
    date: "2023-04-10",
    classId: "class-1",
    records: [
      { studentId: "student-1", status: "present" },
      { studentId: "student-2", status: "present" },
      { studentId: "student-3", status: "absent" },
      { studentId: "student-4", status: "present" },
      { studentId: "student-5", status: "leave" }
    ]
  },
  {
    id: "att-2",
    date: "2023-04-11",
    classId: "class-2",
    records: [
      { studentId: "student-6", status: "present" },
      { studentId: "student-7", status: "absent" },
      { studentId: "student-8", status: "present" },
      { studentId: "student-9", status: "present" },
      { studentId: "student-10", status: "leave" }
    ]
  }
];

const DataContext = createContext<DataContextType | undefined>(undefined);

export const DataProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [classes, setClasses] = useState<Class[]>([]);
  const [attendanceRecords, setAttendanceRecords] = useState<AttendanceRecord[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    // Load mock data
    setClasses(mockClasses);
    setAttendanceRecords(mockAttendanceRecords);
    setIsLoading(false);
  }, []);

  const addClass = async (classData: Omit<Class, "id">): Promise<boolean> => {
    try {
      setIsLoading(true);
      // Simulate API call delay
      await new Promise((resolve) => setTimeout(resolve, 1000));

      const newClass: Class = {
        ...classData,
        id: `class-${Date.now()}`
      };

      setClasses((prev) => [...prev, newClass]);
      toast.success("Class added successfully");
      setIsLoading(false);
      return true;
    } catch (error) {
      console.error("Error adding class:", error);
      toast.error("Failed to add class");
      setIsLoading(false);
      return false;
    }
  };

  const getClassById = (id: string): Class | undefined => {
    return classes.find((c) => c.id === id);
  };

  const getClassesByTeacherId = (teacherId: string): Class[] => {
    return classes.filter((c) => c.teacherId === teacherId);
  };

  const markAttendance = async (attendance: Omit<AttendanceRecord, "id">): Promise<boolean> => {
    try {
      setIsLoading(true);
      // Simulate API call delay
      await new Promise((resolve) => setTimeout(resolve, 1000));

      const newAttendance: AttendanceRecord = {
        ...attendance,
        id: `att-${Date.now()}`
      };

      setAttendanceRecords((prev) => [...prev, newAttendance]);
      toast.success("Attendance marked successfully");
      setIsLoading(false);
      return true;
    } catch (error) {
      console.error("Error marking attendance:", error);
      toast.error("Failed to mark attendance");
      setIsLoading(false);
      return false;
    }
  };

  const getAttendanceByClassId = (classId: string): AttendanceRecord[] => {
    return attendanceRecords.filter((a) => a.classId === classId);
  };

  const getAttendanceByDate = (date: string): AttendanceRecord[] => {
    return attendanceRecords.filter((a) => a.date === date);
  };

  const updateAttendance = async (id: string, records: { studentId: string; status: "present" | "absent" | "leave" }[]): Promise<boolean> => {
    try {
      setIsLoading(true);
      // Simulate API call delay
      await new Promise((resolve) => setTimeout(resolve, 1000));

      setAttendanceRecords((prev) =>
        prev.map((a) => (a.id === id ? { ...a, records } : a))
      );
      toast.success("Attendance updated successfully");
      setIsLoading(false);
      return true;
    } catch (error) {
      console.error("Error updating attendance:", error);
      toast.error("Failed to update attendance");
      setIsLoading(false);
      return false;
    }
  };

  return (
    <DataContext.Provider
      value={{
        classes,
        attendanceRecords,
        isLoading,
        addClass,
        getClassById,
        getClassesByTeacherId,
        markAttendance,
        getAttendanceByClassId,
        getAttendanceByDate,
        updateAttendance
      }}
    >
      {children}
    </DataContext.Provider>
  );
};

export const useData = (): DataContextType => {
  const context = useContext(DataContext);
  if (context === undefined) {
    throw new Error("useData must be used within a DataProvider");
  }
  return context;
};
