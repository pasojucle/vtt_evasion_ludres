export const dataLoader = async (entity, param, token) => {
      const response = await fetch(url(entity, param), options(token));
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

export const dataSender = async (method, entity, param, token, data) => {

  try {
    const response = await fetch(url(entity, param), options(token, method, data));
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

const url = (entity, param) => {
    let url = `/api/${entity}`;
    if (param) {
        url += `/${param}`;
    }
    return url;
}

const options = (token, method='GET', data = null) => {
  let options = {};
  if (token) {
    options['headers'] = {
      'Authorization': `Bearer ${token}`
    }
  }

  if (method === 'POST') {
    options['method'] = 'POST';
    options['headers']['Content-Type'] = 'application/json';
  }

  if (method === 'FILE') {
    options['method'] = 'PATCH';
    options['headers']['Content-Type'] = 'multipart/form-data';
    options['headers']['Accept'] = 'application/ld+json';
  }

  if (method === 'PATCH') {
    options['method'] = 'PATCH';
    options['headers']['Content-Type'] = 'application/merge-patch+json';
    options['headers']['Accept'] = 'application/ld+json';
  }
  if (data) {
    options['body'] = data;
  }

  console.log('options', options)

  return options;
}
