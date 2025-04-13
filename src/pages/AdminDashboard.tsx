
import React, { useState } from "react";
import { Layout } from "@/components/Layout";
import { useData } from "@/context/DataContext";
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Button } from "@/components/ui/button";
import { Calendar, Clock, Download, Users, BookOpen, Check } from "lucide-react";
import { toast } from "sonner";
import { format } from "date-fns";

const AdminDashboard = () => {
  const { classes, attendanceRecords } = useData();
  const [selectedClassId, setSelectedClassId] = useState<string>("");
  const [selectedDate, setSelectedDate] = useState<string>("");

  const handleDownload = () => {
    // In a real app, this would generate a CSV file
    toast.success("Attendance data downloaded successfully");
  };

  const getAttendanceSummary = () => {
    if (!selectedClassId && !selectedDate) {
      return attendanceRecords.flatMap((record) => record.records);
    }

    const filtered = attendanceRecords.filter((record) => {
      if (selectedClassId && selectedClassId !== "all-classes" && record.classId !== selectedClassId) {
        return false;
      }
      if (selectedDate && selectedDate !== "all-dates" && record.date !== selectedDate) {
        return false;
      }
      return true;
    });

    return filtered.flatMap((record) => record.records);
  };

  const attendanceSummary = getAttendanceSummary();
  const totalPresent = attendanceSummary.filter((r) => r.status === "present").length;
  const totalAbsent = attendanceSummary.filter((r) => r.status === "absent").length;
  const totalOnLeave = attendanceSummary.filter((r) => r.status === "leave").length;

  return (
    <Layout>
      <div className="space-y-6">
        <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <div>
            <h1 className="text-3xl font-bold">Admin Dashboard</h1>
            <p className="text-muted-foreground mt-1">
              Monitor all attendance records across classes
            </p>
          </div>
          <Button onClick={handleDownload}>
            <Download className="h-4 w-4 mr-2" />
            Export All Data
          </Button>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium text-muted-foreground">
                Total Classes
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="flex items-center">
                <BookOpen className="h-5 w-5 text-primary mr-2" />
                <span className="text-3xl font-bold">{classes.length}</span>
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium text-muted-foreground">
                Total Students
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="flex items-center">
                <Users className="h-5 w-5 text-primary mr-2" />
                <span className="text-3xl font-bold">
                  {classes.reduce(
                    (total, cls) => total + cls.students.length,
                    0
                  )}
                </span>
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium text-muted-foreground">
                Attendance Records
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="flex items-center">
                <Check className="h-5 w-5 text-primary mr-2" />
                <span className="text-3xl font-bold">
                  {attendanceRecords.length}
                </span>
              </div>
            </CardContent>
          </Card>
        </div>

        <div className="bg-card rounded-lg border shadow-sm p-6">
          <div className="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <h2 className="text-lg font-medium">Attendance Overview</h2>
            <div className="flex flex-col sm:flex-row gap-2">
              <Select
                value={selectedClassId}
                onValueChange={setSelectedClassId}
              >
                <SelectTrigger className="w-[200px]">
                  <SelectValue placeholder="Filter by class" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all-classes">All Classes</SelectItem>
                  {classes.map((cls) => (
                    <SelectItem key={cls.id} value={cls.id}>
                      {cls.subject} - {cls.branch}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>

              <Select
                value={selectedDate}
                onValueChange={setSelectedDate}
              >
                <SelectTrigger className="w-[200px]">
                  <SelectValue placeholder="Filter by date" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all-dates">All Dates</SelectItem>
                  {[...new Set(attendanceRecords.map((r) => r.date))].map(
                    (date) => (
                      <SelectItem key={date} value={date}>
                        {format(new Date(date), "PPP")}
                      </SelectItem>
                    )
                  )}
                </SelectContent>
              </Select>
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div className="bg-green-50 p-4 rounded-lg border border-green-100">
              <div className="text-green-600 font-medium mb-1">Present</div>
              <div className="text-2xl font-bold">{totalPresent}</div>
              <div className="text-sm text-muted-foreground mt-1">
                {Math.round((totalPresent / attendanceSummary.length) * 100) || 0}% of total
              </div>
            </div>
            <div className="bg-red-50 p-4 rounded-lg border border-red-100">
              <div className="text-red-600 font-medium mb-1">Absent</div>
              <div className="text-2xl font-bold">{totalAbsent}</div>
              <div className="text-sm text-muted-foreground mt-1">
                {Math.round((totalAbsent / attendanceSummary.length) * 100) || 0}% of total
              </div>
            </div>
            <div className="bg-yellow-50 p-4 rounded-lg border border-yellow-100">
              <div className="text-yellow-600 font-medium mb-1">On Leave</div>
              <div className="text-2xl font-bold">{totalOnLeave}</div>
              <div className="text-sm text-muted-foreground mt-1">
                {Math.round((totalOnLeave / attendanceSummary.length) * 100) || 0}% of total
              </div>
            </div>
          </div>

          <div className="mt-6">
            <h3 className="font-medium mb-3">Recent Attendance</h3>
            <div className="overflow-x-auto">
              <table className="w-full attendance-table">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Class</th>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>On Leave</th>
                  </tr>
                </thead>
                <tbody>
                  {attendanceRecords
                    .filter((record) => {
                      if (selectedClassId && selectedClassId !== "all-classes" && record.classId !== selectedClassId) {
                        return false;
                      }
                      if (selectedDate && selectedDate !== "all-dates" && record.date !== selectedDate) {
                        return false;
                      }
                      return true;
                    })
                    .slice(0, 5)
                    .map((record) => {
                      const classInfo = classes.find((c) => c.id === record.classId);
                      const present = record.records.filter((r) => r.status === "present").length;
                      const absent = record.records.filter((r) => r.status === "absent").length;
                      const onLeave = record.records.filter((r) => r.status === "leave").length;

                      return (
                        <tr key={record.id}>
                          <td>{format(new Date(record.date), "PPP")}</td>
                          <td>
                            {classInfo
                              ? `${classInfo.subject} - ${classInfo.branch}`
                              : "Unknown"}
                          </td>
                          <td className="text-green-600">{present}</td>
                          <td className="text-red-600">{absent}</td>
                          <td className="text-yellow-600">{onLeave}</td>
                        </tr>
                      );
                    })}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </Layout>
  );
};

export default AdminDashboard;
