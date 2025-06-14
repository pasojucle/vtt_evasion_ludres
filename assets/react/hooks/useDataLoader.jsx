import { useState, useEffect } from "react";
import { useAuth } from "./useAuth";

export const useDataLoader = (entity, param) => {
    const [data, setData] = useState([]);
    const [error, setError] = useState('');
    const [httpResponse, setHttpResponse] = useState(null)
    const { token } = useAuth();

    useEffect(() => {
        const fetchData = async () => {
            const options = (token)
                ? {headers: {
                    'Authorization': `Bearer ${token}`
                }}
                : {};

            let url = `/api/${entity}`;
            if (param) {
                url += `/${param}`;
            }

            const response = await fetch(url, options);
            setHttpResponse(response.status)
            if (response.ok) {
                const jsonResult = await response.json();
                setData(jsonResult);
            } else {
                console.error(error);
                setError(error);
            }
            
        }
        fetchData();
    }, [entity, param]);

    
    return {data, error, httpResponse};
}