export const dataLoader = async (param, token) => {
  try {
    const res = await fetch(`/api/${param}`, {
      // headers: {
      //   'Authorization': `Bearer ${token}`
      // }
    });
    const jsonResult = await res.json();

    return jsonResult;
  } catch (error) {
    console.error(error);
    return {'error': error, httpResponse: response.status}
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