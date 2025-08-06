import { RouterProvider } from 'react-router-dom';
import { router } from '../components/Router';
import { ThemeProvider, useTheme } from '../hooks/useTheme';


export default function App({projectName}: {projectName: string}): React.JSX.Element { 

  const ProjectName = (): null  => {
    const {handleProjectName} = useTheme();
    return handleProjectName(projectName);

  }

  return(
    <ThemeProvider>
      <ProjectName/>
      <RouterProvider router={router} />
    </ThemeProvider>
  );
}

