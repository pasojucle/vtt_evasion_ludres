import { RouterProvider } from 'react-router-dom';
import { router } from '../components/Router';
import { ThemeProvider, useTheme } from '../hooks/useTheme';


export default function App({projectName}) { 

  const ProjectName = () => {
    const {handleProjectName} = useTheme();
    handleProjectName(projectName);
  }

  return(
    <ThemeProvider>
      <ProjectName/>
      <RouterProvider router={router} />
    </ThemeProvider>
  );
}

