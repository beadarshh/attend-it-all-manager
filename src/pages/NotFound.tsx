
import React from "react";
import { useNavigate } from "react-router-dom";
import { Button } from "@/components/ui/button";
import { useAuth } from "@/context/AuthContext";

const NotFound = () => {
  const navigate = useNavigate();
  const { user } = useAuth();

  const handleRedirect = () => {
    if (user) {
      // Navigate to the appropriate dashboard based on user role
      navigate("/dashboard");
    } else {
      navigate("/login");
    }
  };

  return (
    <div className="flex items-center justify-center min-h-screen bg-muted/20">
      <div className="text-center space-y-6 p-8 max-w-md bg-card rounded-lg shadow-lg">
        <h1 className="text-8xl font-bold text-primary">404</h1>
        <h2 className="text-2xl font-semibold">Page Not Found</h2>
        <p className="text-muted-foreground">
          The page you are looking for doesn't exist or has been moved.
        </p>
        <Button onClick={handleRedirect}>
          Go back to {user ? "Dashboard" : "Login"}
        </Button>
      </div>
    </div>
  );
};

export default NotFound;
