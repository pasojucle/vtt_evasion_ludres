export const dataLoader = async (entity, param, token) => {
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
      console.log(response);
      const httpResponse = response.status
      const jsonResult = await response.json();
      if (response.ok) {
          return {data: jsonResult, httpResponse: httpResponse, error: null};
      } else {
          console.error(jsonResult);
          return {data: null, httpResponse: httpResponse, error: jsonResult.message};
      } 
};

export const dataSender = async (param, data) => {

  try {
    const response = await fetch(`/api/${param}`, {
      method: 'POST',
      body: data,
    });
    console.log('response', response.status);
    if (response.status === 401) {
      return {user: null};
    }

    const jsonResult = await response.json();

    return jsonResult;
  } catch (error) {
    console.error(error.message);
    return {'error': error.message, 'status': error.status}
  }
};