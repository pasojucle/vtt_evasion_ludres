import { ChapterType } from "@/types/ChapterType";
import { UserType } from "@/types/UserType"

export type ArticleType = {
    id: number,
    title: string,
    content: string,
    chapter: ChapterType,
    user?: UserType
}