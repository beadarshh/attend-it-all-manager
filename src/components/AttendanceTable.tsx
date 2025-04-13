
import React, { useState } from "react";
import { format } from "date-fns";
import { Download, Filter, Edit } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { toast } from "sonner";
import { Class, AttendanceRecord } from "@/context/DataContext";

interface AttendanceTableProps {
  classData: Class;
  attendanceRecords: AttendanceRecord[];
  onEditAttendance: (record: AttendanceRecord) => void;
}

const AttendanceTable: React.FC<AttendanceTableProps> = ({
  classData,
  attendanceRecords,
  onEditAttendance,
}) => {
  const [searchTerm, setSearchTerm] = useState("");
  const [dateFilter, setDateFilter] = useState<string | undefined>();

  const handleDownload = () => {
    // In a real app, this would generate a CSV file
    toast.success("Attendance data downloaded successfully");
  };

  // Filter records by date and search term
  const filteredRecords = attendanceRecords.filter((record) => {
    // Filter by date if date filter is set
    if (dateFilter && record.date !== dateFilter) {
      return false;
    }

    // Filter by student name or enrollment number
    if (searchTerm) {
      const matchingStudents = record.records.filter((r) => {
        const student = classData.students.find((s) => s.id === r.studentId);
        if (!student) return false;
        
        return (
          student.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
          student.enrollmentNumber.toLowerCase().includes(searchTerm.toLowerCase())
        );
      });
      
      return matchingStudents.length > 0;
    }

    return true;
  });

  const getStatusColor = (status: string) => {
    switch (status) {
      case "present":
        return "text-green-600 bg-green-100";
      case "absent":
        return "text-red-600 bg-red-100";
      case "leave":
        return "text-yellow-600 bg-yellow-100";
      default:
        return "";
    }
  };

  return (
    <div className="space-y-4">
      <div className="flex flex-col sm:flex-row justify-between gap-4">
        <div className="flex-1">
          <Input
            placeholder="Search by name or enrollment number"
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full"
          />
        </div>
        <div className="flex gap-2">
          <Select
            value={dateFilter}
            onValueChange={(value) => setDateFilter(value)}
          >
            <SelectTrigger className="w-[200px]">
              <SelectValue placeholder="Filter by date" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all-dates">All Dates</SelectItem>
              {[...new Set(attendanceRecords.map((record) => record.date))].map(
                (date) => (
                  <SelectItem key={date} value={date}>
                    {format(new Date(date), "PPP")}
                  </SelectItem>
                )
              )}
            </SelectContent>
          </Select>
          <Button variant="outline" onClick={handleDownload}>
            <Download className="h-4 w-4 mr-2" />
            Download
          </Button>
        </div>
      </div>

      <div className="bg-white rounded-lg border shadow-sm overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full attendance-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Enrollment No.</th>
                <th>Student Name</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {filteredRecords.length > 0 ? (
                filteredRecords.map((record) =>
                  record.records.map((attendance) => {
                    const student = classData.students.find(
                      (s) => s.id === attendance.studentId
                    );
                    if (!student) return null;

                    return (
                      <tr key={`${record.id}-${attendance.studentId}`}>
                        <td>{format(new Date(record.date), "PPP")}</td>
                        <td>{student.enrollmentNumber}</td>
                        <td>{student.name}</td>
                        <td>
                          <span
                            className={`inline-block px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(
                              attendance.status
                            )}`}
                          >
                            {attendance.status.charAt(0).toUpperCase() +
                              attendance.status.slice(1)}
                          </span>
                        </td>
                        <td>
                          <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => onEditAttendance(record)}
                          >
                            <Edit className="h-4 w-4" />
                          </Button>
                        </td>
                      </tr>
                    );
                  })
                ).flat()
              ) : (
                <tr>
                  <td colSpan={5} className="text-center py-4">
                    No attendance records found
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};

export default AttendanceTable;
