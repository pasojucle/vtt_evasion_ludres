export interface FetchResult<T = any> {
  data: T | null;
  httpResponse: number;
  error: string | null;
}

export const dataLoader = async (
  entity: string,
  param?: string | number | undefined,
  token?: string | undefined
) => {

  const response = await fetch(url(entity, param), options(token));
  console.log(response);
  const httpResponse = response.status;
  const jsonResult = await response.json();

  if (response.ok) {
    return { data: jsonResult, httpResponse, error: null };
  } else {
    console.error(jsonResult);
    return {
      data: null,
      httpResponse,
      error: jsonResult?.message ?? 'Une erreur est survenue',
    };
  }
};

export const dataSender = async (
  method: 'GET' | 'POST' | 'PATCH' | 'DELETE',
  entity: string,
  param: string | number | undefined,
  token: string | undefined,
  data?: BodyInit
): Promise<any> => {
  try {
    const response = await fetch(url(entity, param), options(token, method, data));
    console.log('response', response.status);

    if (response.status === 401) return { user: null };
    if (response.status === 204) return { status: 204 };

    const jsonResult = await response.json();
    return jsonResult;
  } catch (error: any) {
    console.error(error.message);
    return {
      error: error.message ?? 'Unknown error',
      status: error.status ?? 500,
    };
  }
};

const url = (entity: string, param: string | number | undefined): string => {
  let apiUrl = `/api/${entity}`;
  if (param) {
    apiUrl += `/${param}`;
  }

  return apiUrl;
};

const options = (
  token: string | undefined,
  method: 'GET' | 'POST' | 'PATCH' | 'DELETE' = 'GET',
  data: BodyInit | undefined = undefined
): RequestInit => {
  const headers: Record<string, string> = token
    ? { Authorization: `Bearer ${token}` }
    : {};

  switch (method) {
    case 'DELETE':
      headers['Content-Type'] = 'application/json';
      break;
    case 'POST':
      headers['Content-Type'] = 'application/ld+json';
      break;
    case 'PATCH':
      headers['Content-Type'] = 'application/merge-patch+json';
      headers['Accept'] = 'application/ld+json';
      break;
  }

  const config: RequestInit = {
    method,
    headers,
    body: data,
  };

  return config;
};
