import React, { useState, useEffect } from "react";
import { Link } from "react-router-dom";
import { Button } from "./ui/button";
import { CirclePlus } from "lucide-react";
import { useArticleAction } from "@/hooks/UseArticleAction";
import { useAuth } from "@/hooks/useAuth";


export default function ButtonSmArticleAction(): React.JSX.Element | undefined {

    const [scrollDir, setScrollDir] = useState<"up" | "down" | null>(null);
    const [lastScrollY, setLastScrollY] = useState(0);
    const { sectionOrigin, chapterOrigin, setAddArticle } = useArticleAction();
    const { token } = useAuth();

    useEffect(() => {
        const handleScroll = () => {
            const currentScrollY = window.scrollY;

            if (currentScrollY > lastScrollY) {
                setScrollDir("down");
            } else if (currentScrollY < lastScrollY) {
                setScrollDir("up");
            }

            setLastScrollY(currentScrollY);
        };

        window.addEventListener("scroll", handleScroll);

        return () => window.removeEventListener("scroll", handleScroll);
    }, [lastScrollY]);

    const Label = (): React.JSX.Element => {
        if (scrollDir === "down") {
            return (
                <>
                    <CirclePlus />
                </>
            )
        }
        return (
            <>
                <CirclePlus />
                Ajouter un article
            </>
        )
    }

    if (!token) {
        return undefined;
    }

    if (chapterOrigin) {
        return (
            <Button className="transition-[width] duration-500" onClick={() => setAddArticle(true)}>
                <Label />
            </Button>
        )
    }

    return (
        <Link to={`/article/add`}>
            <Button className="transition-[width] duration-500"><Label /></Button>
        </Link>
    )
}