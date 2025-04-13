
import React, { useState } from "react";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { Input } from "@/components/ui/input";
import { toast } from "sonner";
import { Student } from "@/context/DataContext";
import { Upload, FileText } from "lucide-react";

interface FileUploadProps {
  onStudentsLoaded: (students: Student[]) => void;
}

const FileUpload: React.FC<FileUploadProps> = ({ onStudentsLoaded }) => {
  const [isLoading, setIsLoading] = useState(false);
  const [fileName, setFileName] = useState<string | null>(null);

  const handleFileChange = async (e: React.ChangeEvent<HTMLInputElement>) => {
    if (!e.target.files || e.target.files.length === 0) {
      return;
    }

    const file = e.target.files[0];
    setFileName(file.name);

    // In a real app, you'd parse the CSV/Excel file
    // For this demo, we're using mock data that simulates file parsing
    setIsLoading(true);
    
    try {
      // Simulate file processing delay
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      // Sample student data that would come from parsing the file
      const mockStudents: Student[] = [
        { id: `s-${Date.now()}-1`, name: "Alex Johnson", enrollmentNumber: "EN001" },
        { id: `s-${Date.now()}-2`, name: "Bradley Cooper", enrollmentNumber: "EN002" },
        { id: `s-${Date.now()}-3`, name: "Cassandra Lee", enrollmentNumber: "EN003" },
        { id: `s-${Date.now()}-4`, name: "Daniel Smith", enrollmentNumber: "EN004" },
        { id: `s-${Date.now()}-5`, name: "Emma Watson", enrollmentNumber: "EN005" },
      ];
      
      onStudentsLoaded(mockStudents);
      toast.success(`Loaded ${mockStudents.length} students from file`);
    } catch (error) {
      console.error("Error processing file:", error);
      toast.error("Failed to process file");
    } finally {
      setIsLoading(false);
    }
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
              {isLoading ? (
                <div className="flex items-center">
                  <svg className="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Processing...
                </div>
              ) : (
                <>
                  <Upload className="mr-2 h-4 w-4" />
                  Upload Student List
                </>
              )}
            </Button>
          </div>
          {fileName && (
            <p className="text-sm text-muted-foreground mt-2 flex items-center">
              <FileText className="h-4 w-4 mr-2" />
              Selected file: {fileName}
            </p>
          )}
        </div>
      </div>
    </div>
  );
};

export default FileUpload;
