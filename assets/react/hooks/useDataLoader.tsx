import { useState, useEffect } from "react";
import { useAuth } from "@/hooks/useAuth";
import { dataLoader } from "@/helpers/queryHelper";

export const useDataLoader = (entity: string, param?: string | number | undefined) => {
    const [data, setData] = useState<any | null>(null);
    const { getToken, logout } = useAuth();

    useEffect(() => {
        const fetchData = async () => {
            const token =  await getToken();
            let result = await dataLoader(entity, param, token);
            if (result.httpResponse === 401) {
                logout();
            }

            if (result.data) {
                setData(result.data);
            }

            if (result.error) {
                console.error(result.error);
            }
        }

        fetchData();
    }, [entity, param]);


    return data;
}