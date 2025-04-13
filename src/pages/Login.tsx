
import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import { zodResolver } from "@hookform/resolvers/zod";
import { useForm } from "react-hook-form";
import { z } from "zod";
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import {
  Tabs,
  TabsContent,
  TabsList,
  TabsTrigger,
} from "@/components/ui/tabs";
import { useAuth } from "@/context/AuthContext";
import { Link } from "react-router-dom";

const loginTeacherSchema = z.object({
  email: z.string().email("Invalid email address"),
  password: z.string().min(6, "Password must be at least 6 characters"),
});

const loginAdminSchema = z.object({
  name: z.string().min(2, "Name must be at least 2 characters"),
  adminCode: z.string().min(6, "Admin code is required"),
});

const signupSchema = z.object({
  name: z.string().min(2, "Name must be at least 2 characters"),
  email: z.string().email("Invalid email address"),
  password: z.string().min(6, "Password must be at least 6 characters"),
  phone: z.string().min(10, "Phone number must be at least 10 characters"),
});

type LoginTeacherValues = z.infer<typeof loginTeacherSchema>;
type LoginAdminValues = z.infer<typeof loginAdminSchema>;
type SignupValues = z.infer<typeof signupSchema>;

const Login = () => {
  const [role, setRole] = useState<"admin" | "teacher">("admin");
  const [activeTab, setActiveTab] = useState("login");
  const { login, loginAdmin, signup } = useAuth();
  const navigate = useNavigate();

  const loginTeacherForm = useForm<LoginTeacherValues>({
    resolver: zodResolver(loginTeacherSchema),
    defaultValues: {
      email: "",
      password: "",
    },
  });

  const loginAdminForm = useForm<LoginAdminValues>({
    resolver: zodResolver(loginAdminSchema),
    defaultValues: {
      name: "",
      adminCode: "",
    },
  });

  const signupForm = useForm<SignupValues>({
    resolver: zodResolver(signupSchema),
    defaultValues: {
      name: "",
      email: "",
      password: "",
      phone: "",
    },
  });

  const onLoginSubmit = async (values: LoginTeacherValues | LoginAdminValues) => {
    if (role === "admin") {
      const adminValues = values as LoginAdminValues;
      const success = await loginAdmin(adminValues.name, adminValues.adminCode);
      if (success) {
        navigate("/admin");
      }
    } else {
      const teacherValues = values as LoginTeacherValues;
      const success = await login(teacherValues.email, teacherValues.password, role);
      if (success) {
        navigate("/dashboard");
      }
    }
  };

  const onSignupSubmit = async (values: SignupValues) => {
    const success = await signup(
      values.name,
      values.email,
      values.password,
      values.phone
    );
    if (success) {
      navigate("/dashboard");
    }
  };

  return (
    <div className="min-h-screen flex flex-col items-center justify-center bg-muted p-4">
      <div className="max-w-md w-full">
        <div className="text-center mb-8">
          <h1 className="text-3xl font-bold text-primary">Attend-It-All</h1>
          <p className="text-muted-foreground mt-2">
            Attendance Management System
          </p>
        </div>

        <Card className="w-full">
          <CardHeader>
            <CardTitle>Welcome back</CardTitle>
            <CardDescription>
              Sign in to your account to continue
            </CardDescription>
          </CardHeader>
          <CardContent>
            <Tabs value={activeTab} onValueChange={setActiveTab}>
              <TabsList className="grid w-full grid-cols-2 mb-6">
                <TabsTrigger value="login">Login</TabsTrigger>
                <TabsTrigger value="signup" disabled={role === "admin"}>
                  Signup
                </TabsTrigger>
              </TabsList>

              <div className="mb-6">
                <div className="text-sm font-medium mb-2">Select Role</div>
                <div className="flex space-x-2">
                  <Button
                    type="button"
                    variant={role === "admin" ? "default" : "outline"}
                    onClick={() => {
                      setRole("admin");
                      if (activeTab === "signup") setActiveTab("login");
                    }}
                    className="flex-1"
                  >
                    Admin
                  </Button>
                  <Button
                    type="button"
                    variant={role === "teacher" ? "default" : "outline"}
                    onClick={() => setRole("teacher")}
                    className="flex-1"
                  >
                    Teacher
                  </Button>
                </div>
              </div>

              <TabsContent value="login">
                {role === "admin" ? (
                  <Form {...loginAdminForm}>
                    <form
                      onSubmit={loginAdminForm.handleSubmit(onLoginSubmit)}
                      className="space-y-4"
                    >
                      <FormField
                        control={loginAdminForm.control}
                        name="name"
                        render={({ field }) => (
                          <FormItem>
                            <FormLabel>Admin Name</FormLabel>
                            <FormControl>
                              <Input placeholder="Enter admin name" {...field} />
                            </FormControl>
                            <FormMessage />
                          </FormItem>
                        )}
                      />
                      <FormField
                        control={loginAdminForm.control}
                        name="adminCode"
                        render={({ field }) => (
                          <FormItem>
                            <FormLabel>Admin Code</FormLabel>
                            <FormControl>
                              <Input
                                type="password"
                                placeholder="Enter admin code (232774)"
                                {...field}
                              />
                            </FormControl>
                            <FormMessage />
                          </FormItem>
                        )}
                      />
                      <Button type="submit" className="w-full">
                        Sign In
                      </Button>
                    </form>
                  </Form>
                ) : (
                  <Form {...loginTeacherForm}>
                    <form
                      onSubmit={loginTeacherForm.handleSubmit(onLoginSubmit)}
                      className="space-y-4"
                    >
                      <FormField
                        control={loginTeacherForm.control}
                        name="email"
                        render={({ field }) => (
                          <FormItem>
                            <FormLabel>Email</FormLabel>
                            <FormControl>
                              <Input placeholder="Enter your email" {...field} />
                            </FormControl>
                            <FormMessage />
                          </FormItem>
                        )}
                      />
                      <FormField
                        control={loginTeacherForm.control}
                        name="password"
                        render={({ field }) => (
                          <FormItem>
                            <FormLabel>Password</FormLabel>
                            <FormControl>
                              <Input
                                type="password"
                                placeholder="Enter your password"
                                {...field}
                              />
                            </FormControl>
                            <FormMessage />
                          </FormItem>
                        )}
                      />
                      <Button type="submit" className="w-full">
                        Sign In
                      </Button>
                    </form>
                  </Form>
                )}
              </TabsContent>

              <TabsContent value="signup">
                <Form {...signupForm}>
                  <form
                    onSubmit={signupForm.handleSubmit(onSignupSubmit)}
                    className="space-y-4"
                  >
                    <FormField
                      control={signupForm.control}
                      name="name"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Name</FormLabel>
                          <FormControl>
                            <Input placeholder="Enter your name" {...field} />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                    <FormField
                      control={signupForm.control}
                      name="email"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Email</FormLabel>
                          <FormControl>
                            <Input placeholder="Enter your email" {...field} />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                    <FormField
                      control={signupForm.control}
                      name="password"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Password</FormLabel>
                          <FormControl>
                            <Input
                              type="password"
                              placeholder="Create a password"
                              {...field}
                            />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                    <FormField
                      control={signupForm.control}
                      name="phone"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Phone</FormLabel>
                          <FormControl>
                            <Input
                              placeholder="Enter your phone number"
                              {...field}
                            />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                    <Button type="submit" className="w-full">
                      Create Account
                    </Button>
                  </form>
                </Form>
              </TabsContent>
            </Tabs>
          </CardContent>
          <CardFooter className="flex justify-center">
            <p className="text-sm text-muted-foreground">
              For testing, use:{" "}
              <span className="font-medium">
                admin@example.com / teacher@example.com
              </span>{" "}
              with password:{" "}
              <span className="font-medium">password</span>
            </p>
          </CardFooter>
        </Card>
      </div>
    </div>
  );
};

export default Login;
