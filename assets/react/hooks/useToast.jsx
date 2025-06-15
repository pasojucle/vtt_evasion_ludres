import { createContext, useContext, useMemo, useState } from "react";
const ToastContext = createContext();

export const ToastProvider = ({ children }) => {
  const [shown, setShown] = useState(false);
  const [message, setMessage] = useState(null);
  const [type, setType] = useState('default');


  const showToast = (message, type='default') => {
    console.log('showToast', type)
      setType(type);
      setMessage(message);
      setShown(true);
      setTimeout(() => {
        setShown(false);
      }, 5000)
  };

  const hideToast = () => {
    setShown(false)
  };

  const value = useMemo(
    () => ({
      shown,
      type,
      message,
      showToast,
      hideToast,
    }),
    [shown]
  );
  return <ToastContext.Provider value={value}>{children}</ToastContext.Provider>;
};

export const useToast = () => {
  return useContext(ToastContext);
};