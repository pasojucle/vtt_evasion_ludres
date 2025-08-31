import { ChapterType } from "@/types/ChapterType"

export type SectionType = {
    '@id'?: string,
    id: number,
    title: string,
    chapters: ChapterType[],
}