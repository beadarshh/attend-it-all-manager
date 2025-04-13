
import React, { useState } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { Button } from "@/components/ui/button";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Checkbox } from "@/components/ui/checkbox";
import { Student } from "@/context/DataContext";

const formSchema = z.object({
  branch: z.string().min(1, "Branch is required"),
  year: z.string().min(1, "Year is required"),
  subject: z.string().min(1, "Subject is required"),
  duration: z.string().min(1, "Duration is required"),
  days: z.array(z.string()).min(1, "At least one day must be selected"),
});

type FormValues = z.infer<typeof formSchema>;

interface ClassFormProps {
  students: Student[];
  teacherId: string;
  teacherName: string;
  onSubmit: (values: FormValues & { students: Student[] }) => void;
}

const ClassForm: React.FC<ClassFormProps> = ({
  students,
  teacherId,
  teacherName,
  onSubmit,
}) => {
  const [selectedDays, setSelectedDays] = useState<string[]>([]);

  const form = useForm<FormValues>({
    resolver: zodResolver(formSchema),
    defaultValues: {
      branch: "",
      year: "",
      subject: "",
      duration: "",
      days: [],
    },
  });

  const handleSubmit = (values: FormValues) => {
    onSubmit({ ...values, students });
  };

  const days = [
    { id: "Monday", label: "Monday" },
    { id: "Tuesday", label: "Tuesday" },
    { id: "Wednesday", label: "Wednesday" },
    { id: "Thursday", label: "Thursday" },
    { id: "Friday", label: "Friday" },
    { id: "Saturday", label: "Saturday" },
    { id: "Sunday", label: "Sunday" },
  ];

  const handleDaySelection = (day: string, checked: boolean) => {
    if (checked) {
      setSelectedDays([...selectedDays, day]);
      form.setValue("days", [...selectedDays, day]);
    } else {
      const filtered = selectedDays.filter((d) => d !== day);
      setSelectedDays(filtered);
      form.setValue("days", filtered);
    }
  };

  return (
    <div className="border rounded-lg p-6 bg-card">
      <h3 className="text-lg font-medium mb-4">Class Details</h3>
      <Form {...form}>
        <form onSubmit={form.handleSubmit(handleSubmit)} className="space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <FormField
              control={form.control}
              name="branch"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Branch</FormLabel>
                  <FormControl>
                    <Input placeholder="e.g. Computer Science" {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="year"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Year</FormLabel>
                  <Select
                    onValueChange={field.onChange}
                    defaultValue={field.value}
                  >
                    <FormControl>
                      <SelectTrigger>
                        <SelectValue placeholder="Select Year" />
                      </SelectTrigger>
                    </FormControl>
                    <SelectContent>
                      <SelectItem value="2023">2023</SelectItem>
                      <SelectItem value="2024">2024</SelectItem>
                      <SelectItem value="2025">2025</SelectItem>
                    </SelectContent>
                  </Select>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="subject"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Subject</FormLabel>
                  <FormControl>
                    <Input placeholder="e.g. Web Development" {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="duration"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Teaching Duration</FormLabel>
                  <FormControl>
                    <Input placeholder="e.g. Jan 2023 - May 2023" {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
          </div>

          <FormField
            control={form.control}
            name="days"
            render={() => (
              <FormItem>
                <FormLabel>Teaching Days</FormLabel>
                <div className="grid grid-cols-2 md:grid-cols-4 gap-2 mt-2">
                  {days.map((day) => (
                    <div key={day.id} className="flex items-center space-x-2">
                      <Checkbox
                        id={day.id}
                        checked={selectedDays.includes(day.id)}
                        onCheckedChange={(checked) =>
                          handleDaySelection(day.id, checked as boolean)
                        }
                      />
                      <label
                        htmlFor={day.id}
                        className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                      >
                        {day.label}
                      </label>
                    </div>
                  ))}
                </div>
                <FormMessage />
              </FormItem>
            )}
          />

          <div className="pt-4">
            <Button type="submit" className="w-full">
              Create Class
            </Button>
          </div>
        </form>
      </Form>
    </div>
  );
};

export default ClassForm;
