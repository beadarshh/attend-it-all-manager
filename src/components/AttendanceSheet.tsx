
import React, { useState } from "react";
import { Student } from "@/context/DataContext";
import { Button } from "@/components/ui/button";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { Label } from "@/components/ui/label";
import { format } from "date-fns";
import { CalendarIcon, Check } from "lucide-react";
import { cn } from "@/lib/utils";
import { Calendar } from "@/components/ui/calendar";
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover";

interface AttendanceSheetProps {
  students: Student[];
  classId: string;
  onSubmit: (
    date: string,
    records: { studentId: string; status: "present" | "absent" | "leave" }[]
  ) => void;
  existingAttendance?: {
    date: string;
    records: { studentId: string; status: "present" | "absent" | "leave" }[];
  };
}

const AttendanceSheet: React.FC<AttendanceSheetProps> = ({
  students,
  classId,
  onSubmit,
  existingAttendance,
}) => {
  const [date, setDate] = useState<Date | undefined>(
    existingAttendance ? new Date(existingAttendance.date) : new Date()
  );
  
  const [attendanceRecords, setAttendanceRecords] = useState<
    { studentId: string; status: "present" | "absent" | "leave" }[]
  >(
    existingAttendance?.records || 
    students.map((student) => ({
      studentId: student.id,
      status: "present",
    }))
  );

  const handleStatusChange = (studentId: string, status: "present" | "absent" | "leave") => {
    setAttendanceRecords((prev) =>
      prev.map((record) =>
        record.studentId === studentId ? { ...record, status } : record
      )
    );
  };

  const handleSubmit = () => {
    if (!date) {
      return;
    }
    onSubmit(format(date, "yyyy-MM-dd"), attendanceRecords);
  };

  return (
    <div className="border rounded-lg p-6 bg-card">
      <div className="flex justify-between items-center mb-6">
        <h3 className="text-lg font-medium">Attendance Sheet</h3>
        <Popover>
          <PopoverTrigger asChild>
            <Button
              variant={"outline"}
              className={cn(
                "w-[240px] justify-start text-left font-normal",
                !date && "text-muted-foreground"
              )}
            >
              <CalendarIcon className="mr-2 h-4 w-4" />
              {date ? format(date, "PPP") : <span>Pick a date</span>}
            </Button>
          </PopoverTrigger>
          <PopoverContent className="w-auto p-0" align="start">
            <Calendar
              mode="single"
              selected={date}
              onSelect={setDate}
              initialFocus
              className={cn("p-3 pointer-events-auto")}
            />
          </PopoverContent>
        </Popover>
      </div>

      <div className="overflow-x-auto">
        <table className="w-full attendance-table">
          <thead>
            <tr>
              <th className="w-1/5">Enrollment</th>
              <th className="w-2/5">Name</th>
              <th className="w-2/5">Status</th>
            </tr>
          </thead>
          <tbody>
            {students.map((student) => {
              const record = attendanceRecords.find(
                (r) => r.studentId === student.id
              );
              return (
                <tr key={student.id}>
                  <td>{student.enrollmentNumber}</td>
                  <td>{student.name}</td>
                  <td>
                    <RadioGroup
                      value={record?.status || "present"}
                      onValueChange={(value) =>
                        handleStatusChange(
                          student.id,
                          value as "present" | "absent" | "leave"
                        )
                      }
                      className="flex space-x-4"
                    >
                      <div className="flex items-center space-x-2">
                        <RadioGroupItem
                          value="present"
                          id={`present-${student.id}`}
                        />
                        <Label
                          htmlFor={`present-${student.id}`}
                          className="text-sm font-medium text-green-600"
                        >
                          Present
                        </Label>
                      </div>
                      <div className="flex items-center space-x-2">
                        <RadioGroupItem
                          value="absent"
                          id={`absent-${student.id}`}
                        />
                        <Label
                          htmlFor={`absent-${student.id}`}
                          className="text-sm font-medium text-red-600"
                        >
                          Absent
                        </Label>
                      </div>
                      <div className="flex items-center space-x-2">
                        <RadioGroupItem
                          value="leave"
                          id={`leave-${student.id}`}
                        />
                        <Label
                          htmlFor={`leave-${student.id}`}
                          className="text-sm font-medium text-yellow-600"
                        >
                          On Leave
                        </Label>
                      </div>
                    </RadioGroup>
                  </td>
                </tr>
              );
            })}
          </tbody>
        </table>
      </div>

      <div className="mt-6">
        <Button onClick={handleSubmit} className="w-full">
          <Check className="mr-2 h-4 w-4" />
          {existingAttendance ? "Update Attendance" : "Submit Attendance"}
        </Button>
      </div>
    </div>
  );
};

export default AttendanceSheet;
