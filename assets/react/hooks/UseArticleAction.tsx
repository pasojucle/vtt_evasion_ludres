import React, { createContext, useContext, useMemo, ReactNode, useState } from "react";
import { SectionType } from "@/types/SectionType";
import { ChapterType } from "@/types/ChapterType";
import { ArticleType } from "@/types/ArticleType";

interface ArticleActionContextType {
  addArticle: boolean;
  deleteArticle: ArticleType | null;
  sectionOrigin: SectionType | undefined;
  chapterOrigin: ChapterType | undefined;
  setAddArticle: (value:boolean) => void;
  setDeleteArticle: (article:ArticleType | null) => void;
  setSectionOrigin: (section:SectionType | undefined) => void;
  setChapterOrigin: (cahapter:ChapterType | undefined) => void;
}

const ArticleActionContext = createContext<ArticleActionContextType | undefined>(undefined);

interface ArticleActionProviderProps {
  children: ReactNode;
}

export const ArticleActionProvider = ({ children }: ArticleActionProviderProps): React.JSX.Element => {
  const [addArticle, setAddArticle] = useState<boolean>(false);
  const [deleteArticle, setDeleteArticle] = useState<ArticleType | null>(null);
  const [sectionOrigin, setSectionOrigin] = useState<SectionType | undefined>(undefined);
  const [chapterOrigin, setChapterOrigin] = useState<ChapterType | undefined>(undefined);


  const value = useMemo<ArticleActionContextType>(
    () => ({
      addArticle,
      deleteArticle,
      sectionOrigin,
      chapterOrigin,
      setAddArticle,
      setDeleteArticle,
      setSectionOrigin,
      setChapterOrigin,
    }),
    [addArticle, deleteArticle, sectionOrigin, chapterOrigin]
  );

  return <ArticleActionContext.Provider value={value}>{children}</ArticleActionContext.Provider>;
};

export const useArticleAction = (): ArticleActionContextType => {
  const context = useContext(ArticleActionContext);
  if (!context) {
    throw new Error("useArticleAction must be used within a ArticleActionProvider");
  }
  return context;
};
