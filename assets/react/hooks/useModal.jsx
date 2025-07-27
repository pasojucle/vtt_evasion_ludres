import { createContext, useContext, useMemo, useState } from "react";
import { useAuth } from "./useAuth";
import { useToast } from "./useToast";
import { dataLoader } from '../helpers/queryHelper';
const ModalContext = createContext();

export const ModalProvider = ({ children }) => {
  const [shown, setShown] = useState(false);
  const [data, setData] = useState(null);
  const [title, setTitle] = useState('');
  const [component, setComponent] = useState(null);
  const [size, setSize] = useState('sm');
  const { token } = useAuth();
  const { showToast } = useToast();

  const show = async(title, component, size, entity=null, param=null) => {
      setTitle(title);
      setComponent(component);
      setSize(size);
      console.log('entity', entity)
      if (entity) {
        await dataLoader(entity, param, token).then((response) => {
          console.log('response', response)
          if (response.data) {
            setData(response.data);
            setShown(true);
          } else {
            showToast(response.error, 'error')
          }
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
      size,
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