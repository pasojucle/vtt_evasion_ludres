import React from "react";
import {
    Card,
    CardContent,
    CardHeader,
} from "@/components/ui/card"
import { Skeleton } from "./ui/skeleton";


export default function CardSkeleton({nomberOfResults}: {nomberOfResults: number | undefined}): React.JSX.Element | undefined {
    const count = 8 - (nomberOfResults ?? 0);

    const Content = (): React.JSX.Element => {
        if (8 === count) {
            return (
                <>
                    {Array.from({ length: 2 }).map((_, index) => (
                        <Skeleton className="h-4 w-full mb-3" />
                    ))}
                    <div className="text-border text-center mb-3">Aucun résultats</div>
                    {Array.from({ length: 2 }).map((_, index) => (
                        <Skeleton className="h-4 w-full mb-3" />
                    ))}
                </>
            )
        }
        return (
            <>
                {Array.from({ length: 5 }).map((_, index) => (
                    <Skeleton className="h-4 w-full mb-3" />
                ))}
            </>
        )
    }


    return (
        <>
            {Array.from({ length: count }).map((_, index) => (
                <Card key={index} className="bg-background">
                    <CardHeader>
                        <Skeleton className="h-5 w-48 rounded-full" />
                    </CardHeader>
                    <CardContent>
                        <Content />
                    </CardContent>
                </Card>
            ))}
        </>
    )
}