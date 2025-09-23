import React from "react";
import { Link } from "react-router-dom";
import { Button } from "./ui/button";
import { CirclePlus } from "lucide-react";
import { useArticleAction } from "@/hooks/UseArticleAction";
import { useAuth } from "@/hooks/useAuth";


export default function ButtonArticleAdd(): React.JSX.Element|undefined {

    const {chapterOrigin, setAddArticle} = useArticleAction();
    const { token } = useAuth();

    if (!token) {
        return;
    }

    const Label = (): React.JSX.Element => {
        return (
            <>
                <CirclePlus />
                Ajouter un article
            </>
        )
    }
    if (chapterOrigin) {
        return (
            <Button variant="ghost" size="lg" onClick={() => setAddArticle(true)}>
                <Label />
            </Button>
        )
    }

    return (
        <Link to={`/article/add`}>
            <Button variant="ghost" size="lg"><Label /></Button>
        </Link>
    )
}