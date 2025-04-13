
import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import { Layout } from "@/components/Layout";
import FileUpload from "@/components/FileUpload";
import ClassForm from "@/components/ClassForm";
import StudentManagement from "@/components/StudentManagement";
import { Student } from "@/context/DataContext";
import { useAuth } from "@/context/AuthContext";
import { useData } from "@/context/DataContext";
import { ArrowLeft } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

const AddClass = () => {
  const [students, setStudents] = useState<Student[]>([]);
  const navigate = useNavigate();
  const { user } = useAuth();
  const { addClass } = useData();

  const handleStudentsLoaded = (loadedStudents: Student[]) => {
    setStudents(loadedStudents);
  };

  const handleStudentsChange = (updatedStudents: Student[]) => {
    setStudents(updatedStudents);
  };

  const handleFormSubmit = async (values: any) => {
    if (!user) return;

    // Make sure we have at least one student
    if (students.length === 0) {
      alert("Please add at least one student before creating the class");
      return;
    }

    const success = await addClass({
      ...values,
      teacherId: user.id,
      teacherName: user.name,
      students: students
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
              Upload a student list or add students manually to create a new class
            </p>
          </div>
        </div>

        <div className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Step 1: Add Students</CardTitle>
            </CardHeader>
            <CardContent className="space-y-6">
              <FileUpload onStudentsLoaded={handleStudentsLoaded} />
              
              <div className="border-t pt-6">
                <StudentManagement 
                  students={students} 
                  onStudentsChange={handleStudentsChange} 
                />
              </div>
            </CardContent>
          </Card>

          {user && (
            <Card>
              <CardHeader>
                <CardTitle>Step 2: Class Details</CardTitle>
              </CardHeader>
              <CardContent>
                <ClassForm
                  students={students}
                  teacherId={user.id}
                  teacherName={user.name}
                  onSubmit={handleFormSubmit}
                />
              </CardContent>
            </Card>
          )}
        </div>
      </div>
    </Layout>
  );
};

export default AddClass;
