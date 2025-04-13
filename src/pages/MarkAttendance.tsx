
import React from "react";
import { useParams, useNavigate } from "react-router-dom";
import Layout from "@/components/Layout";
import AttendanceSheet from "@/components/AttendanceSheet";
import { useData } from "@/context/DataContext";
import { Button } from "@/components/ui/button";
import { ArrowLeft } from "lucide-react";

const MarkAttendance = () => {
  const { classId } = useParams<{ classId: string }>();
  const navigate = useNavigate();
  const { getClassById, markAttendance } = useData();

  const classData = classId ? getClassById(classId) : undefined;

  const handleSubmitAttendance = async (
    date: string,
    records: { studentId: string; status: "present" | "absent" | "leave" }[]
  ) => {
    if (!classId) return;

    const success = await markAttendance({
      date,
      classId,
      records,
    });

    if (success) {
      navigate("/dashboard");
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
            <h1 className="text-3xl font-bold">Mark Attendance</h1>
            <p className="text-muted-foreground mt-1">
              {classData.subject} - {classData.branch}
            </p>
          </div>
        </div>

        <AttendanceSheet
          students={classData.students}
          classId={classData.id}
          onSubmit={handleSubmitAttendance}
        />
      </div>
    </Layout>
  );
};

export default MarkAttendance;
