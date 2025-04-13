
import React, { useState } from "react";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { Input } from "@/components/ui/input";
import { toast } from "sonner";
import { Student } from "@/context/DataContext";
import { Upload, FileText } from "lucide-react";
import * as XLSX from 'xlsx';

interface FileUploadProps {
  onStudentsLoaded: (students: Student[]) => void;
}

const FileUpload: React.FC<FileUploadProps> = ({ onStudentsLoaded }) => {
  const [isLoading, setIsLoading] = useState(false);
  const [fileName, setFileName] = useState<string | null>(null);

  const parseCSV = (text: string): Student[] => {
    const lines = text.split(/\r\n|\n/);
    const students: Student[] = [];
    
    if (lines.length === 0) {
      return students;
    }
    
    // Skip header row if it exists - more robust detection
    const firstLine = lines[0].toLowerCase();
    const startRow = firstLine.includes('name') || 
                   firstLine.includes('enrollment') ||
                   firstLine.includes('student') ? 1 : 0;
    
    console.log("CSV parsing - total lines:", lines.length);
    console.log("Starting from row:", startRow);
    
    for (let i = startRow; i < lines.length; i++) {
      const line = lines[i].trim();
      if (!line) continue;
      
      // Support both comma and tab delimited files
      const delimiter = line.includes('\t') ? '\t' : ',';
      const parts = line.split(delimiter);
      
      if (parts.length >= 2) {
        const name = parts[0].trim();
        const enrollmentNumber = parts[1].trim();
        
        if (name && enrollmentNumber) {
          students.push({
            id: `s-${Date.now()}-${i}`,
            name,
            enrollmentNumber
          });
        }
      }
    }
    
    return students;
  };

  const parseExcel = (data: ArrayBuffer): Student[] => {
    try {
      // Parse Excel data using xlsx library
      const workbook = XLSX.read(data, { type: 'array' });
      const firstSheetName = workbook.SheetNames[0];
      const worksheet = workbook.Sheets[firstSheetName];
      
      // Convert worksheet to JSON
      const jsonData = XLSX.utils.sheet_to_json<any>(worksheet);
      console.log("Excel data parsed:", jsonData);
      
      // Map the data to students, handling different possible column names
      return jsonData.map((row, index) => {
        // Try to find name and enrollment number from various possible column names
        const name = row.Name || row.name || row.StudentName || row['Student Name'] || 
                    row.STUDENT || row.Student || row['Full Name'] || Object.values(row)[0];
                    
        const enrollmentNumber = row.Enrollment || row.enrollment || row.EnrollmentNumber || 
                               row['Enrollment Number'] || row.ID || row.Id || row.id || 
                               row.Roll || row['Roll Number'] || Object.values(row)[1];
        
        if (name && enrollmentNumber) {
          return {
            id: `s-${Date.now()}-${index}`,
            name: String(name).trim(),
            enrollmentNumber: String(enrollmentNumber).trim()
          };
        }
        return null;
      }).filter(Boolean) as Student[];
    } catch (error) {
      console.error("Error parsing Excel:", error);
      return [];
    }
  };

  const handleFileChange = async (e: React.ChangeEvent<HTMLInputElement>) => {
    if (!e.target.files || e.target.files.length === 0) {
      return;
    }

    const file = e.target.files[0];
    setFileName(file.name);
    setIsLoading(true);
    
    try {
      const fileExtension = file.name.split('.').pop()?.toLowerCase();
      console.log("Processing file:", file.name, "Extension:", fileExtension);
      
      if (fileExtension === 'csv') {
        // Handle CSV files
        const reader = new FileReader();
        
        reader.onload = (event) => {
          if (!event.target?.result) {
            toast.error("Failed to read file content");
            setIsLoading(false);
            return;
          }
          
          const content = event.target.result as string;
          const students = parseCSV(content);
          
          if (students.length === 0) {
            toast.error("No student data found in the CSV file");
            setIsLoading(false);
            return;
          }
          
          console.log("CSV students extracted:", students);
          onStudentsLoaded(students);
          toast.success(`Loaded ${students.length} students from CSV file`);
          setIsLoading(false);
        };
        
        reader.onerror = () => {
          toast.error("Failed to read CSV file");
          setIsLoading(false);
        };
        
        reader.readAsText(file);
      } else if (fileExtension === 'xlsx' || fileExtension === 'xls') {
        // Handle Excel files
        const reader = new FileReader();
        
        reader.onload = (event) => {
          if (!event.target?.result) {
            toast.error("Failed to read Excel file");
            setIsLoading(false);
            return;
          }
          
          const data = event.target.result as ArrayBuffer;
          const students = parseExcel(data);
          
          if (students.length === 0) {
            toast.error("No student data found in the Excel file");
            setIsLoading(false);
            return;
          }
          
          console.log("Excel students extracted:", students);
          onStudentsLoaded(students);
          toast.success(`Loaded ${students.length} students from Excel file`);
          setIsLoading(false);
        };
        
        reader.onerror = () => {
          toast.error("Failed to read Excel file");
          setIsLoading(false);
        };
        
        reader.readAsArrayBuffer(file);
      } else {
        toast.error("Unsupported file format. Please upload a CSV or Excel file.");
        setIsLoading(false);
      }
    } catch (error) {
      console.error("Error processing file:", error);
      toast.error("Failed to process file");
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
