import { createContext, useContext, useMemo, useState } from "react";
import { dataLoader } from '../helpers/queryHelper';
const ModalContext = createContext();

export const ModalProvider = ({ children }) => {
  const [shown, setShown] = useState(false);
  const [data, setData] = useState(null);
  const [title, setTitle] = useState('');
  const [component, setComponent] = useState(null);

  const show = async(title, component, api=null) => {
      setTitle(title);
      setComponent(component);
      if (api) {
        await dataLoader(api).then((data) => {
          console.log('modal data', data);
          setData(data);
          setShown(true);
        })
        return;
      }
      setData(null);
      setShown(true);
  };

  const hide = () => {
    setShown(false)
  };

  const value = useMemo(
    () => ({
      shown,
      title,
      data,
      component,
      show,
      hide,
    }),
    [shown]
  );
  return <ModalContext.Provider value={value}>{children}</ModalContext.Provider>;
};

export const useModal= () => {
  return useContext(ModalContext);
};