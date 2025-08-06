import React, { createContext, useContext, useMemo, useState, ReactNode, ComponentType } from "react";
import { useAuth } from "./useAuth";
import { useToast } from "./useToast";
import { dataLoader } from "../helpers/queryHelper";

interface ModalContextType {
  shown: boolean;
  title: string;
  data: any;
  component: string|null;
  size: string;
  show: (
    title: string,
    component: string|null,
    size: string,
    entity?: string | null,
    param?: any
  ) => Promise<void>;
  hide: () => void;
}

const ModalContext = createContext<ModalContextType | undefined>(undefined);

interface ModalProviderProps {
  children: ReactNode;
}
export const ModalProvider = ({ children }: ModalProviderProps): React.JSX.Element => {
  const [shown, setShown] = useState<boolean>(false);
  const [data, setData] = useState<any>(null);
  const [title, setTitle] = useState<string>('');
  const [component, setComponent] = useState<string|null>(null);
  const [size, setSize] = useState<string>('sm');

  const { token } = useAuth();

  const { showToast } = useToast();

  const show = async (
    title: string,
    component: string|null,
    size: string,
    entity: string | null = null,
    param: any = null
  ) => {
    setTitle(title);
    setComponent(component);
    setSize(size);

    if (entity && token) {
      try {
        const response = await dataLoader(entity, param, token);
        if (response.data) {
          setData(response.data);
          setShown(true);
        } else if (response.error) {
          showToast(response.error, 'error');
        }
      } catch (error) {
        showToast("Erreur de chargement des données", "error");
      }
      return;
    }

    setData(null);
    setShown(true);
  };

  const hide = () => {
    setShown(false);
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
    [shown, title, data, component, size]
  );

  return <ModalContext.Provider value={value}>{children}</ModalContext.Provider>;
};
export const useModal = (): ModalContextType => {
  const context = useContext(ModalContext);
  if (!context) {
    throw new Error("222 useModal must be used within ModalProvider");
  }
  return context;
};
