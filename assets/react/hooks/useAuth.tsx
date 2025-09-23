import { createContext, useContext, useMemo, ReactNode, useEffect, useCallback } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import { useLocalStorage } from "@/hooks/useLocalStorage";

interface AuthContextType {
  token: string | undefined;
  login: (data: { token: string, refresh_token: string }) => Promise<void>;
  logout: () => void;
  getToken: () => Promise<string | undefined>,
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

interface AuthProviderProps {
  children: ReactNode;
}

export const AuthProvider = ({ children }: AuthProviderProps) => {
  const [token, setToken] = useLocalStorage<string | undefined>("token", undefined);
  const [refreshToken, setRefreshToken] = useLocalStorage<string | undefined>("refresh_token", undefined);
  const navigate = useNavigate();
  const location = useLocation();

  const login = async (data: { token: string, refresh_token: string }): Promise<void> => {
    setToken(data.token);
    setRefreshToken(data.refresh_token);
  };

  const logout = () => {
    setToken(undefined);
    navigate('/');
  };

  const getToken = async (): Promise<string | undefined> => {
    if (shouldRefreshTokenSoon(token)) {
      const newToken = await renewAccessToken();
      return newToken;
    }
    return token;
  }

  const abord = (): boolean => {
    logout();
    return false;
  }

  const shouldRefreshTokenSoon = (token: string | undefined, thresholdSeconds = 120): boolean => {
    if (!token) return abord();
    const parts = token.split('.');
    if (parts.length !== 3) {
      throw new Error('Le jeton JWT n\'est pas valide.');
    }

    try {
      const payload = JSON.parse(atob(parts[1]));
      if (payload.exp) {
        const now = Math.floor(Date.now() / 1000);
        return payload.exp - now <= thresholdSeconds;
      }
    } catch (e) {
      throw new Error('Impossible de décoder le payload du jeton.');
    }
    return abord();
  };

  const renewAccessToken = useCallback(async (): Promise<string | undefined> => {
    console.log('renewAccessToken')
    try {
      const response = await fetch("/api/token/refresh", {
        method: "POST",
        credentials: "include",
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ "refresh_token": refreshToken })
      });

      if (!response.ok) throw new Error("Refresh token failed");

      const data = await response.json();
      if (data.accessToken) {
        setToken(data.accessToken);
        return data.accessToken;
      }
    } catch (err) {
      console.error("Erreur de renouvellement :", err);
      logout();
      return undefined;
    }
    logout();
    return undefined;
  }, [setToken, logout]);

  useEffect(() => {
    if (!token) return;

    const interval = setInterval(() => {
      renewAccessToken();
    }, 13 * 60 * 1000);

    return () => clearInterval(interval);
  }, [token, refreshToken]);

  const value = useMemo<AuthContextType>(
    () => ({
      token,
      login,
      logout,
      getToken,
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
