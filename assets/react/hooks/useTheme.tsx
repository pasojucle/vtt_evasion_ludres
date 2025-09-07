import React, { createContext, useContext, useMemo, useEffect, ReactNode } from "react";
import { useLocalStorage } from "./useLocalStorage";

interface ThemeContextType {
  theme: string | null;
  projectName: string | null;
  toggleTheme: () => void;
  handleProjectName: (value: string | null) => null;
}

const ThemeContext = createContext<ThemeContextType | undefined>(undefined);

interface ThemeProviderProps {
  children: ReactNode;
}

export const ThemeProvider = ({ children }: ThemeProviderProps): React.JSX.Element => {
  const [theme, setTheme] = useLocalStorage<string | null>("theme", null);
  const [projectName, setProjectName] = useLocalStorage<string | null>("project_name", null);

  useEffect(() => {
    applyTheme(theme);
  }, []);

  const toggleTheme = (): void => {
    const newTheme = theme === null ? "dark" : null;
    setTheme(newTheme);
    applyTheme(newTheme);
  };

  const applyTheme = (newTheme: string | null): void => {
    document.documentElement.classList.toggle(
      "dark",
      newTheme === "dark" ||
        (!("theme" in localStorage) &&
          window.matchMedia("(prefers-color-scheme: dark)").matches)
    );
  };

  const handleProjectName = (value: string | null): null => {
    setProjectName(value);
    return null;
  };

  const value = useMemo<ThemeContextType>(
    () => ({
      theme,
      projectName,
      toggleTheme,
      handleProjectName,
    }),
    [theme, projectName]
  );

  return <ThemeContext.Provider value={value}>{children}</ThemeContext.Provider>;
};

export const useTheme = (): ThemeContextType => {
  const context = useContext(ThemeContext);
  if (!context) {
    throw new Error("useTheme must be used within a ThemeProvider");
  }
  return context;
};
