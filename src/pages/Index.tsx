
import { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';

const Index = () => {
  const navigate = useNavigate();

  useEffect(() => {
    // Redirect to login page
    navigate('/login');
  }, [navigate]);

  return (
    <div className="min-h-screen flex items-center justify-center bg-muted">
      <div className="text-center">
        <h1 className="text-4xl font-bold text-primary">Attend-It-All</h1>
        <p className="text-xl text-muted-foreground mt-4">Redirecting to login...</p>
      </div>
    </div>
  );
};

export default Index;
