import React, { createContext, useContext, useMemo, ReactNode, useState, useEffect } from "react";
import { SectionType } from "@/types/SectionType";
import { ChapterType } from "@/types/ChapterType";
import { useAuth } from '../hooks/useAuth';
import { dataLoader } from '@/helpers/queryHelper';

interface ArticleActionContextType {
  openArticleSheet: boolean;
  editArticle: number | null;
  section: SectionType | undefined;
  chapter: ChapterType | undefined;
  currentChapter: ChapterType | undefined;
  sections: SectionType[];
  chapters: ChapterType[];
  setOpenArticleSheet: (value: boolean) => void;
  setEditArticle: (value: number | null) => void;
  setSection: (section: SectionType | undefined) => void;
  setCurrentChapter: (chapter: ChapterType | undefined) => void;
  setChapter: (chapter: ChapterType | undefined) => void;
  loadChapter: (id: string | number | undefined) => void;
  loadChapters: (section: SectionType) => void;
  handleSelectSection: (title: string | undefined) => void;
  handleSelectChapter: (title: string | undefined) => void;
  handleAddSection: (title: string) => void;
  handleAddChapter: (title: string) => void;
}

const ArticleActionContext = createContext<ArticleActionContextType | undefined>(undefined);

interface ArticleActionProviderProps {
  children: ReactNode;
}

export const ArticleActionProvider = ({ children }: ArticleActionProviderProps): React.JSX.Element => {
  const [openArticleSheet, setOpenArticleSheet] = useState<boolean>(false);
  const [editArticle, setEditArticle] = useState<number | null>(null);
  const [section, setSection] = useState<SectionType | undefined>(undefined);
  const [pendingSection, setPendingSection] = useState<string | null>(null);
  const [chapter, setChapter] = useState<ChapterType | undefined>(undefined);
  const [pendingChapter, setPendingChapter] = useState<string | null>(null);
  const [currentChapter, setCurrentChapter] = useState<ChapterType | undefined>(undefined);
  const [sections, setSections] = useState<SectionType[]>([]);
  const [chapters, setChapters] = useState<ChapterType[]>([]);
  const { getToken } = useAuth();

  useEffect(() => {
    if (pendingSection) {
      const exists = sections.some((s) => String(s.id) === pendingSection);
      if (exists) {
        handleSelectSection(pendingSection);
        setPendingSection(null);
      }
    }
  }, [sections, pendingSection]);

  useEffect(() => {
    if (pendingChapter) {
      const exists = chapters.some((c) => String(c.id) === pendingChapter);
      if (exists) {
        handleSelectChapter(pendingChapter);
        setPendingChapter(null);
      }
    }
  }, [chapters, pendingChapter]);

  const loadSections = async () => {
    const accessToken = await getToken();
    const result = await dataLoader("sections", undefined, accessToken);
    setSections(result.data.member);
  }

  const loadChapters = async (section: SectionType | undefined) => {
    if (undefined !== section?.id) {
      const accessToken = await getToken();
      const result = await dataLoader(`chapters?section.id=${section.id}`, undefined, accessToken);
      setChapters(result.data.member);
    }
  }

  const loadChapter = async (id: string | number | undefined) => {
    if (id) {
      const result = await dataLoader(`chapters/${id}`);
      setChapter(result.data);
      setCurrentChapter(result.data);
      setSection(result.data.section);
    }
  }

  const handleSelectSection = (value: string | undefined) => {
    const currentSection = sections.find((s: SectionType) => s.id === Number(value));
    console.log("currentSection", currentSection)
    setSection(currentSection)
    setChapter(undefined)
  }

  const handleSelectChapter = (value: string | undefined) => {
    const currentChapter = chapters.find((c: SectionType) => c.id === Number(value));
    console.log("currentChapter", currentChapter, chapters)
    setChapter(currentChapter)
  }

  const handleAddSection = (title: string) => {
    console.log('handleAddSection', title, sections)
    const sectionToAdd: SectionType = { id: -1, title: title }
    setSections((prevSections) => {
      const index = prevSections.findIndex((section) => section.id === -1)
      if (index > -1) {
        const updated = [...prevSections]
        updated.splice(index, 1, sectionToAdd)
        return updated
      }

      return [...prevSections, sectionToAdd]
    })
    setPendingSection("-1");
  }

  const handleAddChapter = (title: string) => {
    console.log('handleAddChapter', title)
    const chapterToAdd: ChapterType = { id: -1, title: title }
    setChapters((prevChapters) => {
      const index = prevChapters.findIndex((chapter) => chapter.id === -1)
      console.log('handleAddChapter index', index)
      if (index > -1) {
        const updated = [...prevChapters]
        updated.splice(index, 1, chapterToAdd)
        return updated
      }

      return [...prevChapters, chapterToAdd]
    })
    setPendingChapter('-1');
  }

  const value = useMemo<ArticleActionContextType>(
    () => ({
      openArticleSheet,
      editArticle,
      section,
      chapter,
      currentChapter,
      sections,
      chapters,
      setOpenArticleSheet,
      setEditArticle,
      setSection,
      setChapter,
      setCurrentChapter,
      loadChapter,
      loadChapters,
      handleSelectSection,
      handleSelectChapter,
      handleAddSection,
      handleAddChapter,
    }),
    [openArticleSheet, editArticle, section, chapter]
  );


  useEffect(() => {
    console.log("load sections");
    loadSections();
  }, [])

  useEffect(() => {
    console.log("loadChapters section", section);
    if (chapter?.id !== -1) {
      loadChapters(section);
    }
  }, [section])



  return <ArticleActionContext.Provider value={value}>{children}</ArticleActionContext.Provider>;
};

export const useArticleAction = (): ArticleActionContextType => {
  const context = useContext(ArticleActionContext);
  if (!context) {
    throw new Error("useArticleAction must be used within a ArticleActionProvider");
  }
  return context;
};
