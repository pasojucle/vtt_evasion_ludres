import { createContext, useContext, useMemo } from "react";
import { useNavigate, replace, useLocation } from "react-router-dom";
import { useLocalStorage } from "./useLocalStorage";
const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  console.log(useLocalStorage("user", null))
  const [user, setUser] = useLocalStorage("user", null);
  const navigate = useNavigate();
  let location = useLocation();

  // call this function when you want to authenticate the user
  const login = async (data) => {
    setUser(data);
    console.log('useLocation', location, location.pathname.includes('login'))
    if (location.pathname.includes('login')) {
      navigate({ pathname: "/"})
    }
    navigate(location);
  };

  // call this function to sign out logged in user
  const logout = () => {
    setUser(null);
    navigate({ pathname: "/"})
  };

  const value = useMemo(
    () => ({
      user,
      login,
      logout,
    }),
    [user]
  );
  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};

export const useAuth = () => {
  return useContext(AuthContext);
};