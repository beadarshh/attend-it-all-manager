
import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import Layout from "@/components/Layout";
import FileUpload from "@/components/FileUpload";
import ClassForm from "@/components/ClassForm";
import { Student } from "@/context/DataContext";
import { useAuth } from "@/context/AuthContext";
import { useData } from "@/context/DataContext";
import { ArrowLeft } from "lucide-react";
import { Button } from "@/components/ui/button";

const AddClass = () => {
  const [students, setStudents] = useState<Student[]>([]);
  const navigate = useNavigate();
  const { user } = useAuth();
  const { addClass } = useData();

  const handleStudentsLoaded = (loadedStudents: Student[]) => {
    setStudents(loadedStudents);
  };

  const handleFormSubmit = async (values: any) => {
    if (!user) return;

    const success = await addClass({
      ...values,
      teacherId: user.id,
      teacherName: user.name,
    });

    if (success) {
      navigate("/dashboard");
    }
  };

  return (
    <Layout>
      <div className="space-y-6">
        <div className="flex items-center gap-4">
          <Button variant="ghost" size="icon" onClick={() => navigate(-1)}>
            <ArrowLeft className="h-4 w-4" />
          </Button>
          <div>
            <h1 className="text-3xl font-bold">Add New Class</h1>
            <p className="text-muted-foreground mt-1">
              Upload a student list and create a new class
            </p>
          </div>
        </div>

        <div className="space-y-6">
          <FileUpload onStudentsLoaded={handleStudentsLoaded} />

          {students.length > 0 && user && (
            <>
              <div className="bg-muted p-4 rounded-lg">
                <h3 className="font-medium mb-2">Uploaded Student List</h3>
                <p className="text-sm text-muted-foreground">
                  {students.length} students loaded successfully
                </p>
              </div>

              <ClassForm
                students={students}
                teacherId={user.id}
                teacherName={user.name}
                onSubmit={handleFormSubmit}
              />
            </>
          )}
        </div>
      </div>
    </Layout>
  );
};

export default AddClass;
