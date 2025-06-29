import { createContext, useContext, useMemo, useEffect } from "react";
import { useLocalStorage } from "./useLocalStorage";

const ThemeContext = createContext();

export const ThemeProvider = ({ children }) => {
  const [theme, setTheme] = useLocalStorage("theme", null);
  const [projectName, setProjectName] = useLocalStorage("project_name", null);

  useEffect(() => {
      applyTheme(theme);
      console.log('applyMode', theme)
  }, [])

  const toggleTheme = () => {
    const newTheme = theme === null ? 'dark' : null;
    setTheme(newTheme);
    applyTheme(newTheme);
  };

  const applyTheme = (newTeme) => {
    document.documentElement.classList.toggle( "dark", newTeme === "dark" || (!("theme" in localStorage) && window.matchMedia("(prefers-color-scheme: dark)").matches),);
  }

  const handleProjectName = (value) => {
    setProjectName(value);
  }

  const value = useMemo(
    () => ({
      theme,
      projectName,
      toggleTheme,
      handleProjectName
    }),
    [theme, projectName]
  );
  return <ThemeContext.Provider value={value}>{children}</ThemeContext.Provider>;
};

export const useTheme = () => {
  return useContext(ThemeContext);
};