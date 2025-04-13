
import React from "react";
import { Button } from "@/components/ui/button";
import { Link } from "react-router-dom";
import { useAuth } from "@/context/AuthContext";

const NotFound = () => {
  const { isAuthenticated, user } = useAuth();

  const getDashboardUrl = () => {
    if (!isAuthenticated) return "/login";
    return user?.role === "admin" ? "/admin" : "/dashboard";
  };

  return (
    <div className="min-h-screen flex flex-col items-center justify-center bg-muted p-6 text-center">
      <h1 className="text-6xl font-bold text-primary">404</h1>
      <p className="text-xl mt-4 mb-8">
        Oops! The page you're looking for doesn't exist.
      </p>
      <Button asChild>
        <Link to={getDashboardUrl()}>
          Go back to {isAuthenticated ? "dashboard" : "login"}
        </Link>
      </Button>
    </div>
  );
};

export default NotFound;
