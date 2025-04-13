
import React, { useState } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Student } from "@/context/DataContext";
import { PlusCircle, Pencil, Trash, Save, X } from "lucide-react";
import { toast } from "sonner";

interface StudentManagementProps {
  students: Student[];
  onStudentsChange: (students: Student[]) => void;
}

const StudentManagement: React.FC<StudentManagementProps> = ({
  students,
  onStudentsChange,
}) => {
  const [editingId, setEditingId] = useState<string | null>(null);
  const [isAdding, setIsAdding] = useState(false);
  const [newStudent, setNewStudent] = useState<{
    name: string;
    enrollmentNumber: string;
  }>({ name: "", enrollmentNumber: "" });

  const handleAddNewStudent = () => {
    setIsAdding(true);
    setNewStudent({ name: "", enrollmentNumber: "" });
  };

  const handleSaveNewStudent = () => {
    if (!newStudent.name || !newStudent.enrollmentNumber) {
      toast.error("Please fill in all fields");
      return;
    }

    // Check for duplicate enrollment numbers
    if (students.some(s => s.enrollmentNumber === newStudent.enrollmentNumber)) {
      toast.error("A student with this enrollment number already exists");
      return;
    }

    const updatedStudents = [
      ...students,
      {
        id: `s-${Date.now()}`,
        name: newStudent.name,
        enrollmentNumber: newStudent.enrollmentNumber,
      },
    ];

    onStudentsChange(updatedStudents);
    setIsAdding(false);
    toast.success("Student added successfully");
  };

  const handleCancelAddStudent = () => {
    setIsAdding(false);
  };

  const handleEditStudent = (id: string) => {
    setEditingId(id);
  };

  const handleSaveEdit = (id: string, name: string, enrollmentNumber: string) => {
    if (!name || !enrollmentNumber) {
      toast.error("Please fill in all fields");
      return;
    }

    // Check for duplicate enrollment numbers (excluding the current student)
    if (students.some(s => s.enrollmentNumber === enrollmentNumber && s.id !== id)) {
      toast.error("A student with this enrollment number already exists");
      return;
    }

    const updatedStudents = students.map((student) =>
      student.id === id ? { ...student, name, enrollmentNumber } : student
    );

    onStudentsChange(updatedStudents);
    setEditingId(null);
    toast.success("Student updated successfully");
  };

  const handleDeleteStudent = (id: string) => {
    const updatedStudents = students.filter((student) => student.id !== id);
    onStudentsChange(updatedStudents);
    toast.success("Student removed successfully");
  };

  return (
    <div className="space-y-4">
      <div className="flex justify-between items-center">
        <h3 className="text-lg font-medium">Student List</h3>
        <Button onClick={handleAddNewStudent} size="sm" disabled={isAdding}>
          <PlusCircle className="h-4 w-4 mr-2" />
          Add Student
        </Button>
      </div>

      {students.length === 0 && !isAdding ? (
        <div className="text-center p-4 border border-dashed rounded-md bg-muted/50">
          <p className="text-muted-foreground">No students added yet</p>
        </div>
      ) : (
        <div className="border rounded-md">
          <table className="w-full">
            <thead>
              <tr className="border-b bg-muted/50">
                <th className="px-4 py-2 text-left">Name</th>
                <th className="px-4 py-2 text-left">Enrollment Number</th>
                <th className="px-4 py-2 text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              {students.map((student) => (
                <tr key={student.id} className="border-b last:border-b-0">
                  <td className="px-4 py-2">
                    {editingId === student.id ? (
                      <Input
                        defaultValue={student.name}
                        id={`name-${student.id}`}
                        className="max-w-xs"
                      />
                    ) : (
                      student.name
                    )}
                  </td>
                  <td className="px-4 py-2">
                    {editingId === student.id ? (
                      <Input
                        defaultValue={student.enrollmentNumber}
                        id={`enrollment-${student.id}`}
                        className="max-w-xs"
                      />
                    ) : (
                      student.enrollmentNumber
                    )}
                  </td>
                  <td className="px-4 py-2 text-right">
                    {editingId === student.id ? (
                      <div className="flex justify-end gap-2">
                        <Button
                          size="sm"
                          variant="outline"
                          onClick={() => {
                            const nameInput = document.getElementById(`name-${student.id}`) as HTMLInputElement;
                            const enrollmentInput = document.getElementById(`enrollment-${student.id}`) as HTMLInputElement;
                            handleSaveEdit(student.id, nameInput.value, enrollmentInput.value);
                          }}
                        >
                          <Save className="h-4 w-4" />
                        </Button>
                        <Button
                          size="sm"
                          variant="outline"
                          onClick={() => setEditingId(null)}
                        >
                          <X className="h-4 w-4" />
                        </Button>
                      </div>
                    ) : (
                      <div className="flex justify-end gap-2">
                        <Button
                          size="sm"
                          variant="outline"
                          onClick={() => handleEditStudent(student.id)}
                        >
                          <Pencil className="h-4 w-4" />
                        </Button>
                        <Button
                          size="sm"
                          variant="outline"
                          className="text-destructive hover:bg-destructive/10"
                          onClick={() => handleDeleteStudent(student.id)}
                        >
                          <Trash className="h-4 w-4" />
                        </Button>
                      </div>
                    )}
                  </td>
                </tr>
              ))}
              {isAdding && (
                <tr className="border-b">
                  <td className="px-4 py-2">
                    <Input
                      value={newStudent.name}
                      onChange={(e) =>
                        setNewStudent({ ...newStudent, name: e.target.value })
                      }
                      placeholder="Student Name"
                      className="max-w-xs"
                    />
                  </td>
                  <td className="px-4 py-2">
                    <Input
                      value={newStudent.enrollmentNumber}
                      onChange={(e) =>
                        setNewStudent({
                          ...newStudent,
                          enrollmentNumber: e.target.value,
                        })
                      }
                      placeholder="Enrollment Number"
                      className="max-w-xs"
                    />
                  </td>
                  <td className="px-4 py-2 text-right">
                    <div className="flex justify-end gap-2">
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={handleSaveNewStudent}
                      >
                        <Save className="h-4 w-4" />
                      </Button>
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={handleCancelAddStudent}
                      >
                        <X className="h-4 w-4" />
                      </Button>
                    </div>
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
};

export default StudentManagement;
