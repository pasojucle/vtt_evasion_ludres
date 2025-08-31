import { createContext, useContext, useMemo, ReactNode } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import { useLocalStorage } from "@/hooks/useLocalStorage";

interface AuthContextType {
  token: string | undefined;
  login: (token: string) => Promise<void>;
  logout: () => void;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

interface AuthProviderProps {
  children: ReactNode;
}

export const AuthProvider = ({ children }: AuthProviderProps) => {
  const [token, setToken] = useLocalStorage<string | undefined>("token", undefined);
  const navigate = useNavigate();
  const location = useLocation();

  const login = async (newToken: string) => {
    setToken(newToken);
  };

  const logout = () => {
    setToken(undefined);
    navigate('/');
  };

  const value = useMemo<AuthContextType>(
    () => ({
      token,
      login,
      logout,
    }),
    [token]
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};

export const useAuth = (): AuthContextType => {
  const context = useContext(AuthContext);
  if (!context) {
    
    throw new Error("custom error : useAuth must be used within an AuthProvider");
  }
  return context;
};
