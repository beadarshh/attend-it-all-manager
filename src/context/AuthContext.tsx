
import React, { createContext, useContext, useState, useEffect } from "react";
import { toast } from "sonner";

type User = {
  id: string;
  name: string;
  email: string;
  role: "admin" | "teacher";
};

type AuthContextType = {
  user: User | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  login: (email: string, password: string, role: "admin" | "teacher") => Promise<boolean>;
  signup: (name: string, email: string, password: string, phone: string) => Promise<boolean>;
  logout: () => void;
};

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    // Check if user data exists in local storage
    const storedUser = localStorage.getItem("user");
    if (storedUser) {
      setUser(JSON.parse(storedUser));
    }
    setIsLoading(false);
  }, []);

  const login = async (email: string, password: string, role: "admin" | "teacher"): Promise<boolean> => {
    setIsLoading(true);
    try {
      // In a real application, this would be an API call to authenticate the user
      // For this demo, we'll simulate a successful login

      // Make sure email and password are not empty
      if (!email || !password) {
        toast.error("Please provide email and password");
        setIsLoading(false);
        return false;
      }

      // Simulate API call delay
      await new Promise((resolve) => setTimeout(resolve, 1000));

      // Sample admin credentials
      if (role === "admin" && email === "admin@example.com" && password === "password") {
        const userData: User = {
          id: "admin-123",
          name: "Admin User",
          email: email,
          role: "admin"
        };
        setUser(userData);
        localStorage.setItem("user", JSON.stringify(userData));
        toast.success("Login successful");
        setIsLoading(false);
        return true;
      }

      // Sample teacher credentials
      if (role === "teacher" && email === "teacher@example.com" && password === "password") {
        const userData: User = {
          id: "teacher-123",
          name: "John Doe",
          email: email,
          role: "teacher"
        };
        setUser(userData);
        localStorage.setItem("user", JSON.stringify(userData));
        toast.success("Login successful");
        setIsLoading(false);
        return true;
      }

      toast.error("Invalid credentials");
      setIsLoading(false);
      return false;
    } catch (error) {
      console.error("Login error:", error);
      toast.error("An error occurred during login");
      setIsLoading(false);
      return false;
    }
  };

  const signup = async (name: string, email: string, password: string, phone: string): Promise<boolean> => {
    setIsLoading(true);
    try {
      // In a real application, this would be an API call to register the user
      // For this demo, we'll simulate a successful registration

      // Make sure all fields are filled
      if (!name || !email || !password || !phone) {
        toast.error("Please fill all fields");
        setIsLoading(false);
        return false;
      }

      // Simulate API call delay
      await new Promise((resolve) => setTimeout(resolve, 1000));

      // Create a new user with the teacher role
      const userData: User = {
        id: `teacher-${Date.now()}`,
        name: name,
        email: email,
        role: "teacher"
      };

      setUser(userData);
      localStorage.setItem("user", JSON.stringify(userData));
      toast.success("Registration successful");
      setIsLoading(false);
      return true;
    } catch (error) {
      console.error("Signup error:", error);
      toast.error("An error occurred during signup");
      setIsLoading(false);
      return false;
    }
  };

  const logout = () => {
    setUser(null);
    localStorage.removeItem("user");
    toast.success("Logout successful");
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        isAuthenticated: !!user,
        isLoading,
        login,
        signup,
        logout
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = (): AuthContextType => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error("useAuth must be used within an AuthProvider");
  }
  return context;
};
