
import React from "react";
import { Link } from "react-router-dom";
import { Layout } from "@/components/Layout";
import { Button } from "@/components/ui/button";
import { PlusCircle } from "lucide-react";
import { useAuth } from "@/context/AuthContext";
import { useData } from "@/context/DataContext";
import ClassCard from "@/components/ClassCard";

const TeacherDashboard = () => {
  const { user } = useAuth();
  const { getClassesByTeacherId } = useData();

  const classes = user ? getClassesByTeacherId(user.id) : [];

  return (
    <Layout>
      <div className="space-y-6">
        <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <div>
            <h1 className="text-3xl font-bold">Teacher Dashboard</h1>
            <p className="text-muted-foreground mt-1">
              Manage your classes and attendance records
            </p>
          </div>
          <Button asChild>
            <Link to="/add-class">
              <PlusCircle className="h-4 w-4 mr-2" />
              Add New Class
            </Link>
          </Button>
        </div>

        {classes.length > 0 ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {classes.map((classData) => (
              <ClassCard key={classData.id} classData={classData} />
            ))}
          </div>
        ) : (
          <div className="bg-muted rounded-lg p-12 text-center">
            <h3 className="text-lg font-medium mb-2">No classes yet</h3>
            <p className="text-muted-foreground mb-6">
              Start by adding your first class
            </p>
            <Button asChild>
              <Link to="/add-class">
                <PlusCircle className="h-4 w-4 mr-2" />
                Add New Class
              </Link>
            </Button>
          </div>
        )}
      </div>
    </Layout>
  );
};

export default TeacherDashboard;
