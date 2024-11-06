export const checkStatus = (response) => {
    if(response.status !== 500) {
         return response;    
    }
    throw new Error('Something went wrong.');    
}

export const isJsonResponse = (response) => {
    const contentType = response.headers.get("content-type");
    if (contentType && contentType.indexOf("application/json") !== -1) {
         return response;    
    }
    throw new Error('Something went wrong.');    
}