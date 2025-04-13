
import React, { useState } from "react";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { Input } from "@/components/ui/input";
import { toast } from "sonner";
import { Student } from "@/context/DataContext";
import { Upload } from "lucide-react";

interface FileUploadProps {
  onStudentsLoaded: (students: Student[]) => void;
}

const FileUpload: React.FC<FileUploadProps> = ({ onStudentsLoaded }) => {
  const [isLoading, setIsLoading] = useState(false);
  const [fileName, setFileName] = useState<string | null>(null);

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (!e.target.files || e.target.files.length === 0) {
      return;
    }

    const file = e.target.files[0];
    setFileName(file.name);

    // In a real app, you'd parse the CSV/Excel file
    // For this demo, we'll simulate parsing with mock data
    setIsLoading(true);
    setTimeout(() => {
      const mockStudents: Student[] = [
        { id: `s-${Date.now()}-1`, name: "Alex Johnson", enrollmentNumber: "EN001" },
        { id: `s-${Date.now()}-2`, name: "Bradley Cooper", enrollmentNumber: "EN002" },
        { id: `s-${Date.now()}-3`, name: "Cassandra Lee", enrollmentNumber: "EN003" },
        { id: `s-${Date.now()}-4`, name: "Daniel Smith", enrollmentNumber: "EN004" },
        { id: `s-${Date.now()}-5`, name: "Emma Watson", enrollmentNumber: "EN005" },
      ];
      
      onStudentsLoaded(mockStudents);
      toast.success(`Loaded ${mockStudents.length} students from file`);
      setIsLoading(false);
    }, 1500);
  };

  return (
    <div className="border rounded-lg p-6 bg-card">
      <h3 className="text-lg font-medium mb-4">Upload Student List</h3>
      <div className="space-y-4">
        <div className="flex flex-col space-y-2">
          <Label htmlFor="file-upload">Select CSV or Excel file</Label>
          <div className="flex items-center gap-4">
            <Input
              id="file-upload"
              type="file"
              accept=".csv,.xlsx,.xls"
              onChange={handleFileChange}
              className="hidden"
            />
            <Button 
              onClick={() => document.getElementById("file-upload")?.click()}
              disabled={isLoading}
              className="w-full"
            >
              <Upload className="mr-2 h-4 w-4" />
              {isLoading ? "Processing..." : "Upload Student List"}
            </Button>
          </div>
          {fileName && (
            <p className="text-sm text-muted-foreground mt-2">
              Selected file: {fileName}
            </p>
          )}
        </div>
      </div>
    </div>
  );
};

export default FileUpload;
