import { ChapterType } from "@/types/ChapterType"

export type SectionType = {
    id: number,
    title: string,
    chapters: ChapterType[],
}