import { useEffect } from 'react';
import { RouterProvider } from 'react-router-dom';
import { router } from '../components/Router';
import { ThemeProvider, useTheme } from '../hooks/useTheme';


function InitProjectName({ projectName }: { projectName: string }) {
  const { handleProjectName } = useTheme();

  useEffect(() => {
    handleProjectName(projectName);
  }, [projectName]);

  return null;
}


export default function App({projectName}: {projectName: string}): React.JSX.Element { 

  return(
    <ThemeProvider>
      <InitProjectName projectName={projectName}/>
      <RouterProvider router={router} />
    </ThemeProvider>
  );
}

