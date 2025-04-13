
import React, { useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import Layout from "@/components/Layout";
import AttendanceTable from "@/components/AttendanceTable";
import AttendanceSheet from "@/components/AttendanceSheet";
import { useData } from "@/context/DataContext";
import { Button } from "@/components/ui/button";
import { ArrowLeft } from "lucide-react";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { AttendanceRecord } from "@/context/DataContext";

const AttendanceHistory = () => {
  const { classId } = useParams<{ classId: string }>();
  const navigate = useNavigate();
  const { getClassById, getAttendanceByClassId, updateAttendance } = useData();
  const [editingAttendance, setEditingAttendance] = useState<AttendanceRecord | null>(null);

  const classData = classId ? getClassById(classId) : undefined;
  const attendanceRecords = classId ? getAttendanceByClassId(classId) : [];

  const handleEditAttendance = (record: AttendanceRecord) => {
    setEditingAttendance(record);
  };

  const handleUpdateAttendance = async (
    date: string,
    records: { studentId: string; status: "present" | "absent" | "leave" }[]
  ) => {
    if (!editingAttendance) return;

    const success = await updateAttendance(editingAttendance.id, records);
    if (success) {
      setEditingAttendance(null);
    }
  };

  if (!classData) {
    return (
      <Layout>
        <div className="text-center py-12">
          <h2 className="text-2xl font-bold">Class not found</h2>
          <Button
            variant="link"
            onClick={() => navigate("/dashboard")}
            className="mt-4"
          >
            Go back to dashboard
          </Button>
        </div>
      </Layout>
    );
  }

  return (
    <Layout>
      <div className="space-y-6">
        <div className="flex items-center gap-4">
          <Button variant="ghost" size="icon" onClick={() => navigate(-1)}>
            <ArrowLeft className="h-4 w-4" />
          </Button>
          <div>
            <h1 className="text-3xl font-bold">Attendance History</h1>
            <p className="text-muted-foreground mt-1">
              {classData.subject} - {classData.branch}
            </p>
          </div>
        </div>

        <AttendanceTable 
          classData={classData} 
          attendanceRecords={attendanceRecords}
          onEditAttendance={handleEditAttendance}
        />
      </div>

      <Dialog 
        open={!!editingAttendance} 
        onOpenChange={(open) => !open && setEditingAttendance(null)}
      >
        <DialogContent className="sm:max-w-[800px]">
          <DialogHeader>
            <DialogTitle>Edit Attendance</DialogTitle>
          </DialogHeader>
          {editingAttendance && classData && (
            <AttendanceSheet
              students={classData.students}
              classId={classData.id}
              onSubmit={handleUpdateAttendance}
              existingAttendance={{
                date: editingAttendance.date,
                records: editingAttendance.records,
              }}
            />
          )}
        </DialogContent>
      </Dialog>
    </Layout>
  );
};

export default AttendanceHistory;
