import { SectionType } from "@/types/SectionType";
import { ArticleType } from "@/types/ArticleType";

export type ChapterType = {
    '@id'?: string,
    id: number,
    title: string,
    section?: SectionType,
    articles?: ArticleType[],
}