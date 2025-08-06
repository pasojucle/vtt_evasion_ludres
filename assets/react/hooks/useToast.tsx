import React, {
  createContext,
  useContext,
  useMemo,
  useState,
  ReactNode,
} from 'react';
import { ToastType } from '@/types/ToastType';

interface ToastContextType {
  shown: boolean;
  message: string | null;
  type: ToastType;
  showToast: (message: string, type?: ToastType) => void;
  hideToast: () => void;
}

interface ToastProviderProps {
  children: ReactNode;
}

const ToastContext = createContext<ToastContextType | undefined>(undefined);

export const ToastProvider: React.FC<ToastProviderProps> = ({ children }) => {
  const [shown, setShown] = useState<boolean>(false);
  const [message, setMessage] = useState<string | null>(null);
  const [type, setType] = useState<ToastType>('default');

  const showToast = (message: string, type: ToastType = 'default') => {
    console.log('showToast', type);
    setType(type);
    setMessage(message);
    setShown(true);
    setTimeout(() => {
      setShown(false);
    }, 5000);
  };

  const hideToast = () => {
    setShown(false);
  };

  const value = useMemo<ToastContextType>(
    () => ({
      shown,
      type,
      message,
      showToast,
      hideToast,
    }),
    [shown, type, message]
  );

  return <ToastContext.Provider value={value}>{children}</ToastContext.Provider>;
};

export const useToast = (): ToastContextType => {
  const context = useContext(ToastContext);
  if (!context) {
    throw new Error('useToast must be used within a ToastProvider');
  }
  return context;
};
