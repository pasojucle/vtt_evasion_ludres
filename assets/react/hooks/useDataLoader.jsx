import { useState, useEffect } from "react";
import { useAuth } from "./useAuth";
import { dataLoader} from "../helpers/queryHelper";

export const useDataLoader = (entity, param) => {
    const [data, setData] = useState(null);
    const [error, setError] = useState('');
    const [httpResponse, setHttpResponse] = useState(null)
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