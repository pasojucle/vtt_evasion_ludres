import { useState, useEffect } from "react";
import { useAuth } from "@/hooks/useAuth";
import { dataLoader } from "@/helpers/queryHelper";

export const useDataLoader = (entity: string, param?: string | number | undefined) => {
    const [data, setData] = useState<any | null>(null);
    const { token } = useAuth();

    useEffect(() => {
        const fetchData = async () => {
            await dataLoader(entity, param, token).then((result) => {
                setData(result.data);

                if (result.error) {
                    console.error(result.error);
                }
            })
        }

        fetchData();
    }, [entity, param]);


    return data;
}