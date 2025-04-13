
import React, { createContext, useContext, useState, useEffect } from "react";
import { toast } from "sonner";

type User = {
  id: string;
  name: string;
  email: string;
  role: "teacher";
  phone?: string;
};

type AuthContextType = {
  user: User | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  login: (email: string, password: string) => Promise<boolean>;
  signup: (name: string, email: string, password: string, phone: string) => Promise<boolean>;
  updateProfile: (name: string, email: string, phone: string) => Promise<boolean>;
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

  const login = async (email: string, password: string): Promise<boolean> => {
    setIsLoading(true);
    try {
      // Make sure email and password are not empty
      if (!email || !password) {
        toast.error("Please provide email and password");
        setIsLoading(false);
        return false;
      }

      // Simulate API call delay
      await new Promise((resolve) => setTimeout(resolve, 1000));

      // Sample teacher credentials
      if (email === "teacher@example.com" && password === "password") {
        const userData: User = {
          id: "teacher-123",
          name: "John Doe",
          email: email,
          role: "teacher",
          phone: "1234567890"
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
        role: "teacher",
        phone: phone
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

  const updateProfile = async (name: string, email: string, phone: string): Promise<boolean> => {
    try {
      if (!user) {
        toast.error("You must be logged in to update profile");
        return false;
      }

      // Simulate API call delay
      await new Promise((resolve) => setTimeout(resolve, 1000));

      // Update user data
      const updatedUser: User = {
        ...user,
        name,
        email,
        phone
      };

      setUser(updatedUser);
      localStorage.setItem("user", JSON.stringify(updatedUser));
      toast.success("Profile updated successfully");
      return true;
    } catch (error) {
      console.error("Profile update error:", error);
      toast.error("An error occurred while updating profile");
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
        updateProfile,
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
