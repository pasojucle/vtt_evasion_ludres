import { ChapterType } from "@/types/ChapterType";
import { UserType } from "@/types/UserType"
import { SectionType } from "./SectionType";

export type ArticleType = {
    id?: number,
    title: string,
    content: string,
    chapter: ChapterType | undefined,
    section?: SectionType | undefined,
    user?: UserType
}