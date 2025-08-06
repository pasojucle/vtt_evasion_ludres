export type UserType = {
    id: number,
    email: string,
    firstName?: string,
    lastName?: string,
    isActive: boolean,
    roles?: string[],
}