import React, { createContext, useContext, useMemo, ReactNode, useState } from "react";
import { SectionType } from "@/types/SectionType";
import { ChapterType } from "@/types/ChapterType";

interface ArticleAddContextType {
    addArticle: boolean;
    sectionOrigin: SectionType | undefined;
    chapterOrigin: ChapterType | undefined;
    setAddArticle: (value:boolean) => void;
    setSectionOrigin: (section:SectionType | undefined) => void;
    setChapterOrigin: (cahapter:ChapterType | undefined) => void;
}

const ArticleAddContext = createContext<ArticleAddContextType | undefined>(undefined);

interface ArticleAddProviderProps {
  children: ReactNode;
}

export const ArticleAddProvider = ({ children }: ArticleAddProviderProps): React.JSX.Element => {
  const [addArticle, setAddArticle] = useState<boolean>(false);
  const [sectionOrigin, setSectionOrigin] = useState<SectionType | undefined>(undefined);
  const [chapterOrigin, setChapterOrigin] = useState<ChapterType | undefined>(undefined);


  const value = useMemo<ArticleAddContextType>(
    () => ({
      addArticle,
      sectionOrigin,
      chapterOrigin,
      setAddArticle,
      setSectionOrigin,
      setChapterOrigin,
    }),
    [addArticle, sectionOrigin, chapterOrigin]
  );

  return <ArticleAddContext.Provider value={value}>{children}</ArticleAddContext.Provider>;
};

export const useArticleAdd = (): ArticleAddContextType => {
  const context = useContext(ArticleAddContext);
  if (!context) {
    throw new Error("useArticleAdd must be used within a ArticleAddProvider");
  }
  return context;
};
