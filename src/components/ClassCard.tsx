
import React from "react";
import { useNavigate } from "react-router-dom";
import { Calendar, Users, BookOpen } from "lucide-react";
import { Class } from "@/context/DataContext";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";

interface ClassCardProps {
  classData: Class;
}

const ClassCard: React.FC<ClassCardProps> = ({ classData }) => {
  const navigate = useNavigate();

  return (
    <Card className="h-full flex flex-col">
      <CardHeader>
        <CardTitle className="flex justify-between items-start">
          <span>{classData.subject}</span>
          <Badge>{classData.year}</Badge>
        </CardTitle>
      </CardHeader>
      <CardContent className="flex-grow">
        <div className="space-y-4">
          <div className="flex items-center text-muted-foreground">
            <BookOpen className="h-4 w-4 mr-2" />
            <span>{classData.branch}</span>
          </div>
          <div className="flex items-center text-muted-foreground">
            <Calendar className="h-4 w-4 mr-2" />
            <span>{classData.duration}</span>
          </div>
          <div className="flex items-center text-muted-foreground">
            <Users className="h-4 w-4 mr-2" />
            <span>{classData.students.length} Students</span>
          </div>
          <div className="flex flex-wrap gap-1 mt-2">
            {classData.days.map((day) => (
              <Badge key={day} variant="outline">
                {day}
              </Badge>
            ))}
          </div>
        </div>
      </CardContent>
      <CardFooter>
        <div className="w-full space-y-2">
          <Button
            onClick={() => navigate(`/mark-attendance/${classData.id}`)}
            className="w-full"
          >
            Mark Attendance
          </Button>
          <Button
            variant="outline"
            onClick={() => navigate(`/attendance-history/${classData.id}`)}
            className="w-full"
          >
            View Attendance History
          </Button>
        </div>
      </CardFooter>
    </Card>
  );
};

export default ClassCard;
