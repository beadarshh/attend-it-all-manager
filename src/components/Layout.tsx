
import React from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "@/context/AuthContext";
import { Button } from "@/components/ui/button";
import { LogOut, UserCircle } from "lucide-react";

interface LayoutProps {
  children: React.ReactNode;
}

const Layout: React.FC<LayoutProps> = ({ children }) => {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = () => {
    logout();
    navigate("/login");
  };

  return (
    <div className="min-h-screen flex flex-col">
      {user && (
        <header className="bg-primary py-4 px-6 text-primary-foreground shadow-md">
          <div className="container mx-auto flex justify-between items-center">
            <div className="flex items-center space-x-4">
              <h1 className="text-2xl font-bold">Attend-It-All</h1>
              <span className="text-sm bg-white/20 px-3 py-1 rounded-full">{user.role === "admin" ? "Admin" : "Teacher"}</span>
            </div>
            <div className="flex items-center space-x-4">
              <div className="flex items-center space-x-2">
                <UserCircle className="h-6 w-6" />
                <span>{user.name}</span>
              </div>
              <Button variant="secondary" size="sm" onClick={handleLogout}>
                <LogOut className="h-4 w-4 mr-2" />
                Logout
              </Button>
            </div>
          </div>
        </header>
      )}
      <main className="flex-1 py-8 px-4">
        <div className="container mx-auto">
          {children}
        </div>
      </main>
      <footer className="bg-muted py-4 text-center text-muted-foreground">
        <div className="container mx-auto">
          <p>&copy; {new Date().getFullYear()} Attend-It-All Management System</p>
        </div>
      </footer>
    </div>
  );
};

export default Layout;
