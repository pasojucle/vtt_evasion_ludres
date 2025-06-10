import { createContext, useContext, useMemo } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import { useLocalStorage } from "./useLocalStorage";

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [token, setToken] = useLocalStorage("token", null);
  const navigate = useNavigate();
  let location = useLocation();

  const login = async (token) => {
    setToken(token);
    if (location.pathname.includes('login')) {
      navigate('/');
    }
    navigate(location);
  };

  const logout = () => {
    setToken(null);
    navigate('/');
  };

  const value = useMemo(
    () => ({
      token,
      login,
      logout,
    }),
    [token]
  );
  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};

export const useAuth = () => {
  return useContext(AuthContext);
};