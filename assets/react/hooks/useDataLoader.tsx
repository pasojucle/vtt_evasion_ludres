import { useState, useEffect } from "react";
import { useAuth } from "@/hooks/useAuth";
import { dataLoader } from "@/helpers/queryHelper";

export const useDataLoader = (entity: string, param?: string | number | undefined) => {
    const [data, setData] = useState<any|null>(null);
    const [error, setError] = useState<string|null>('');
    const [httpResponse, setHttpResponse] = useState<number|null>(null)
    const { token } = useAuth();
    useEffect(() => {
        const fetchData = async() => {
            await dataLoader(entity, param, token).then((result) => {
                console.log('result', result);
                setData(result.data);
                setError(result.error);
                setHttpResponse(result.httpResponse);
            })
        }

        fetchData();
    }, [entity, param]);

    
    return {data, error, httpResponse};
}